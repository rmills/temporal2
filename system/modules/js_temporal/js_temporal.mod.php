<?php
namespace Module;
class Js_temporal extends Module{
    public static function __registar_callback() {
        \CMS::callstack_add('check_user', DEFAULT_CALLBACK_PARSE);
    }
    
    public static function check_user(){
        if(\CMS::$_user->_uid != DEFAULT_USER){
            self::add_script();
        }
    }
    
    public static function add_script(){
        \Html::$_scripts[] = '<script type="text/javascript" src="{root_doc}system/inc/js/jquery.temporal.js"></script>';
    }
}
