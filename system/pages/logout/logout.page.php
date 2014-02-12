<?php
namespace Page;
class Logout extends Page{
    public static function active(){
        \CMS::$_user->destroy();
        session_destroy();
        \CMS::$_user = new \User(DEFAULT_USER);
        \CMS::redirect('home');
    }
}
