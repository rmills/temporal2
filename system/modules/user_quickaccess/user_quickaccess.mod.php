<?php

namespace Module;

class User_quickaccess extends Module {

    public static function __registar_callback() {
        \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE + 1);
    }

    public static function parse() {
        if (USER_TYPE == 'community') {
            if (\Html::find('{user-quickaccess}')) {
                if (isset($_SESSION['user'])) {
                    if ($_SESSION['user'] == DEFAULT_USER || $_SESSION['user'] == 0) {
                        self::build_guest();
                    } else {
                        self::build_loggedin();
                    }
                } else {
                    self::build_guest();
                }
            }
        } else {
            \Html::set('{user-quickaccess}');
        }
    }

    public static function build_guest() {
        \Html::set("{user-quickaccess}", self::block('login.html'));
        \Html::set("{footer}", self::block('js.html'));
    }

    public static function build_loggedin() {
        \Html::set("{user-quickaccess}", self::block('loggedin.html'));
        \Html::set("{footer}", self::block('js.html'));
        $id = \CMS::$_user->_modules['Avatar']->_data;
        if(is_numeric($id)){
            $image = new \Image($id);
            \Html::set("{user-image}", '<div id="quickaccess-image">'.$image->thumbnail(THUMBNAIL_TINY, true).'</div>');
        }else{
            \Html::set("{user-image}", '<div id="quickaccess-image"><img src="{root_doc}system/modules/user_quickaccess/assets/User.jpg" alt="site user" title="site user"></div>');
        }
        \Html::set("{user-name}", \CMS::$_user->_data['name']);
    }

}