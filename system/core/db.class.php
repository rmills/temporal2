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
     * Sanitise user input for SQL querys, not binary safe
     * @param string $input
     * @return string
     */
    public static function clean($input) {
        return mysqli_real_escape_string(self::$__connection, $input);
    }

}