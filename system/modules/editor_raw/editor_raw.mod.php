<?php

namespace Module;

class Editor_raw extends Module {
    public static $_isrestricted = true;
    public static function __registar_callback() {
        if (\CMS::allowed()) {
            \CMS::callstack_add('add_links', DEFAULT_CALLBACK_PARSE - 1);
        }
    }

    public static function add_links() {
        \Html::set('{scripts}', '<script src="{root_doc}system/inc/js/tmpl.min.js" type="text/javascript"></script>');
        if (\CMS::$_page_type == 'zpage') {
            \Page\Admin::add_quick_link('<li><a id="raw-edit-button" href="#">Enable Raw Editor</a></li>');
            \Html::set('{footer}', self::block('template.html'));
        }
    }

}