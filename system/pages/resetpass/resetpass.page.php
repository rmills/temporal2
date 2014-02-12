<?php
namespace Page;
class Resetpass extends Page {
    public static $_isrestricted = true;
    private static $_error = '';

    public static function active() {
        if(\CMS::allowed()){
            \CMS::callstack_add('setup', DEFAULT_CALLBACK_SETUP);
            \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
        }
    }

    public static function setup() {
        \CMS::$_page_type = 'resetpass';
        \CMS::$_content_type = 'html';
        \Html::load();
        
        if (\CMS::$_vars[1] == 'submit') {
            $vaild = self::reset_password();
            if ($vaild) {
                $html = self::block('mailsent.html');
                $html = str_replace('{emailfrom}', MAIL_FROM_EMAIL, $html);
                \Html::set('{content}', $html);
            } else {
                \Html::set('{content}', self::block('resetform.html'));
                \Html::set('{email}', $_POST['email']);
                \Html::set('{error}', \Module\Notice::error('Address not found'));
            }
        } elseif (\CMS::$_vars[1] == 'complete') {
            if (isset($_POST['key'])) {
                $key = $_POST['key'];
            } else {
                $key = \CMS::$_vars[2];
            }
            if($key != ''){
                $try = self::check_key($key);
                if ($try) {
                    if(isset($_POST['pass1'])){
                        self::set_new_password($try, $_POST['pass1']);
                        \Html::set('{content}', self::block('complete.html'));
                    }else{
                        \Html::set('{content}', self::block('setpass.html'));
                        \Html::set('{key}', $key);
                    }
                } else {
                    \Html::set('{content}', self::block('key_fail.html'));
                    \Html::set('{error}');
                }
            }else{
                \Html::set('{content}', self::block('key_form.html'));
                \Html::set('{error}');
            }
        } else {
            \Html::set('{content}', self::block('resetform.html'));
            \Html::set('{email}');
            \Html::set('{user}');
            \Html::set('{error}');
        }
    }
    
    private static function reset_password() {
        $key = \Crypto::random_secure_key();
        $try = self::email_check($_POST['email']);
        if(!$try){
            return false;
        }
        
        $try = self::set_key($key);
        if ($try) {
            $body = self::block('email.html');
            $body = str_replace('{domain}', DEFAULT_PROTOCOL . DOMAIN, $body);
            $body = str_replace('{site_name}', SITE_NAME, $body);
            $body = str_replace('{code}', $key, $body);
            $try = \Module\Sendsmtp::send($_POST['email'], REGISTER_SITENAME, $body);
            if ($try) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    public static function set_key($key) {
        $email = trim(strtolower($_POST['email']));

        $sql = 'UPDATE `users` SET 
            `reset_key` = \'' . \DB::clean($key) . '\',
            `reset_request_ip` = \'' . \DB::clean($_SERVER['SERVER_ADDR']) . '\',
            `reset_request_time` = \'' . \DB::clean(date("Ymd")) . '\'

            WHERE `email` = \'' . \DB::clean($email) . '\' LIMIT 1';
        \DB::q($sql);
        
        return true;
    }
    
    public static function email_check($email) {
        $email = trim($email);
        $sql = 'SELECT * FROM `users`';
        $list = \DB::q($sql);
        if (is_array($list)) {
            foreach ($list as $v) {
                if (strtolower($email) == strtolower($v['email'])) {
                    return $v['uid'];
                }
            }
        }
        return false;
    }
    
    
    public static function check_key($key) {
        $sql = 'SELECT * FROM `users` WHERE `reset_key` = \''.strtolower($key).'\' LIMIT 1';
        $list = \DB::q($sql);
        if (is_array($list)) {
            foreach ($list as $v) {
                return $v['uid'];
            }
        }
        return false;
    }
    
    public static function set_new_password($uid, $password){
        $salt = \Crypto::random_key();
        $password = md5($salt . trim($password) . $salt);
        $sql = 'UPDATE `users` SET 
            `password` = \'' . \DB::clean($password) . '\',
            `salt` = \''.\DB::clean($salt).'\',
            `reset_key` = \'0\',
            `reset_request_time` = \'0\'

            WHERE `uid` = \'' . \DB::clean($uid) . '\' LIMIT 1';
        \DB::q($sql);
    }
}