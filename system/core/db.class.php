<?php
if(!defined('ALLOW_DB_DEBUG')){
    define('ALLOW_DB_DEBUG', false);
}
class DB {

    private static $__connection = false;
    private static $__querys = array();
    public static $_lasterror = false;
    public static $_lastid = 0;

    /**
     * Connect to database
     */
    public static function init() {
        $DB = false;
        self::$__connection = new mysqli(
                        DATABASE_HOST,
                        DATABASE_USER,
                        DATABASE_PASSWORD,
                        DATABASE_TABLE
        );

        if (mysqli_connect_errno()) {
            printf("Site offline for updates, Code: 1");
            exit();
        }
    }
    
    /**
     * Make a database call
     * @param sql $sql
     * @return mixed 
     */
    public static function q($sql) {
        /** store query for debug * */
        self::$__querys[] = $sql;

        /** run * */
        $data = array();
        $free_result = false;
        $result = mysqli_query(self::$__connection, $sql);
        if (gettype($result) != 'boolean') {
            $free_result = true;
            while ($r = $result->fetch_assoc()) {
                $data[] = $r;
            }
        } else {
            if ($result) {
                $data = $result;
            } else {
                $error = mysqli_error(self::$__connection);
                if ($error) {
                    CMS::log('DB', 'SQL ERROR (next two lines)', 2);
                    CMS::log('DB', $sql, 2);
                    CMS::log('DB', $error, 2);
                }
                $data = false;
            }
        }

        self::$_lastid = mysqli_insert_id(self::$__connection);
        self::$_lasterror = mysqli_error(self::$__connection);
        
        if(ALLOW_DB_DEBUG){
            if(self::$_lasterror){
                echo '<br><h2>SQL DEBUG</h2><p>'.$sql.'</p><p>'.self::$_lasterror.'</p>';
                $trace = debug_backtrace();
                echo '<p><strong>Line:</strong> '.$trace[0]['line'].'&nbsp;&nbsp;&nbsp;<strong>File:</strong> '.$trace[0]['file'].'</p>';
                if(isset($trace[1]['line'])){
                    echo '<p><strong>Line:</strong> '.$trace[1]['line'].'&nbsp;&nbsp;&nbsp;<strong>File:</strong> '.$trace[1]['file'].'</p>';
                }
                if(isset($trace[2]['line'])){
                    echo '<p><strong>Line:</strong> '.$trace[2]['line'].'&nbsp;&nbsp;&nbsp;<strong>File:</strong> '.$trace[2]['file'].'</p>';
                }
                echo '</p><br><br>';
            }
        }else{
            if(self::$_lasterror){
                try{
                    //\SiteDebug::log( 'SQL DEBUG: '.$sql.' :: Last Error: '.self::$_lasterror);
                }catch (Exception $e)  {
                    //ignore, not loaded yet
                }
            }
        }
        
        if ($free_result) {
            mysqli_free_result($result);
        }
        return $data;
    }
    
    
    /**
     * Get name, type and max len for a row.
     * 
     * @param string $table table name
     * @param int $id id for row to fetch
     * @param string $id_field name of field
     * @return array contains name, type, max_len
     */
    public static function fetch_field_data_for_id($table, $id, $id_field = 'id') {
        $sql = 'SELECT * FROM \''.self::clean($table).'\' WHERE `'.self::clean($id_field).'` = \''.self::clean($id).'\'';
        
        /** store query for debug * */
        self::$__querys[] = $sql;
        $result = mysqli_query(self::$__connection, $sql);
        $data = array();
        if ($result) {

            /* Get field information for all columns */
            $finfo = $result->fetch_fields();

            foreach ($finfo as $val) {
                $data[] = array('name'=>$val->name, 'type'=>$val->type, 'max_len'=>$val->max_length);
            }
            $result->close();
        }
        return $data;
    }
    
    public static function fetch_fields($table){
        /** store query for debug * */
        $sql = 'SHOW columns FROM '.self::clean($table);
        self::$__querys[] = $sql;
        $result = mysqli_query(self::$__connection, $sql);
        $data = array();
        if ($result) {
            while ($r = $result->fetch_assoc()) {
                $data[] = $r;
            }
            $result->close();
        }
        return $data;
    }
    
    /**
     * Sanitise user input for SQL querys, not binary safe
     * @param string $input
     * @return string
     */
    public static function clean($input) {
        return mysqli_real_escape_string(self::$__connection, $input);
    }

}