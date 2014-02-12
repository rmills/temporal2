<?php

/**
 * @author Ryan Mills <ryan@ryanmills.net> (Primary)
 * 
 * Login page
 */
namespace Page;
class Login extends Page {
    public static $_error = false;
    public static $_isrestricted = true;
    private static $_fail_strings = array(
        'Fail! Are you sure you<br />belong here?',
        'Nope, thats not vaild!',
        'Oh noes, you failed!',
        'Got fail? Yes you do.',
        'Well thats not right...'
    );

    public static function active() {
        if (\CMS::allowed()) {
            \CMS::callstack_add('setup', DEFAULT_CALLBACK_SETUP);
            \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
        }
    }

    public static function setup() {
        \Html::set('{title}', 'Login');
        \CMS::$_page_type = 'login';
        \CMS::$_content_type = 'html';
    }

    public static function parse() {
        if (USER_TYPE == 'admin') {
            \Html::template(self::block('admin.html'));
        } else {
            \Html::load();
            \Html::set('{content}', self::block('community.html'));
        }
        \Html::set('{login_error}');
        
        if(isset($_SESSION['login_message'])){
            \Html::set('{login_error}', \Module\Notice::info($_SESSION['login_message']));
            $_SESSION['login_message'] = null;
        }
        
        if (isset($_POST['do_login'])) {
            if (!self::auth($_POST['email'], $_POST['pass'])) {
                if (self::$_error) {
                    \Html::set('{login_error}', '<p class="alert alert-error">' . self::$_error . '</p>');
                } else {
                    \Html::set('{login_error}', '<p class="alert alert-error">' . self::$_fail_strings[rand(0, count(self::$_fail_strings) - 1)] . '</p>');
                }
            } else {
                header('Location: ' . DEFAULT_PROTOCOL . DOMAIN);
                die();
            }
        }
    }

    public static function auth($email, $pass) {
        $email = strtolower(stripslashes(trim($email)));
        $email = substr($email, 0, 60);

        $sql = 'SELECT salt,uid,password,status FROM `users` WHERE `email` = "' . \DB::clean($email) . '" LIMIT 1';
        $response = \DB::q($sql);

        if (count($response)) {
            $salt = $response[0]['salt'];
            $hashed = md5($salt . $pass . $salt);
            if ($hashed == $response[0]['password']) {
                if ($response[0]['status'] == 'active') {
                    $_SESSION['user'] = $response[0]['uid'];
                    \CMS::$_user = new \User($response[0]['uid']);
                    return true;
                } elseif ($response[0]['status'] == 'new') {
                    self::$_error = 'You have not actived your account. Please check your email and click the link.';
                }
            } else {
                \CMS::log('User', 'password does not match email');
            }
        } else {
            \CMS::log('User', 'email address not found');
        }
        return false;
    }

}
