<?php

namespace Module;

class Admin_user extends Module {

    private static $_pagemode = false;
    private static $_status = false;
    private static $_action_complete = false;

    public static function __registar_callback() {
        if (\CMS::allowed()) {
            if (\CMS::$_vars[0] == 'admin' && \CMS::$_vars[1] == 'user') {
                \CMS::callstack_add('create', DEFAULT_CALLBACK_CREATE);
                \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
                \CMS::callstack_add('set_nav', DEFAULT_CALLBACK_CREATE);
            } else {
                \CMS::callstack_add('set_nav', DEFAULT_CALLBACK_CREATE);
            }
        }
    }

    public static function set_nav() {
        \Page\Admin::add_link('
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Users<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="{root_doc}admin/user/add">Add User</a></li>
                    <li><a href="{root_doc}admin/user/editlist">Manage Users</a></li>
                </ul>
            </li>
        ');
    }

    public static function create() {
        \Page\Admin::$_subpage = true;
    }

    public static function parse() {
        /* Pre Action Handlers */
        if (\CMS::$_vars[3] == 'submit') {
            switch (\CMS::$_vars[2]) {
                case 'edit':
                    self::edit_user_submit();
                    break;

                case 'add':
                    self::add_user_submit();
                    break;
            }
        }


        /* Page Content Handlers */
        switch (\CMS::$_vars[2]) {
            case 'add':
                self::$_pagemode = 'add';
                self::add_user();
                \Html::set('{userstitle}', 'Add User');
                break;

            case 'edit':
                self::$_pagemode = 'edit';
                self::edit_user();
                \Html::set('{userstitle}', 'Update User');
                break;

            case 'editlist':
                self::$_pagemode = 'editlist';
                self::edit_list();
                \Html::set('{userstitle}', 'Manage Users');
                break;

            case 'confirmdelete':
                self::$_pagemode = 'confirmdelete';
                self::confirmdelete_user();
                \Html::set('{userstitle}', 'Delete User');
                break;

            case 'delete':
                self::$_pagemode = 'delete';
                self::delete_user();
                \Html::set('{userstitle}', 'Delete User');
                break;

            case 'suspend':
                self::$_pagemode = 'suspend';
                self::suspend_user();
                \Html::set('{userstitle}', 'Suspend User');
                break;

            case 'restore':
                self::$_pagemode = 'restore';
                self::restore_user();
                \Html::set('{userstitle}', 'Restore User');
                break;
            default:
                self::$_pagemode = 'editlist';
                self::edit_list();
                \Html::set('{userstitle}', 'Manage Users');
                break;
        }

        if (self::$_status) {
            \Html::set('{status}', self::$_status);
        }
    }

    public static function add_user() {
        if (!self::$_action_complete) {
            $html = self::block('adduser.html');
            \Html::set('{admin_content}', $html);

            if (isset($_POST['email'])) {
                \Html::set('{setemail}', $_POST['email']);
            } else {
                \Html::set('{setemail}');
            }

            if (isset($_POST['name'])) {
                \Html::set('{setname}', $_POST['name']);
            } else {
                \Html::set('{setname}');
            }

            if (isset($_POST['password'])) {
                \Html::set('{setpassword}', $_POST['password']);
            } else {
                \Html::set('{setpassword}');
            }

            if (isset($_POST['super_user'])) {
                \Html::set('{setsuperuser}', 'checked="checked"');
            } else {
                \Html::set('{setsuperuser}');
            }
            \Html::set('{groups}', self::build_groups_list( array(1,2) ));
            \Html::set('{status}');
        }
    }

    public static function add_user_submit() {
        $email = trim(strtolower($_POST['email']));
        $name = trim($_POST['name']);
        $salt = \Crypto::random_key();
        $password = md5($salt . trim($_POST['password']) . $salt);

        $check = self::dup_email_check($email);
        if ($check) {
            self::$_action_complete = false;
            self::$_status = '<div class="error">Email already in use</div>';
            return false;
        }

        if (isset($_POST['super_user'])) {
            $super_user = 'yes';
        } else {
            $super_user = 'no';
        }
        
        $groups = false;
        foreach($_POST as $k=>$v){
            if($v == 'add'){
                $id = self::fetch_group_id($k);
                $groups .= $id.',';
            }
        }
        
        $sql = '
            INSERT INTO users (
                `email`,
                `name`,
                `password`,
                `salt`,
                `super_user`,
                `groups`
            ) VALUES (
                \'' . \DB::clean($email) . '\',
                \'' . \DB::clean($name) . '\',
                \'' . \DB::clean($password) . '\',
                \'' . \DB::clean($salt) . '\',
                \'' . $super_user . '\',
                \''.$groups.'\'
		)';
        \DB::q($sql);
        echo \DB::$_lasterror;
        $html = '<h1>Success</h1><hr /><p>User "' . $email . '" created.</p><div class="form-controls"> <a class="btn btn-info" href="{root_doc}admin/user/">Return</a></div>';
        \Html::set('{admin_content}', $html);
        self::$_action_complete = true;
    }
    
    /**
     * Used for pages and modules to add users
     * 
     * If a password is not set an outside class (fb, etc) must set the login 
     * since the password will not be useable
     * 
     * @param type $email
     * @param type $name
     * @param type $groups
     * @param type $password
     * @param type $super_user
     * @return boolean
     */
    public static function remote_adduser($email, $name, $groups = '1,2', $auth_provider = 'site', $auth_id = 0, $password = false, $super_user = false) {
        $email = trim(strtolower($email));
        $name = trim($name);
        if($password){
            $salt = \Crypto::random_key();
            $password = md5($salt . trim($password) . $salt);
        }else{
            $salt = \Crypto::random_key();
            $password = \Crypto::random_key().\Crypto::random_key();
        }
        
        $check = self::dup_email_check($email);
        if ($check) {
            return false;
        }

        if ($super_user) {
            $super_user = 'yes';
        } else {
            $super_user = 'no';
        }
        
        $sql = '
            INSERT INTO users (
                `email`,
                `name`,
                `password`,
                `salt`,
                `super_user`,
                `groups`,
                `auth_provider`,
                `auth_id`
            ) VALUES (
                \'' . \DB::clean($email) . '\',
                \'' . \DB::clean($name) . '\',
                \'' . \DB::clean($password) . '\',
                \'' . \DB::clean($salt) . '\',
                \'' . $super_user . '\',
                \''.\DB::clean($groups).'\',
                \''.\DB::clean($auth_provider).'\',
                \''.\DB::clean($auth_id).'\'
            )';
        \DB::q($sql);
        return \DB::$_lastid;
    }

    public static function edit_list() {
        $html = self::block('editlist.html');
        $users = array();
        $sql = 'SELECT * FROM `users`';
        $list = \DB::q($sql);
        foreach ($list as $v) {
            if ($v['status'] != 'active') {
                $status = '<a class="btn btn-danger disabled">' . $v['status'] . '</a>';
            } else {
                $status = '<a class="btn btn-info disabled">' . $v['status'] . '</a>';
            }

            if ($v['super_user'] == 'yes') {
                $super = '<a class="btn btn-warning disabled">Enabled</a>';
            } else {
                $super = '<a class="btn btn-info disabled">Disabled</a>';
            }
            $users[] = '
                <tr>
                    <td>' . $v['email'] . '</td>
                    <td>' . $v['name'] . '</td>
                    <td>' . $super . '</td>
                    <td>' . $status . '</td>
                    <td>';
            $users[] = '<div class="btn-group">
                <button type="button" class="btn btn-primary">Options</button>
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                  <span class="caret"></span>
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" role="menu">
                <li><a href="{root_doc}admin/user/edit/' . $v['uid'] . '"><i class="icon-edit icon-white"></i> edit</a></li> ';

            if ($v['status'] == 'active') {
                $users[] = '<li><a href="{root_doc}admin/user/suspend/' . $v['uid'] . '"><i class="icon-lock icon-white"></i> suspend</a></li> ';
            } else {
                $users[] = '<li><a href="{root_doc}admin/user/restore/' . $v['uid'] . '"><i class="icon-white icon-share-alt"></i> restore</a></li> ';
            }

            $users[] = '<li><a href="{root_doc}admin/user/confirmdelete/' . $v['uid'] . '"><i class="icon-white icon-remove"></i> delete</a></li> 
                    </ul></div></td>
                </tr>
            ';
        }
        $html = str_replace('{users}', implode(PHP_EOL, $users), $html);
        \Html::set('{admin_content}', $html);

        \Html::set('{status}');
    }

    public static function edit_user() {
        if (!self::$_action_complete) {
            $html = self::block('edituser.html');
            if (is_numeric(\CMS::$_vars[3])) {
                $uid = \CMS::$_vars[3];
            } else {
                $uid = $_POST['uid'];
            }

            $user = new \User($uid);

            \Html::set('{admin_content}', $html);

            \Html::set('{status}');
            \Html::set('{setemail}', $user->_data['email']);
            $name = str_replace('"', '&quot;', $user->_data['name']);
            \Html::set('{setname}', $name);
            \Html::set('{uid}', $uid);
            \Html::set('{groups}', self::build_groups_list($user->_data['groups']));
            if ($user->_data['super_user'] == 'yes') {
                \Html::set('{setsuperuser}', 'checked="checked"');
            } else {
                \Html::set('{setsuperuser}');
            }
        }
    }

    public static function edit_user_submit() {
        $email = trim(strtolower($_POST['email']));
        $name = trim($_POST['name']);

        $salt = \Crypto::random_key();

        $password = md5($salt . trim($_POST['password']) . $salt);
        $uid = $_POST['uid'];

        $check = self::dup_email_check($email, $uid);
        if ($check) {
            self::$_action_complete = false;
            self::$_status = '<div class="error">Email already in use</div>';
            return false;
        }

        if (isset($_POST['super_user'])) {
            $super_user = 'yes';
        } else {
            $super_user = 'no';
        }
        
        $groups = false;
        foreach($_POST as $k=>$v){
            if($v == 'add'){
                $id = self::fetch_group_id($k);
                $groups .= $id.',';
            }
        }

        if ($_POST['password'] != '') {
            $sql = 'UPDATE `users` SET 
            `email` = \'' . \DB::clean($email) . '\',
            `name` = \'' . \DB::clean($name) . '\',
            `password` = \'' . \DB::clean($password) . '\',
             `salt` = \'' . \DB::clean($salt) . '\',
            `super_user` = \'' . \DB::clean($super_user) . '\',
            `groups` = \'' . \DB::clean($groups) . '\'
            WHERE `uid` = \'' . \DB::clean($uid) . '\' LIMIT 1';
        } else {
            $sql = 'UPDATE `users` SET 
            `email` = \'' . \DB::clean($email) . '\',
            `name` = \'' . \DB::clean($name) . '\',
            `super_user` = \'' . \DB::clean($super_user) . '\',
            `groups` = \'' . \DB::clean($groups) . '\'
            WHERE `uid` = \'' . \DB::clean($uid) . '\' LIMIT 1';
        }

        \DB::q($sql);
        $html = '<h1>Success</h1><hr><p>User "' . $email . '" updated.</p><div class="form-controls"><a class="btn btn-info" href="{root_doc}admin/user/editlist/">Return</a></div>';
        \Html::set('{admin_content}', $html);
        self::$_action_complete = true;
    }

    public static function confirmdelete_user() {
        $uid = \CMS::$_vars[3];
        $user = new \User($uid );

        $html = '<h1>Confirm Delete</h1><hr><p>Are you sure you want to delete the user: "' . $user->_data['email'] . '"?</p><div class="form-controls"><a class="btn btn-warning" href="{root_doc}admin/user/delete/' . $uid . '">Delete</a> <a class="btn btn-info" href="{root_doc}admin/user/editlist/">Cancel</a></div>';
        \Html::set('{admin_content}', $html);
    }

    public static function delete_user() {
        $uid = \CMS::$_vars[3];
        if ($uid !== '1') {
            $sql = 'DELETE FROM `users` WHERE `uid` = \'' . \DB::clean($uid) . '\' LIMIT 1';
            \DB::q($sql);

            $html = '<h1>User Deleted</h1><hr><p>User removed.</p><div class="form-controls"><a class="btn btn-info" href="{root_doc}admin/user/editlist/">Return</a></div>';
            \Html::set('{admin_content}', $html);
        } else {
            $html = '<h1>Failed</h1><hr><p>Guest user can not be removed.</p><div class="form-controls"><a class="btn btn-info" href="{root_doc}admin/user/editlist/">Return</a></div>';
            \Html::set('{admin_content}', $html);
        }
    }

    public static function suspend_user() {
        $uid = \CMS::$_vars[3];
        $user = new \User($uid);
        if ($uid !== '1') {
            if ($uid == \CMS::$_user->_data['uid']) {
                $html = '<h1>Failed</h1><hr><p>You can not suspend yourself.</p><div class="form-controls"><a class="btn btn-info" href="{root_doc}admin/user/editlist/">Return</a></div>';
                \Html::set('{admin_content}', $html);
                return false;
            } else {
                $sql = 'UPDATE `users` SET `status` = \'suspended\' WHERE `uid` = \'' . \DB::clean($uid) . '\' LIMIT 1';
                \DB::q($sql);
                $html = '<h1>Success</h1><hr /><p>User ' . $user->_data['email'] . ' suspended.</p><div class="form-controls"><a class="btn btn-info" href="{root_doc}admin/user/editlist/">Return</a></div>';

                \Html::set('{admin_content}', $html);
                return true;
            }
        } else {
            $html = '<h1>Failed</h1><hr /><p>Guest user can not be suspended.</p><div class="form-controls"><a class="btn btn-info" href="{root_doc}admin/user/editlist/">Return</a></div>';
            \Html::set('{admin_content}', $html);
            return false;
        }
    }

    public static function restore_user() {
        $uid = \CMS::$_vars[3];
        $user = new \User($uid);
        $sql = 'UPDATE `users` SET `status` = \'active\' WHERE `uid` = \'' . \DB::clean($uid) . '\' LIMIT 1';
        \DB::q($sql);
        $html = '<h1>Success</h1><hr><p>User ' . $user->_data['email'] . ' access restored.</p><div class="form-controls"><a class="btn btn-info" href="{root_doc}admin/user/editlist/">Return</a></div>';

        \Html::set('{admin_content}', $html);
        return true;
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

