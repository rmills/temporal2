<?php

namespace Module;

class Js_datatables extends Module {

    public static function __registar_callback() {
        \CMS::callstack_add('add_assets', DEFAULT_CALLBACK_CREATE);
    }

    public static function add_assets() {
        \Html::$_css[] = '<link href="' . PATH_BASE . 'system/modules/js_datatables/assets/css/demo_page.css" type="text/css" rel="stylesheet">';
        \Html::$_css[] = '<link href="' . PATH_BASE . 'system/modules/js_datatables/assets/css/demo_table_jui.css" type="text/css" rel="stylesheet">';
        \Html::$_scripts[] = '<script type="text/javascript" src="' . PATH_BASE . 'system/modules/js_datatables/assets/jquery.dataTables.js"></script>';
        //\Html::$_scripts[] = '<script type="text/javascript">hs.graphicsDir = "'.PATH_BASE.'class/modules/js_datatables/assets/graphics/";</script>';
    }

}