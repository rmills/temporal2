<?php

namespace Module;

class Js_jgrowl extends Module {

    public static function __registar_callback() {
        \CMS::callstack_add('parse', DEFAULT_CALLBACK_CREATE);
    }

    public static function parse() {
        \Html::set('{css}', '<link href="' . PATH_BASE . 'system/modules/js_jgrowl/assets/jquery.jgrowl.min.css" type="text/css" rel="stylesheet">');
        \Html::set('{scripts}', '<script type="text/javascript" src="' . PATH_BASE . 'system/modules/js_jgrowl/assets/jquery.jgrowl.min.js"></script>');
    }

}