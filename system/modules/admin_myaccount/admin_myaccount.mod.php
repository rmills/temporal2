<?php

namespace Module;

class Admin_myaccount extends Module {

    private static $_pagemode = false;
    private static $_status = array();

    public static function __registar_callback() {
        if (\CMS::allowed()) {
            if (\CMS::$_vars[0] == 'admin' && \CMS::$_vars[1] == 'admin_myaccount') {
                \CMS::callstack_add('create', DEFAULT_CALLBACK_CREATE);
                \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
                \CMS::callstack_add('set_nav', DEFAULT_CALLBACK_CREATE);
            } else {
                \CMS::callstack_add('set_nav', DEFAULT_CALLBACK_CREATE);
            }
            
            $blocks = array();
            $blocks[] = new \Module\AdminHomeBlock('My Account', '/admin/admin_myaccount', 'fa-user');
            \Module\Admin_home_blocks::add('My Account', $blocks, 1150);
        }
    }

    public static function set_nav() {
        \Page\Admin::add_quick_link('<li><a href="/admin/admin_myaccount">My Account</a></li>', 200);
        \Page\Admin::add_link('<li><a href="{root_doc}admin/admin_myaccount">My Account</a></li>');
    }

    public static function create() {
        \Page\Admin::$_subpage = true;
    }

    public static function parse() {
        \Html::set('{admin_content}', self::block('editself.html'));
        
        if (\CMS::$_vars[2] == 'submit') {
            self::edit_user_submit();
        }

        self::$_pagemode = 'edit';
        self::edit_self();
        
        foreach(self::$_status as $v){
            \Html::set('{status}', $v);
        }
        \Html::set('{status}');
    }

    
    
    public static function edit_self() {
        $uid = \CMS::$_user->uid;
        $user = new \User(\CMS::$_user->uid);

        \Html::set('{status}');
        \Html::set('{setemail}', $user->_data['email']);
        $name = str_replace('"', '&quot;', $user->_data['name']);
        \Html::set('{setname}', $name);
        \Html::set('{uid}', $uid);
    }

    public static function edit_user_submit() {
        $email = trim(strtolower($_POST['email']));
        $name = trim($_POST['name']);
        $password = trim($_POST['password']);
        $password_confirm = trim($_POST['confirm_password']);
        $uid = \CMS::$_user->uid;
        
        $error = false;
        if($email == ''){
            $error = true;
            self::$_status[] = \Module\Notice::error('Email address is required');
        }

        $check = self::dup_email_check($email, $uid);
        if ($check) {
            self::$_status[] = \Module\Notice::error('Email already in use');
            return false;
        }
        
        if($name == ''){
            $error = true;
            self::$_status[] = \Module\Notice::error('Name is required');
        }
        
        if($password !=  $password_confirm){
            $error = true;
            self::$_status[] = \Module\Notice::error('Passwords do not match');
        }

        
        
        if($error){
            return false;
        }
        
        $salt = \Crypto::random_key();
        $password = md5($salt . trim($_POST['password']) . $salt);

        if ($_POST['password'] != '') {
            $sql = 'UPDATE `users` SET 
            `email` = \'' . \DB::clean($email) . '\',
            `name` = \'' . \DB::clean($name) . '\',
            `password` = \'' . \DB::clean($password) . '\',
            `salt` = \'' . \DB::clean($salt) . '\'
            WHERE `uid` = \'' . \DB::clean($uid) . '\' LIMIT 1';
        } else {
            $sql = 'UPDATE `users` SET 
            `email` = \'' . \DB::clean($email) . '\',
            `name` = \'' . \DB::clean($name) . '\'
            WHERE `uid` = \'' . \DB::clean($uid) . '\' LIMIT 1';
        }

        \DB::q($sql);
        self::$_status[] = \Module\Notice::success('Your account information has been updated.');
    }

    public static function dup_email_check($email, $uid = 0) {
        $sql = 'SELECT * FROM `users` WHERE `email` = \'' . $email . '\'';
        $list = \DB::q($sql);
        if (is_array($list)) {
            foreach ($list as $v) {
                if ($uid != $v['uid']) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public static function build_groups_list($ids = array()){
        $sql = 'SELECT * FROM `groups`';
        $html = false;
        $q = \DB::q($sql);
        foreach ($q as $v) {
            $check = false;
            foreach($ids as $v2){
                if($v['id'] == $v2 && !$check ){
                    $check = 'checked="yes"';
                    break;
                }else{
                    $check = false;
                }
            }
            $html .= '<label class="checkbox"><input type="checkbox" name="'.$v['name'].'" value="add" '.$check.' >'.$v['name'].'</label>';
        }
        return $html;
    }
    
    public static function fetch_group_id($name){
        $sql = 'SELECT * FROM `groups` WHERE `name` = \''.\DB::clean($name).'\' LIMIT 1';
        $q = \DB::q($sql);
        if(is_array($q)){
            foreach ($q as $v) {
                return $v['id'];
            }
        }
        return false;
    }

}

