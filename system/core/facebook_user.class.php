<?php

class Facebook_user extends \AutoDB{
    
    /*
     * Class vars are auto loaded from the database fields. They are here JUST
     * for code hinting. 
     */
    public $uid;
    public $id;
    public $name;
    public $first_name;
    public $last_name;
    public $link;
    public $username;
    public $gender;
    public $timezone;
    public $locale;
    public $verified;
    public $updated_time;
    public $email;
    
    /* addon vars for internal use */
    public $updated_time_epoch;
    public $init_ip;
    public $karma;
    public $profile_image;
    public $lid;
    public $bio;
    
    /*private vars*/
    private $_current_fb_data;
    
    /**
     * Create new Fackbook_user object
     * @param type $fb_data array from api /me call
     * @param type $inflate disable auto inflate, you will have to call $this->load
     * after your ready 
     */
    public function __construct($fb_data, $id_name = false){
        if(!$fb_data){
            $this->load_guest();
        }else{
            $this->_current_fb_data = $fb_data;
            if($id_name){
                parent::__construct('fb_user', $id_name, $fb_data['id']);
            }else{
                parent::__construct('fb_user', 'id', $fb_data['id']);
            }
        }
    }
    
    public function inflate($load_only = true){
        $check = $this->load();
        if(!$check){
            if(!$load_only){
                $this->init_new_user($this->_current_fb_data);
                return true;
            }
            return false;
        }
        return true;
    }
    
    /**
     * Add first time user to DB
     * @param array $fb_data
     */
    public function init_new_user($fb_data){
        $this->uid = $this->init($fb_data);
        $id = \Module\Admin_user::remote_adduser($fb_data['email'], $fb_data['name'], DEFAULT_GROUPS, 'fb', $this->uid);
        $this->load();
        $this->lid = $id;
        $_SESSION['user'] = $id;
        $this->init_ip = $_SERVER['REMOTE_ADDR'];
        $this->update();
    }
    
    
    /**
     * Add first time user to DB
     * @param array $fb_data
     */
    public function load_guest(){
        $this->uid = 0;
        $this->id = 0;
    }
    
    /**
     * Fetch Fb avatar and store it locally as a DImage
     */
    public function load_profile_photo(){
        $path = 'http://graph.facebook.com/'.$this->id.'/picture?width='.FACEBOOK_PROFILE_PHOTO_WIDTH.'&height='.FACEBOOK_PROFILE_PHOTO_HEIGHT;
        $image = new DImage();
        $raw_image = file_get_contents($path);
        $image->create($raw_image);
        $image->title = $this->name;
        $image->catagory = 3;
        $image->update();
        $this->profile_image = $image->id;
    }
    
    /**
     * Check if user has change any tracked fb properties
     */
    public function update_check(){
        if(strtotime($this->updated_time) < strtotime($this->_current_fb_data['updated_time']) || $this->updated_time === 0){
            foreach($this->_current_fb_data as $k=>$v){
                $this->{$k} = $v;
            }
            $this->load_profile_photo();
            $this->update();
        }
        
        if(!$this->profile_image){
            $this->load_profile_photo();
            $this->update();
        }
    }
}

