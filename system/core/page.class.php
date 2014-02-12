<?php

/**
 * @author Ryan Mills <ryan@ryanmills.net> (Primary)
 * 
 * This is a baseclass that all pages should extend
 */

namespace Page;

class Page {
    public static $_isrestricted = false;
    
    /**
     * Load block for page
     * @param string $filename
     * @return mixed html or false if not found 
     */
    protected static function block($filename) {
        $trace = debug_backtrace();
        $class = explode('\\', $trace[1]['class']);
        $file = PATH_PAGE_ROOT_ADDON . strtolower($class[1]) . '/blocks/' . $filename;
        if (is_file($file)) {
            return file_get_contents($file);
        } else {
            $file = PATH_PAGE_ROOT . strtolower($class[1]) . '/blocks/' . $filename;
            if (is_file($file)) {
                return file_get_contents($file);
            } else {
                return 'NotFound: '.$file;
                \CMS::log('Page', 'Missing block: ' . $file, 2);
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
        
    }

}