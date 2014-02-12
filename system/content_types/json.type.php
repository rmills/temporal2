<?php
/**
 * @author Ryan Mills <ryan@ryanmills.net> (Primary)
 */
class Json{
    /**
     * Flag when content is loaded
     * @var type 
     */
    public static $_hascontent = false;
    
    /**
     * Primary Buffer
     * @var type 
     */
    public static $_body;
    
    public static $_bypass_encode = false;
    
    /**
     * Standard Callback
     */
    public static function __registar_callback(){
        CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE+1); // future use
        CMS::callstack_add('output', DEFAULT_CALLBACK_OUTPUT);
    }
    
    /**
     * Future use
     */
    public static function body($array){
        if(self::$_bypass_encode){
            self::$_body = $array;
        }else{
            self::$_body = json_encode( $array );
        }
    }
    
    /**
     * Push content to the browser with JSON headers
     */
    public static function output(){
        if(CMS::$_content_type == 'json'){
            header('Pragma: no-cache');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Content-Disposition: inline; filename="files.json"');
            header('X-Content-Type-Options: nosniff');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
            header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');
            
            echo self::$_body;
        }
    }
}
