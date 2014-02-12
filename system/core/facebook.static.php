<?php
/**
 * @author Ryan Mills <ryan@ryanmills.net>
 * 
 * Facebook user sync
 * 
 */

require_once("site/inc/facebook-php-sdk-master/src/facebook.php");
require_once("site/inc/facebook_user.class.php");

class FacebookAPI{
    /**
     * Bridge to Fb API
     * @var \Facebook()  
     */
    static public $api = false;
    
    /**
     * User object
     * @var Facebook_user
     */
    static public $user = false;
    
    /**
     * Load errors
     * @var type 
     */
    static public $api_errors = array();
    
    /**
     * Init the class
     */
    public static function init(){
        $config = array();
        $config['appId'] = FB_AUTH_KEY;
        $config['secret'] = FB_AUTH_SECRECT;
        $config['fileUpload'] = true; // optional
        $config['cookie'] = true; // optional
        
        self::$api = new \Facebook($config);
        $user_data = self::$api->getUser();
        if ($user_data) {
          try {
            $user = self::$api->api('/me');
            if($user['id'] !== 0 ){
                self::$user = new Facebook_user($user);
                self::$user->inflate(false);
                $_SESSION['user'] = self::$user->lid;
                \CMS::$_user = new \User(self::$user->lid);
                self::$user->update_check();
            }else{
                $_SESSION['user'] = DEFAULT_USER;
                \CMS::$_user->destroy();
                \CMS::$_user = new \User(DEFAULT_USER);
                self::$user = new Facebook_user(0);
                \CMS::redirect('home');
            }
          } catch (\FacebookApiException $e) {
            self::$user = new Facebook_user(0);
            $_SESSION['user'] = DEFAULT_USER;
            \CMS::$_user->destroy();
            \CMS::$_user = new \User(DEFAULT_USER);
            self::$user = new Facebook_user(0);
            \SiteDebug::log($e);
          }
        }else{
            self::$user = new Facebook_user(0);
        }
    }
}
FacebookAPI::init();