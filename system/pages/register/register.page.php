<?php
namespace Page;
if (!defined('REGISTER_SITENAME')) {
    define('REGISTER_SITENAME', 'Temporal Site');
}

if (!defined('REGISTER_DEFAULT_GROUPS')) {
    define('REGISTER_DEFAULT_GROUPS', '1,2');
}

class Register extends Page {
    public static $_isrestricted = true;
    private static $_error = '';

    public static function active() {
        if(\CMS::allowed()){
            if (USER_TYPE == 'community') {
                \CMS::callstack_add('setup', DEFAULT_CALLBACK_SETUP);
                \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
            }
        }
    }

    public static function setup() {
        \CMS::$_page_type = 'register';
        \CMS::$_content_type = 'html';
        \Html::set('{title}', 'Register');
        \Html::load();

        if (\CMS::$_vars[1] == 'submit') {
            $vaild = self::create_account();
            if ($vaild) {
                $html = self::block('mailsent.html');
                $html = str_replace('{emailfrom}', MAIL_FROM_EMAIL, $html);
                \Html::set('{content}', $html);
            } else {
                \Html::set('{content}', self::block('register.html'));
                \Html::set('{email}', $_POST['email']);
                \Html::set('{user}', $_POST['user']);
                \Html::set('{error}', self::$_error);
            }
        } elseif (\CMS::$_vars[1] == 'complete') {
            if (isset($_POST['key'])) {
                $key = $_POST['key'];
            } else {
                $key = \CMS::$_vars[2];
            }
            if (is_numeric($key)) {
                $try = self::check_key($key);
                if ($try) {
                    \Html::set('{content}', self::block('key_valid.html'));
                } else {
                    \Html::set('{content}', self::block('key_fail.html'));
                }
            } else {
                $html = self::block('key_form.html');
                if ($key) {
                    $html = str_replace('{error}', '<i>key not valid</i>', $html);
                } else {
                    $html = str_replace('{error}', '', $html);
                }
                \Html::set('{content}', $html);
            }
        } else {
            \Html::set('{content}', self::block('register.html'));
            \Html::set('{email}');
            \Html::set('{user}');
            \Html::set('{error}');
        }
    }

    private static function create_account() {
        $key = self::create_key();
        $try = self::add_user_to_db($key);
        if ($try) {
            $body = self::block('email.html');
            $body = str_replace('{domain}', DEFAULT_PROTOCOL . DOMAIN, $body);
            $body = str_replace('{site_name}', SITE_NAME, $body);
            $body = str_replace('{code}', $key, $body);
            $try = \Module\Sendsmtp::send($_POST['email'], SITE_NAME, $body);
            if ($try) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private static function check_key($key) {
        $sql = 'SELECT * FROM `users` WHERE `status` = \'new\'';
        $list = \DB::q($sql);
        if (is_array($list)) {
            foreach ($list as $v) {
                if ($key == $v['activation_code']) {
                    self::activate_account($v['uid']);
                    return true;
                }
            }
        }
        return false;
    }

    private static function create_key() {
        return rand(1000000, 9999999);
    }

    public static function add_user_to_db($key) {
        $email = trim(strtolower($_POST['email']));
        $name = trim($_POST['user']);

        $check1 = self::dup_email_check($email);
        $check2 = self::dup_user_check($name);

        if ($check1 || $check2) {
            return false;
        }

        $salt = self::hash();
        $password = md5($salt . trim($_POST['password']) . $salt);


        $sql = '
            INSERT INTO users (
                `email`,
                `name`,
                `password`,
                `salt`,
                `groups`,
                `activation_code`,
                `status`,
                `super_user`,
                `date_create`,
                `last_ip`
            ) VALUES (
                \'' . \DB::clean($email) . '\',
                \'' . \DB::clean($name) . '\',
                \'' . \DB::clean($password) . '\',
                \'' . \DB::clean($salt) . '\',
                \'' . REGISTER_DEFAULT_GROUPS . '\',
                \'' . \DB::clean($key) . '\',
                \'new\',
                \'no\',
                \'' . date("Ymd") . '\',
                \'' . $_SERVER['REMOTE_ADDR'] . '\'
            )';
        \DB::q($sql);
        echo \DB::$_lasterror;
        return true;
    }

    public static function hash($length = 20) {
        $random = "";
        srand((double) microtime() * 1000000);
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        $char_list .= "1234567890";
        // Add the special characters to $char_list if needed

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($char_list, (rand() % (strlen($char_list))), 1);
        }
        return $random;
    }

    public static function dup_email_check($email) {
        $sql = 'SELECT * FROM `users` WHERE `email` = \'' . $email . '\'';
        $list = \DB::q($sql);
        if (is_array($list)) {
            foreach ($list as $v) {
                self::$_error .= '<div class="alert alert-error">Email already in use</div>';
                return true;
            }
        }
        return false;
    }

    public static function dup_user_check($name) {
        $sql = 'SELECT * FROM `users`';
        $list = \DB::q($sql);
        if (is_array($list)) {
            foreach ($list as $v) {
                if (strtolower($name) == strtolower($v['name'])) {
                    self::$_error .= '<div class="alert alert-error">Username already in use</div>';
                    return true;
                }
            }
        }
        return false;
    }

    public static function activate_account($uid) {
        $sql = 'UPDATE `users` SET 
            `status` = \'active\'
            WHERE `uid` = \'' . \DB::clean($uid) . '\' LIMIT 1';
        \DB::q($sql);
    }

}
