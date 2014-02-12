<?php
/**
 * @author Ryan Mills <ryan@ryanmills.net> (Primary)
 */
class Xml{
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
    
    /**
     * Standard Callback
     */
    public static function __registar_callback(){
        CMS::callstack_add('output', DEFAULT_CALLBACK_OUTPUT);
    }
    
    
    /**
     * Push content to the browser with JSON headers
     */
    public static function output(){
        if(CMS::$_content_type == 'xml'){
            header ("Content-Type:text/xml");
            echo self::$_body;
        }
    }
}
