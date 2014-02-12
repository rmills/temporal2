<?php
/**
 * @author Ryan Mills <ryan@ryanmills.net>
 * 
 * Simple base class for dealing with items in a database
 */
class AutoDB{
    
    /**
     * Internal settings
     * @var array interal config settings
     */
    public $_config = array('table'=>'default_table', 'id_field'=>'id', 'id'=>0);
    
    /**
     * Database fields in the table set by the config
     * @var type 
     */
    public $_fields = array();
    
    /**
     * This class must be init by setting the database table, id field and id
     * @param string $table name of the table
     * @param string $id_field id field
     * @param int $id id of the item
     */
    public function __construct($table = 'default_table', $id_field = 'id', $id = 0){
        if($table){
            $this->_config['table'] = $table;
        }
        if($id_field){
            $this->_config['id_field'] = $id_field;
        }
        if($id){
            $this->_config['id'] = $id;
        }
        $sql = 'SHOW columns FROM '.$this->_config['table'].';';
        $this->_fields = \DB::q($sql);
    }
    
    /**
     * Loads and item from the database
     * @param int $id item id
     * @param string $id_field override id field
     * @return boolean false on fail
     */
    public function load($id = false, $id_field = false){
        if(!$id){
           $id =  $this->_config['id'];
        }else{
            $this->_config['id'] = $id;
        }
        
        if(!$id_field){
           $id_field = $this->_config['id_field'];
        }else{
            $this->_config['id_field'] = $id_field;
        }
        
        $table = $this->_config['table'];
        
        $sql = 'SELECT * FROM `'.\DB::clean($table).'` WHERE `'.\DB::clean($id_field).'` = \''.\DB::clean($id).'\' LIMIT 1';
        $try = \DB::q($sql);
        if(is_array($try)){
            if(count($try)){
                foreach($try[0] as $k=>$v){
                    $this->{$k} = $v;
                    
                }
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * Cross check fields with db and insert
     * @param array $array init item
     * @return boolean id on true, false on fail
     */
    public function init($array = array()){
        if(is_array($array)){
            $keys = array();
            $values = array();
            foreach($array as $k =>$v){
                $valid = false;
                foreach($this->_fields as $v2){
                    if($v2['Field'] == $k){
                        $valid = true;
                    }
                }
                
                if($valid){
                    $keys[] = $k;
                    if(is_array($v)){
                        $values[] = implode(' ', $v);
                    }else{
                        $values[] = $v;
                    }
                }
            }
            $sql = 'INSERT INTO '.\DB::clean($this->_config['table']).' (';
            $i1 = 1;
            $t1 = count($keys);
            foreach($keys as $v){
                if($i1 == $t1){
                    $sql .= '`'.$v.'`'.PHP_EOL;
                }else{
                    $sql .= '`'.$v.'`,'.PHP_EOL;
                }
                $i1++;
            }
            $sql .= ') VALUES (';

            $i2 = 1;
            $t2 = count($values);
            foreach($values as $v){
                if($i2 == $t2){
                    $sql .= '\'' . \DB::clean($v).'\''.PHP_EOL;
                }else{
                    $sql .= '\'' . \DB::clean($v).'\','.PHP_EOL;
                }
                $i2++;
            }
            $sql .= ')';
            \DB::q($sql);
            return \DB::$_lastid;
        }else{
            return false;
        }
    }
    
    /**
     * Upload sync class with database
     */
    public function update(){
        //$class_vars = get_class_vars(get_class($this));
        $sql = 'UPDATE `'.\DB::clean($this->_config['table']).'` SET ';
        foreach($this as $k=>$v){
            $valid = false;
            foreach($this->_fields as $v2){
                if($v2['Field'] == $k){
                    $valid = true;
                }
            }
            if($valid){
              $sql .=  '`'.\DB::clean($k).'` = \'' . \DB::clean($this->{$k}) . '\',';
            }
        }
        $sql = substr($sql, 0, (strlen($sql)-1));
        $sql .= ' WHERE `' . \DB::clean($this->_config['id_field']) . '` = \'' . \DB::clean($this->_config['id']) . '\' LIMIT 1 ';
        \DB::q($sql);
    }
    
}