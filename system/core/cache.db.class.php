<?php
/**
 * Enabled via ENABLE_CACHE or \CMS() will ignore.
 */
class  CacheDB{
    
    /**
     * Not Used
     * @var int id 
     */
    public $_id;
    
    /**
     * Example: $_SERVER['REQUEST_URI']
     * @var string URL 
     */
    public $_path;
    
    /**
     * HTML or string data. Binary safe but not tested.
     * @var string 
     */
    public $_data;
    
    /**
     * Loaded via db
     * Epoch time to expire, set via CACHE_TIME
     * @var int 
     */
    public $_expires;
    
    /**
     * Loaded via db
     * @var bool is block 
     */
    public $_block = false;
    
    /**
     * @var type true if loaded and not expired
     */
    public $_status = false;
    
    /**
     * @var type load only check, does not check if expired
     */
    public $_loaded = false;
    
    /**
     * CacheDB is used to selectivly cache page content. Both whole pages and a 
     * single block if $block is set to true. Pages are set via URI. It's been
     * tested used $_SERVER['REQUEST_URI'] for the $path.
     * 
     * @param mixed $id either id or path || $_SERVER['REQUEST_URI']
     * @param bool $block true if content is only used inside a page.
     * @return bool true if content found
     */
    function __construct($path = false, $block = false) {
        if(is_numeric($path)){
            $try = $this->load($path, $block);
            return $try;
        }elseif($path){
            $try = $this->loadpath($path, $block);
            return $try;
        }
    }
    
    /**
     * Load content based on path||$_SERVER['REQUEST_URI']
     * @param string $path
     * @param bool $block
     */
    public function loadpath($path, $block){
        $sql = 'SELECT * FROM `cache` WHERE `path` = \''.DB::clean($path).'\' AND `block` = \''.$block.'\' LIMIT 1';
        $q = \DB::q($sql);
        foreach($q as $row){
            $this->_id = $row['id'];
            $this->_path = $row['path'];
            $this->_data = self::decode($row['data']);
            $this->_expires = $row['expires'];
            $this->_block = $row['block'];
            $this->_loaded = true;
            if(time() < $this->_expires){
                $this->_status = true;
                return true;
            }
        }
        return false;
    }
    
    /**
     * Load content based on id, not really used
     * @param string $id
     * @param bool $block
     */
    public function load($id){
        $sql = 'SELECT * FROM `cache` WHERE `id` = \''.DB::clean($id).'\' AND `block` = \''.$block.'\' LIMIT 1';
        $q = \DB::q($sql);
        foreach($q as $row){
            $this->_id = $row['id'];
            $this->_path = $row['path'];
            $this->_data = $row['data'];
            $this->_expires = $row['expires'];
            $this->_block = $row['block'];
            $this->_loaded = true;
            if(time() < $this->_expires){
                $this->_status = true;
            }
            return true;
        }
        return false;
    }
    
    /**
     * This will return the data loaded via load()/loadpath()
     * @return string
     */
    public function data(){
        return $this->_data;
    }
    
    /**
     * Returns if stored content is a block.
     * @return type
     */
    public function block(){
        return $this->_block;
    }
    
    
/* Static Methods */
    
    /**
     * Should never change this, we store content in base64
     * @param string $str
     * @return base64
     */
    public static function encode($str){
        return base64_encode($str);
    }
    
    /**
     * Should never change this, we store content in base64
     * @param base64 $str
     * @return string
     */
    public static function decode($str){
        return base64_decode($str);
    }
    
    /**
     * !!WARNING!! This will flush all stored cache pages. Only a super user
     * can access this function.
     */
    public static function flush(){
        if(\CMS::$_user->_super_user){
            $sql = 'TRUNCATE TABLE `cache`';
            \DB::q($sql);
        }
    }
    
    /**
     * Add content to database.
     * 
     * @param string $path example: $_SERVER['REQUEST_URI']
     * @param type $data string data, mostly HTML
     * @param type $block true if content is a block and not a whole page
     * @param type $expires how long cache is active
     */
    public static function setcache($path, $data, $block, $expires = CACHE_TIME){
        self::clearpath($path, $block);
        $time = time();
        $expires = strtotime($expires, $time);
        $sql = '
            INSERT INTO cache (
                `path`,
                `data`,
                `block`,
                `expires`
            ) VALUES (
                \'' . \DB::clean($path) . '\',
                \'' . \DB::clean(self::encode($data)) . '\',
                \'' . \DB::clean($block) . '\',
                \'' . \DB::clean($expires) . '\'
            )';
        \DB::q($sql);
    }
    
    /**
     * Internal call to make sure we dont keep dup content.
     * @param type $path example: $_SERVER['REQUEST_URI']
     * @param type $block true if content is a block and not a whole page
     */
    public static function clearpath($path, $block){
        
        $sql = 'DELETE FROM `cache` WHERE `path` = \'' . \DB::clean($path) . '\' AND `block` = \''.$block.'\'';
        \DB::q($sql);
    }
}