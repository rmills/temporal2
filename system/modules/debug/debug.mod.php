<?php

namespace Module;

class Debug extends Module {

    public static function __registar_callback() {
        \CMS::callstack_add('check_insert', DEFAULT_CALLBACK_PARSE + 1);
    }

    public static function check_insert() {
        $check = \Html::find('{debug}');
        if ($check) {
            if (ENABLE_DEBUG_TRACE) {
                \Html::set('{debug}', \CMS::log_display_html() . \CMS::callstack_display_html());
            } else {
                \Html::set('{debug}');
            }
        }
    }

}
