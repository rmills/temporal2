<?php

/**
 * @author Ryan Mills <ryan@ryanmills.net> (Primary)
 * 
 * This is a baseclass that all modules should extend
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
                \CMS::log('Module', 'Missing block: ' . $file, 2);
                return false;
            }
        }else{
            $file = PATH_MODULE_ROOT . strtolower($class[1]) . '/blocks/' . $filename;
            if (is_file($file)) {
                return file_get_contents($file);
            } else {
                return 'Not Found: '.$file;
                \CMS::log('Module', 'Missing block: ' . $file, 2);
                return false;
            }
        }
    }
    
    
    /**
     * Fetch an asset of a Module in a formated tag or url path
     * @param string $filename name of file
     * @param string $type tag type: css, js, image
     * @return string url/html tag
     * 
     * Leave type blank to return just the path
     */
    protected static function asset($filename, $type = false){
        
        if(!$filename){
            return 'Filename not passed to Module::asset()';
        }
        
        $trace = debug_backtrace();
        $class = explode('\\', $trace[1]['class']);
        $path = explode(DIRECTORY_SEPARATOR,$trace[0]['file']);
        $type_class = strtolower($path[count($path)-4]);
        
        if($type_class == 'site'){
            $file = PATH_MODULE_ROOT_ADDON . strtolower($class[1]) . '/assets/' . $filename;
        }else{
            $file = PATH_MODULE_ROOT . strtolower($class[1]) . '/assets/' . $filename;
        }
        
        if(!$type){
            if(is_file($file)){
                return $file;
            }else{
                return 'Module::asset() "'.$filename.'" not found: '.$file;
            }
        }else{
            if(is_file($file)){
                switch($type){
                    case 'css':
                        return '<link rel="stylesheet" href="/'.$file.'" type="text/css">';
                    case 'js':
                        return '<script type="text/javascript" src="/'.$file.'"></script>';
                    case 'image':
                        return '<img src="'.$file.'">';
                    default:
                        return 'Module::asset() '.$filename.' type: '.$type.' not supported';
                }
            }else{
                return 'Module::asset() "'.$filename.'" not found: '.$file;
            }
        }
    }

}