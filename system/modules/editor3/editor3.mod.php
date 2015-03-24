<?php

namespace Module;

class Editor3 extends Module {
    public static $_isrestricted = true;
    public static function __registar_callback() {
        if (\CMS::allowed()) {
            \CMS::callstack_add('add_links', DEFAULT_CALLBACK_PARSE - 1);
        }
    }

    public static function add_links() {
        if (\CMS::$_page_type == 'zpage') {
            //\Html::set('{scripts}', '<script src="{root_doc}system/inc/js/jquery.tmpl.min.js" type="text/javascript"></script>');
            \Html::set('{scripts}', '<script src="//cdn.ckeditor.com/4.4.7/full/ckeditor.js"></script>');
            \Html::set('{scripts}', '<script src="{root_doc}system/modules/editor3/assets/config.js?cache=2" type="text/javascript"></script>');
            \Page\Admin::add_quick_link('<li><a id="editor-edit-button" href="#">Enable Rich Editor</a></li>');
            \Html::set('{footer}', self::block('template.html'));
        }
    }

}