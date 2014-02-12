<?php

namespace Module;
class Ban_check extends Module{
    
    static $bans = array();
    
    public static function __registar_callback() {
        \CMS::callstack_add('check_ban', DEFAULT_CALLBACK_SETUP-1);
    }
    
    public static function check_ban(){
        $sql = 'SELECT * FROM `blocks` WHERE `user` = \''.\DB::clean(\CMS::$_user->uid).'\'';
        $check = \DB::q($sql);
        $has_bans = true;
        foreach($check as $v){
            if($v['expires'] > time()){
                
                $has_bans = true;
                if($v['type'] == 'ryan_ban'){
                    self::display_ban_page_ryan();
                    die();
                }
                $ban =  new \AutoDB('blocks');
                $ban->load($v['id']);
                self::$bans[] = $ban;
            }
            
        }
        
        if(count(self::$bans)){
            $body = self::block('ban_page_wrapper.html');
            $details_wrapper = self::block('ban_details.html');
            $ban_html = '';
            foreach(self::$bans as $v){
                $ban = $details_wrapper;
                
                $issued = \DateTools::past_date(time(), $v->issued);
                $ban = str_replace('{issued}', $issued, $ban);
                
                $expired = \DateTools::future_date(time(), $v->expires);
                $ban = str_replace('{expires}', $expired, $ban);
                
                $ban = str_replace('{reason}', $v->reason, $ban);
                $ban_html .= $ban;
            }
            $body = str_replace('{bans}', $ban_html, $body);
            echo $body;
            die();
        }
        
        
    }
    
    public static function display_ban_page($details_id){
        echo self::block('ban_page_wrapper.html');
        die();
    }
    
    public static function display_ban_page_ryan(){
        echo self::block('ban_ryan.html');
        die();
    }
}