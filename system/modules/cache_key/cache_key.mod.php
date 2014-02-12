<?php

namespace Module;

if(!defined('CACHE_KEY')){
    define('CACHE_KEY', '0');
}
class Cache_key extends Module {
    
    public static function __registar_callback() {
        \CMS::callstack_add('set_tag', DEFAULT_CALLBACK_PARSE+10);
    }
    
    public static function set_tag(){
        \Html::set('{cache_key}', CACHE_KEY);
    }
}