<?php

/**
 * @author Ryan Mills <ryan@ryanmills.net> (Primary)
 */
class Html {

    /**
     * @var bool true when content is loaded, used mostly for checking for a 404
     */
    public static $_hascontent = false;

    /**
     * @var string title of page 
     */
    public static $_title = '';

    /**
     * @var string title of page 
     */
    public static $_scripts = array();

    /**
     * @var string title of page 
     */
    public static $_css = array();

    /**
     * This holds every tag called and parsing in FIFO order.
     * @var array stores all the tags for parsing
     */
    private static $_tags_key = array();

    /**
     * This holds all data called and parsing in FIFO order.
     * @var array stores all the content for tag for parsing
     */
    private static $_tags_value = array();

    /**
     * This holds all the tags used and will strip tags.
     * @var array stores all the content for tag for parsing
     */
    private static $_tags_cleanup = array();

    /**
     * Stores all the content to be managed. Set by contstructor.
     * @var string buffer to be managed
     */
    public static $_body;

    public static function __registar_callback() {
        CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE + 1);
        CMS::callstack_add('finalize', DEFAULT_CALLBACK_OUTPUT - 1);
        if(ENABLE_PARSER){
            CMS::callstack_add('smart_parse', DEFAULT_CALLBACK_OUTPUT);
        }
        CMS::callstack_add('output', DEFAULT_CALLBACK_OUTPUT + 1);
    }

    /**
     * For setting tags and content
     * @param string $tag
     * @param mixed $value string or array
     * @return boolean was this valid
     */
    public static function set($tag, $value = '') {
        if (!is_array($tag) && $tag !== '' && !is_array($value) && $value !== '') {
            self::$_tags_key[] = $tag;
            self::$_tags_cleanup[] = $tag;
            self::$_tags_value[] = $value;
            return true;
        } elseif (is_array($value)) {
            self::$_tags_cleanup[] = $tag;
            foreach ($value as $v) {
                self::$_tags_key[] = $tag;
                self::$_tags_value[] = $v;
            }
        } else {
            self::$_tags_cleanup[] = $tag;
            return false;
        }
    }

    /**
     * Load template into primary buffer (ie template)
     * 
     * @param string $string content for buffer
     * @param bool $flush flag if content should be flushed before add
     */
    public static function template($string, $flush = true) {
        if ($flush) {
            self::$_body = $string;
        } else {
            self::$_body .= $string;
        }
        self::$_hascontent = true;
    }

    /**
     * Removes all set tags from the buffer
     */
    public static function tags_strip() {
        foreach (self::$_tags_cleanup as $tag) {
            self::$_body = str_replace($tag, '', self::$_body);
        }
    }

    /**
     * used for finding tags in the content
     * 
     * @param string $find
     * @return boolean was the string found
     */
    public static function find($find) {
        $pos = strpos(self::$_body, $find);
        if ($pos !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Loops thru all tags set via set()
     */
    public static function parse() {

        /** parse each tag and matching content, store tags for later cleanup * */
        $loop = 0;
        foreach (self::$_tags_key as $v) {
            self::$_body = str_replace($v, self::$_tags_value[$loop] . $v, self::$_body);
            $loop++;
        }

        /** init tag stacks so we dont enter dup content * */
        self::$_tags_key = array();
        self::$_tags_value = array();
    }
    
    public static function smart_parse(){
        \Parser::body(self::$_body);
        \Parser::parse();
        self::$_body = \Parser::body();
    }

    /**
     * Parse and strip any tags, call before displaying
     * 
     * You should call this when you no longer need to use set()
     */
    public static function finalize() {
        self::common_tags();
        self::parse();
        self::tags_strip();
    }
    
    /**
     * Output buffer to browser
     */
    public static function output() {
        if(!CMS::$_page_type){
            self::error_404();
        }else{
            if (CMS::$_content_type == 'html') {
                
                if(ENABLE_CACHE){
                    if(\CMS::$_cacheable){
                        CacheDB::setcache($_SERVER['REQUEST_URI'], self::$_body, \CMS::$_cacheblock);
                    }
                }
                
                self::$_body = str_replace('{buildtime}', "Build Time: ".\CMS::get_build_time(), self::$_body);
                echo self::$_body;
            }
        }
    }
    
    /**
     * Set common tags
     */
    public static function common_tags() {
        self::set('{title}', self::$_title);
        foreach (self::$_css as $v) {
            self::set('{css}', $v);
        }
        
        self::set('{css}');
        foreach (self::$_scripts as $v) {
            self::set('{scripts}', $v);
        }
        
        self::set('{meta}');
        self::set('{scripts}');
        self::set('{footer}');
        self::set('{debug}');
        self::set('{css_preload}');

        self::set('{domain}', DOMAIN);
        self::set('{default_protocol}', DEFAULT_PROTOCOL);
        self::set('{root_doc}', PATH_BASE);
        self::set('{root_css}', PATH_BASE . CMS::$_config['path_layout'] . 'css/');
        self::set('{root_js}', PATH_BASE . CMS::$_config['path_layout'] . 'js/');
        self::set('{root_images}', PATH_BASE . CMS::$_config['path_layout'] . 'images/');
        self::set('{site_name}', SITE_NAME);
    }

    /**
     * Load a template into the bugger
     * @param type $file filename
     * @return boolean true if a template is found
     */
    public static function load($file = false) {
        if ($file) {
            $path = CMS::$_config['path_layout'] . $file;
        } else {
            $path = CMS::$_config['path_layout_default'];
        }
        if (is_file($path)) {
            self::$_hascontent = true;
            self::template(file_get_contents($path));
        } else {
            CMS::log('Body', 'Could not find template: ' . $path, 2);
            return false;
        }
    }
    
    /**
     * Output 404 message
     */
    public static function error_404($die = false){
        $path_custom = PATH_LAYOUT_ROOT.'404.html';
        $path_system = PATH_LAYOUT_ROOT_DEFAULT.'404.html';
        if(is_file($path_custom)){
           echo file_get_contents($path_custom); 
        }else{
            echo file_get_contents($path_system);
        }
        
        if($die){
            die();
        }
    }

}