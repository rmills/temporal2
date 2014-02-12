<?php

namespace Module;

class Facebook_tags extends Module {
    
    public static function __registar_callback() {
        if(ENABLE_FB_LOGIN){
            \CMS::callstack_add('add_inc', DEFAULT_CALLBACK_PARSE+10);
        }
    }

    public static function add_inc() {
        \Html::set('{footer}', self::block('fb_footer_inc.html'));
        if(\FacebookAPI::$user->uid !== 0) {
          
            \Html::set('{fb_login}', '
                <div id="fb-login-wrapper">
                <!--
                  <div class="notications-wrapper btn-group">
                    <a title="notications" class="dropdown-toggle notications-link" data-toggle="dropdown">3</a>
                    <ul class="news dropdown-menu">
                          <li class="top-nav-sub-arrow"><img src="{root_images}nav-sub-uparrow.png"></li>
                        <li>test 1</li>
                        <li>test 2</li>
                        <li>test 3</li>
                    </ul>
                  </div>
                  -->
                  <div class="links">
                      <i class="icon-user icon-white"></i><a class="user-link" href="/profile">'.  \FacebookAPI::$user->first_name.'</a><a href="/logout">Logout</a>
                  </div>
                </div>
            ');

            \Html::set('{fb_login_mobile}', '
                <a href="/logout">Logout</a>
            ');
          
        }else{
            $path = 'http://'.DOMAIN.$_SERVER['REQUEST_URI'];
            $path = self::filter_path($path);
            $login_prams = array('scope' => 'email,user_photos,user_videos', 'fbconnect' => 0,'redirect_uri' => $path ,'display' => 'page', 'cookie' => 1);
            $loginUrl = \FacebookAPI::$api->getLoginUrl($login_prams);
            \Html::set('{fb_login}', '
              <div id="fb-login-wrapper">
                <div class="links">
                    <a id="fb-login" title="Facebook Log in" href="'.$loginUrl.'">Log in with Facebook</a>
                </div>
              </div>
          ');
            
          \Html::set('{fb_generic_login}', '
              <a class="fb-login-generic" title="Facebook Log in" href="'.$loginUrl.'">Log in</a>
          ');
          
          \Html::set('{fb_generic_login_btn}', '
              <a class="btn btn-primary" title="Facebook Log in" href="'.$loginUrl.'">Log In</a>
          ');
            
          \Html::set('{fb_login_mobile}', '
              <a title="Facebook Login" href="'.$loginUrl.'">Log in with Facebook</a>
          ');
          
        }
    }
    
    public static function filter_path($path){
        $path = str_replace('/ajax', '', $path);
        return $path;
    }
    
    public static function ajax_login_tag($path){
        $path = 'http://'.DOMAIN.'/'.$path;
        $path = self::filter_path($path);
        $login_prams = array('scope' => 'email,user_photos,user_videos', 'fbconnect' => 0,'redirect_uri' => $path ,'display' => 'page', 'cookie' => 1);
        $loginUrl = \FacebookAPI::$api->getLoginUrl($login_prams);
        return '
            <div class="fb-login-btn">
                <h3>You need to be logged in to use this feature</h3>
                <p>&nbsp;</p>
                <p><a id="fb-login" title="Facebook Log in" href="'.$loginUrl.'"><img src="/site/layout/wsu1/images/facebook-login-button.png" title="Log in with Facebook" alt="Log in with Facebook"></a></p>
            </div>
      ';
        
    }

}