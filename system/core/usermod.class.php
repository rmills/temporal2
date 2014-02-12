<?php

class UserMod{
    
    /**
     * @var string where data is store
     */
    protected $_table = false;
    
    /**
     * @var type user id
     */
    protected $_uid = false;
    
    /**
     * @var type user id
     */
    protected $_mid = false;
    
    /**
     * @var mixed content stored in database serialized
     */
    public $_data = false;
    
    /**
     * @var object class
     */
    protected $_class = false;
    
    /**
     * Load based on class and uid
     * @param type $class
     * @param type $uid
     * @return type
     */
    function __construct($class, $uid, $mid) {
        $this->_uid = $uid; 
        $this->_class = $class; 
        $this->_mid = $mid;
        return $this->init();
    }
    
    /**
     * Fetch data from database
     */
    function init(){
        $sql = 'SELECT data FROM `usermod` WHERE `mid` = "'.DB::clean($this->_mid).'" AND `uid` = "'.DB::clean($this->_uid).'" LIMIT 1';
        $response = DB::q($sql);
        if(count($response)){
            $this->_data = unserialize($response[0]['data']);
        }else{
            $this->init_db_data();
        }
    }
    
    /**
     * Used to init a new row for a module or user
     */
    function init_db_data(){
        $sql = '
            INSERT INTO `usermod` (
                `mid`,
                `uid`,
                `data`
            ) VALUES (
                \''.DB::clean( $this->_mid ).'\',
                \''.DB::clean( $this->_uid ).'\',
                \''.DB::clean( serialize($this->_data) ).'\'
            )';
        DB::q( $sql );
        $this->_data = $this->_data;
    }
    
    /**
     * Updates content saved
     */
    public function save(){
        $sql = 'UPDATE `usermod` SET `data` = \''.DB::clean(  serialize($this->_data) ).'\' WHERE `mid` = "'.DB::clean($this->_mid).'" AND `uid` = "'.DB::clean($this->_uid).'" LIMIT 1';
        DB::q( $sql );
    }
}

interface iUserMod
{
    public function update();
    public function edit_html();
    public function profile($type);
}
