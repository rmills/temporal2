<?php
namespace Appserve;
class Appserve{
    public static $_isrestricted = false;
    public static $_apps = array();
    
    /**
     * Load block for page
     * @param string $filename
     * @return mixed html or false if not found 
     */
    protected static function block($filename) {
        $trace = debug_backtrace();
        $class = explode('\\', $trace[1]['class']);
        $file = PATH_PAGE_ROOT . strtolower($class[1]) . '/blocks/' . $filename;
        if (is_file($file)) {
            return file_get_contents($file);
        } else {
            $file = PATH_PAGE_ROOT_ADDON . strtolower($class[1]) . '/blocks/' . $filename;
            if (is_file($file)) {
                return file_get_contents($file);
            } else {
                return 'NotFound: '.$file;
                \CMS::log('Appserve', 'Missing block: ' . $file, 2);
            }
        }
    }

    /**
     * Registar a callback with the core
     */
    public static function __registar_callback() {
        
    }

    /**
     * Called based on URI
     */
    public static function active() {
        die( json_encode( array('status'=>'fail') ) );
    }
    
    /**
     * Register a callback, currently only the UserMod's are supported but 
     * future modules will add this to replace __registar_callback
     * @param string $class
     * @param string $type
     * @throws Exception if a type is not set, only UserMod is supported
     */
    public static function register($class){
        self::$_apps[$class] = $class;
    }
}
