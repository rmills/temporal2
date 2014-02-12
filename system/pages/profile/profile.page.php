<?php
namespace Page;
class Profile extends Page {
    public static $_isrestricted = true;
    private static $_error = '';
    private static $_type = 'private';
    private static $_userid = 0;
    public static $_status = array();
    
    public static function active(){
        if(\CMS::allowed()){
            if(USER_TYPE == 'community'){
                \CMS::callstack_add('setup', DEFAULT_CALLBACK_SETUP);
                \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
            }
        }
    }
    
    public static function setup(){
        \CMS::$_page_type = 'register';
        \CMS::$_content_type = 'html';
        \Html::load();
        if( is_numeric(\CMS::$_vars[1]) ){
            self::$_type = 'public';
            self::$_userid = \CMS::$_vars[1];
        }else{
            if(\CMS::$_vars[1] == 'edit'){
                self::$_type = 'edit';
                self::$_userid = $_SESSION['user'];
            }
        }
    }
    
    public static function parse(){
        $html = array();
        switch(self::$_type){
            case'private':
                if(\CMS::$_user->_uid == DEFAULT_USER){
                    \CMS::redirect('login');
                }else{
                    \Html::set('{content}', self::build_private());
                }
                break;
            case'public':
                \Html::set('{content}', self::build_public());
                break;
            case'edit':
                if(\CMS::$_vars[2] == 'submit'){
                    self::submit_edit();
                }
                \Html::set('{content}', self::build_edit());
                break;
        }
    }
    
    private static function build_private(){
        $html = array();
        $html[] = '<div id="profile"><h4 class="temporal-usermod-title">'.\CMS::$_user->_data['name'].'</h4>';
        
        $content = array();
        foreach(\CMS::$_user->_modules as $usermod){
            $data = $usermod->profile('private');
            $check = false;
            $i = $data[1];
            while(!$check){
                if(!array_key_exists($i, $content)){
                    $content[$i] = $data[0];
                    $check = true;
                }
                $i++;
            }
        }
        ksort($content);
        foreach($content as $v){
            $html[] = $v;
        }
        $html[] = '<p style="clear:both">&nbsp;</p></div>';
        return implode(PHP_EOL, $html);
        
    }
    
    private static function build_public(){
        $user = new \User(self::$_userid);
        if($user->_error){
            return '<div id="profile"><h2>User Not Found</h2></div>';
        }
        $html = array();
        $html[] = '<div id="profile"><h2>'.$user->_data['name'].'</h2>';
        $content = array();
        foreach($user->_modules as $usermod){
            $data = $usermod->profile('public');
            $check = false;
            $i = $data[1];
            while(!$check){
                if(!array_key_exists($i, $content)){
                    $content[$i] = $data[0];
                    $check = true;
                }
                $i++;
            }
        }
        ksort($content);
        foreach($content as $v){
            $html[] = $v;
        }
        $html[] = '<p style="clear:both">&nbsp;</p></div>';
        return implode(PHP_EOL, $html);
    }
    
    
    private static function build_edit(){
        $html = self::block('edit.html');
        foreach(self::$_status as $v){
            $html = str_replace('{status}', $v.'{status}', $html );
        }
        $html = str_replace('{status}', '', $html );
        $html = str_replace('{setname}', \CMS::$_user->_data['name'], $html );
        $html = str_replace('{setemail}', \CMS::$_user->_data['email'], $html );
        
        $content = array();
        foreach(\CMS::$_user->_modules as $usermod){
            $data = $usermod->edit_html();
            $check = false;
            $i = $data[1];
            while(!$check){
                if(!array_key_exists($i, $content)){
                    $content[$i] = $data[0];
                    $check = true;
                }
                $i++;
            }
        }
        ksort($content);
        foreach($content as $v){
            $html = str_replace('{modules}', $v.'{modules}', $html );
        }
        $html = str_replace('{modules}','', $html );
        return $html;
    }
    
    private static function submit_edit(){
        if(!self::dup_email_check($_POST['email'], $_SESSION['user'])){
            self::$_status[] = '<div class="alert alert-error">Email address already in use.</div>';
            return false;
        }
        
        if(!self::dup_user_check($_POST['name'], $_SESSION['user'])){
            self::$_status[] = '<div class="alert alert-error">Username address already in use.</div>';
            return false;
        }
        $sql = 'UPDATE `users` SET 
            `email` = \'' . \DB::clean($_POST['email']) . '\',
            `name` = \'' . \DB::clean($_POST['name']) . '\'
            WHERE `uid` = \'' . \DB::clean($_SESSION['user']) . '\' LIMIT 1';
        \DB::q($sql);
        self::$_status[] = '<div class="alert alert-block">Profile Updated</div>';
        $content = array();
        foreach(\CMS::$_user->_modules as $usermod){
            $usermod->update();
        }
        \CMS::$_user = new \User(\CMS::$_user->_uid);
    }
    
    public static function  dup_email_check($email, $uid){
        $sql = 'SELECT uid, email FROM `users`';
	$list = \DB::q($sql);
        foreach($list as $v){
            if(trim(strtolower($email)) == strtolower($v['email'])){
                if($v['uid'] != $uid){
                    return false;
                }
            }
        }
        return true;
    }
    
    public static function  dup_user_check($name, $uid){
        $sql = 'SELECT uid, name FROM `users`';
	$list = \DB::q($sql);
        foreach($list as $v){
            if(trim(strtolower($name)) == strtolower($v['name'])){
                if($v['uid'] != $uid){
                    return false;
                }
            }
        }
        return true;
    }
}