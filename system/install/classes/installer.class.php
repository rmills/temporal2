<?php
class Installer{
    public static $db = false;
    public static $db_error = false;
    public static $page_errors = array();
    
    public static function checkdb() {
        self::$db = @new mysqli(
            DATABASE_HOST,
            DATABASE_USER,
            DATABASE_PASSWORD,
            DATABASE_TABLE
        );

        if (mysqli_connect_errno()) {
            self::$db_error = @mysqli_connect_error(self::$db);
            return false;
        }else{
            return true;
        }
    }
    
    public static function init_db($sql){
        
        if(DATABASE_PASSWORD == ''){
            $pass = "";
        }else{
            $pass = ' -p'.DATABASE_PASSWORD;
        }
        
        $command = 'mysql -v -u '.DATABASE_USER.$pass.' -h '.
                DATABASE_HOST.' -D '.DATABASE_TABLE.' < '. $sql;
        $check = shell_exec($command);
        self::$db_error = $check;
        return $check;
    }
    
    public static function init_user($name, $email, $password){
        $email = trim(strtolower($email));
        $name = trim($name);
        $salt = self::hash();
        $password = md5($salt . trim($password) . $salt);
        
        
        $sql = '
            INSERT INTO users (
                `email`,
                `name`,
                `password`,
                `salt`,
                `groups`,
                `status`,
                `super_user`,
                `date_create`,
                `last_ip`
            ) VALUES (
                \'' . self::clean($email) . '\',
                \'' . self::clean($name) . '\',
                \'' . self::clean($password) . '\',
                \'' . self::clean($salt) . '\',
                \'' . REGISTER_DEFAULT_GROUPS . '\',
                \'active\',
                \'yes\',
                \'' . date("Ymd") . '\',
                \'' . $_SERVER['REMOTE_ADDR'] . '\'
            )';
        @mysqli_query(self::$db, $sql);
        return mysqli_error(self::$db);
    }
    
    public static function hash($length = 20) {
        $random = "";
        srand((double) microtime() * 1000000);
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        $char_list .= "1234567890";
        // Add the special characters to $char_list if needed

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($char_list, (rand() % (strlen($char_list))), 1);
        }
        return $random;
    }
    
    public static function clean($input) {
        return mysqli_real_escape_string(self::$db, $input);
    }
}
