<?php

namespace Page;

class Zpage extends Page {

    public static $_isrestricted = true;
    static public $_data;
    static public $_status = 500;
    static public $_pid = 0;
    static public $_zonestack = array(
        'z1', 'z2', 'z3', 'z4', 'z5', 'z6', 'z7', 'z8', 'z9', 'z10', 'z11',
        'z12', 'z13', 'z14', 'z15', 'z16', 'z17', 'z18', 'z19', 'z20'
    );

    public static function __registar_callback() {
        if (\CMS::allowed() ) {
            \CMS::callstack_add('check_url', 10);
            if ( \CMS::$_vars[0] == 'update_zone' && !is_null( filter_input( INPUT_POST, 'zone_data', FILTER_UNSAFE_RAW) ) ) {
                \CMS::$_content_type = 'json';
                \CMS::$_page_type = 'zpage';
                \CMS::callstack_add('update', DEFAULT_CALLBACK_PARSE);
            }
        }
        
        if (\CMS::allowed('module\editor3') ) {
            if (\CMS::$_vars[0] == 'zone_history' && !is_null( filter_input(INPUT_POST ,'zone', FILTER_SANITIZE_STRING) ) && !is_null(filter_input(INPUT_POST ,'pid', FILTER_SANITIZE_NUMBER_INT) ) ){
                \CMS::$_content_type = 'json';
                \CMS::$_page_type = 'zpage';
                \CMS::callstack_add('zone_history', DEFAULT_CALLBACK_PARSE);
            }
            if (\CMS::$_vars[0] == 'zone_history_data' && !is_null( filter_input(INPUT_POST ,'z_id', FILTER_SANITIZE_NUMBER_INT) )) {
                \CMS::$_content_type = 'json';
                \CMS::$_page_type = 'zpage';
                \CMS::callstack_add('zone_history_data', DEFAULT_CALLBACK_PARSE);
            }
        }else{
            if ( \CMS::$_vars[0] == 'update_zone' && !is_null( filter_input( INPUT_POST, 'zone_data', FILTER_UNSAFE_RAW) ) ) {
                \CMS::$_content_type = 'json';
                \CMS::$_page_type = 'zpage';
                \Json::$_body .= json_encode(array('status' => 'fail'));
            }
        }
    }

    public static function update() {
        self::update_zone(\CMS::$_vars[1], \CMS::$_vars[2], filter_input( INPUT_POST ,'zone_data', FILTER_UNSAFE_RAW) );
    }

    public static function check_url() {

        $found = false;
        if (\CMS::$_vars[0] == '' && DEFAULT_PAGE_GUEST == 'Zpage') {
            $found = true;
        } elseif (\CMS::$_vars[0] == 'page') {
            $found = true;
        } elseif (\CMS::$_vars[0] != '') {
            self::$_pid = self::fetch_by_url(\CMS::$_vars[0]);
            if (is_numeric(self::$_pid)) {
                if (self::$_pid != 0) {
                    $found = true;
                }
            }
        }

        if ($found) {
            \CMS::$_page_type = 'zpage';
            \CMS::$_content_type = 'html';
            \CMS::callstack_add('create', DEFAULT_CALLBACK_CREATE);
            \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
        }
        \CMS::callstack_add('output', DEFAULT_CALLBACK_OUTPUT);
        \CMS::callstack_add('reg_page_list', DEFAULT_CALLBACK_CREATE);
    }

    public static function create() {
        if (!self::$_pid) {
            if (\CMS::$_vars[0] == '') {
                self::$_pid = DEFAULT_ZPAGE;
                self::$_data = self::fetch_by_id(self::$_pid);
                self::$_status = 200;
            } elseif (\CMS::$_vars[0] == 'page') {
                $check = self::fetch_by_id(\CMS::$_vars[1]);
                if (is_array($check)) {
                    self::$_pid = \CMS::$_vars[1];
                    self::$_data = $check;
                    self::$_status = 200;
                } else {
                    self::$_status = 404;
                }
            }
        } else {
            self::$_data = self::fetch_by_id(self::$_pid);
            self::$_status = 200;
        }
        \Page\Admin::add_quick_link('<li><a href="{root_doc}admin/page/edit/' . self::$_pid . '">Edit Page</a></li>');
        \CMS::log('Zpage', 'loading page "' . self::$_data['pid'] . '"');
        self::mount_zones(self::$_pid);
        if(\CMS::$_user->_uid == DEFAULT_USER){
            \CMS::$_cacheable = true;
            \CMS::$_cacheblock = false;
        }
    }

    public static function parse() {
        \Html::load(self::$_data['template']);
        if (is_array(self::$_data)) {
            foreach (self::$_data as $key => $value) {
                \Html::set('{' . $key . '}', $value);
            }
        }
        \Html::set('{page_url}', self::$_data['url']);
    }

    public static function output() {
        if (!\Html::$_hascontent && \CMS::$_content_type == 'html' && \CMS::$_page_type == 'zpage') {
            self::display_404();
        }
    }

    private static function fetch_by_url($url) {
        $url = strtolower(trim($url));
        $sql = 'SELECT pid FROM `pages` WHERE `url` = \'' . \DB::clean($url) . '\' AND `status` = \'active\' LIMIT 1;';
        $response = \DB::q($sql);
        if (is_array($response)) {
            foreach ($response as $item) {
                return $item['pid'];
            }
        }
    }

    public static function fetch_by_id($pid, $all = false) {
        if (!is_numeric($pid)) {
            return false;
        }
        $pid = trim($pid);
        if ($all) {
            $sql = 'SELECT * FROM `pages` WHERE `pid` = \'' . \DB::clean($pid) . '\' LIMIT 1;';
        } else {
            $sql = 'SELECT * FROM `pages` WHERE `pid` = \'' . \DB::clean($pid) . '\' AND `status` = \'active\' LIMIT 1;';
        }
        $response = \DB::q($sql);
        if (is_array($response)) {
            foreach ($response as $item) {
                return $item;
            }
        }
    }
    
    public static function reg_page_list() {
        $sql = 'SELECT `url` FROM `pages`';
        $response = \DB::q($sql);
        if (is_array($response)) {
            foreach ($response as $item) {
                \Page\Sitemap::register(DEFAULT_PROTOCOL.DOMAIN.'/'.$item['url']);
            }
        }
    }

    private static function mount_zones($pid) {
        foreach (self::$_zonestack as $v) {
            if (is_numeric(self::$_data[$v])) {
                $data = self::fetch_zone($pid, self::$_data[$v]);
                if(!$data){
                   /** Ignore and dont add data, editors should do this on toggle
                    $data = '<p>Blank Zone</p>'; 
                    
                    */
                    self::$_data[$v] = '';
                }
                self::$_data[$v] = $data;
            }
        }
    }

    private static function fetch_zone($pid, $z_id) {
        if (!is_numeric($z_id)) {
            return false;
        }
        $z_id = trim($z_id);
        $sql = 'SELECT z_data FROM `zones` WHERE `z_pid` = \'' . \DB::clean($pid) . '\' AND `z_id` = \'' . \DB::clean($z_id) . '\' LIMIT 1;';
        $response = \DB::q($sql);
        if (is_array($response)) {
            foreach ($response as $item) {
                return urldecode($item['z_data']);
            }
        }
        return false;
    }

    private static function display_404() {
        \Html::error_404();
    }

    private static function update_zone($z_id, $pid, $zdata) {
        $zdate = date("U");
        $zdata = urlencode(trim($zdata));
        $sql = '
            INSERT INTO zones (
                `z_data`,
                `z_date`,
                `z_pid`,
                `z_parent`,
                `z_user`
            ) VALUES (
                \'' . \DB::clean($zdata) . '\',
                \'' . $zdate . '\',
                \'' . \DB::clean($pid) . '\',
                \'' . \DB::clean($z_id) . '\',
                \'' . \DB::clean(\CMS::$_user->_data['uid']) . '\'
            )';
        \DB::q($sql);

        $sql = '
            UPDATE `pages` SET 
                    `' . \DB::clean($z_id) . '` = \'' . \DB::clean(\DB::$_lastid) . '\'
            WHERE 
                    `pid` = \'' . \DB::clean($pid) . '\' 
            LIMIT 1
	';
        \DB::q($sql);
        \Json::$_body .= json_encode(array('status' => 'ok'));
    }
    
    public static function zone_history() {
        $zone = filter_input( INPUT_POST ,'zone', FILTER_SANITIZE_STRING);
        $pid = filter_input( INPUT_POST ,'pid', FILTER_SANITIZE_NUMBER_INT);
        
        $sql = 'SELECT * FROM `zones` WHERE `z_pid` = \'' . \DB::clean($pid) . '\' AND `z_parent` = \'' . \DB::clean($zone) . '\' ORDER BY `z_id` DESC LIMIT '.ZPAGE_HISTORY_LIMIT.';';
        
        $response = \DB::q($sql);
        $stack = array();
        if (is_array($response)) {
            foreach ($response as $item) {
                $temp = array();
                foreach($item as $k=>$v){
                    $temp[$k] = $v;
                }
                $user = new \User($item['z_user']);
                if(!$user->_error){
                    $temp['username'] = $user->_data['name'];
                    $stack[] = $temp;
                }
            }
        }
        
        \Json::$_body .= json_encode($stack);
    }
    
    public static function zone_history_data() {
        $z_id = filter_input( INPUT_POST ,'z_id', FILTER_SANITIZE_NUMBER_INT);
        
        $sql = 'SELECT * FROM `zones` WHERE `z_id` = \'' . \DB::clean($z_id) . '\' LIMIT 1;';
        
        $response = \DB::q($sql);
        $stack = array();
        if (is_array($response)) {
            foreach ($response as $item) {
                $temp = array();
                foreach($item as $k=>$v){
                    $temp[$k] = $v;
                }
                $stack[] = $temp;
            }
        }
        
     
        \Json::$_body .= json_encode($stack);
    }

}

