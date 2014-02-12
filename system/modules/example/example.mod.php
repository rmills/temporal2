<?php

namespace Module;

class Example extends Module {

    public static function __registar_callback() {
        //\CMS::callstack_add('callbacktest');
    }

    public static function callbacktest() {
        /*
          echo 'Sample1 callbacktest()';
          echo self::block('test.html');
         */
    }

}