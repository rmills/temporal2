<?php

/**
 * @author Ryan Mills <ryan@ryanmills.net> (Primary)
 * 
 * This is a baseclass that all pages should extend
 */

namespace Module;

class Module {
    public static $_isrestricted = false;

    /**
     * Add callbacks to \CMS()
     */
    public static function __registar_callback() {
        
    }
    
    /**
     * Legacy, do not use
     */
    public static function active() {
        
    }

    /**
     * Load block for module
     * @param string $filename
     * @return mixed html or false if not found 
     */
    protected static function block($filename) {
        $trace = debug_backtrace();
        $class = explode('\\', $trace[1]['class']);
        $path = explode(DIRECTORY_SEPARATOR,$trace[0]['file']);
        $type = strtolower($path[count($path)-4]);
        
        if($type == 'site'){
            $file = PATH_MODULE_ROOT_ADDON . strtolower($class[1]) . '/blocks/' . $filename;
            if (is_file($file)) {
                return file_get_contents($file);
            } else {
                return 'Not Found: '.$file;
                \CMS::log('Page', 'Missing block: ' . $file, 2);
                return false;
            }
        }else{
            $file = PATH_MODULE_ROOT . strtolower($class[1]) . '/blocks/' . $filename;
            if (is_file($file)) {
                return file_get_contents($file);
            } else {
                return 'Not Found: '.$file;
                \CMS::log('Page', 'Missing block: ' . $file, 2);
                return false;
            }
        }
    }

}