<?php

namespace Module;

class Editor4 extends Module {
    public static $_isrestricted = true;
    public static function __registar_callback() {
        if (\CMS::allowed()) {
            //\CMS::callstack_add('add_links', DEFAULT_CALLBACK_PARSE - 1);
            /* This editor is called only as needed */
        }
    }

    public static function add_links() {
        \Html::set('{scripts}', '<script src="{root_doc}system/modules/editor4/assets/trumbowyg.min.js"></script>');
        \Html::set('{css}', '<link rel="stylesheet" href="{root_doc}system/modules/editor4/assets/ui/trumbowyg.min.css">');
    }

}