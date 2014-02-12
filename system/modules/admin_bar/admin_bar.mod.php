<?php

namespace Module;
class Admin_bar extends Module {
    public static $_isrestricted = true;
    public static function __registar_callback() {
        if (\CMS::allowed() || \CMS::$_user->mod_user == 'yes') {
            \CMS::callstack_add('set_tags', DEFAULT_CALLBACK_PARSE + 1);
        } else {
            \Html::set('{adminbar}');
        }
    }

    public static function set_tags() {
        $html = self::build_admin_bar();
        ksort(\Page\Admin::$_quicklinks);
        $i = 0;
        foreach (\Page\Admin::$_quicklinks as $v) {
            $break = '<li class="divider-vertical"></li>';
            $html = str_replace('{adminlinks}', $v . $break . '{adminlinks}', $html);
            $i++;
        }
        $html = str_replace('{adminlinks}', '', $html);
        \Html::set('{adminbar}', $html);
    }

    public static function build_admin_bar() {
        $html = self::block('adminbar.html');
        return $html;
    }
}
