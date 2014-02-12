<?php
if(!defined('PARSER_OPEN_TOKEN')){
    define('PARSER_OPEN_TOKEN', '<*');
}

if(!defined('PARSER_CLOSE_TOKEN')){
    define('PARSER_CLOSE_TOKEN', '*>');
}

if(!defined('ENABLE_PARSER')){
    define('ENABLE_PARSER', false);
}
/*
 * The parser allows it to load a block of html and parse tags.
 * 
 * Tag Format:
 * <*class=method||var1||var2||var3*>
 * 
 * 
 * 
 * This is an experimental class.
 */
class Parser{
    /**
     * Use these constants to check the current state of the parser
     */
    const STATE_EMPTY = 'stateEmpty';
    const STATE_READY = 'stateReady';
    const STATE_PARSED = 'stateParsed';
    const STATE_ACTIVE = 'stateActive';
    const STATE_ERROR = 'stateError';
    
    /**
     * Used to store a stack of valid classes that can be called via tags
     * @var <array> 
     */
    static private $callbacks = array();
    
    /**
     * Stores the content
     * @var <string> 
     */
    static private $body = '';
    
    /**
     * Current state of the parser
     * @var type 
     */
    static private $state = self::STATE_EMPTY;
    
    /**
     * Holds parsers start time
     * @var <float> 
     */
    static private $time_start = 0;
    
    /**
     * Holds parsers exec time
     * @var <float> 
     */
    static public $time_exec = 0;
    
    /**
     * Holds parsers exec time
     * @var <float> 
     */
    static public $error = '';
   
    
    
    
    
    
    
    /**
     * Getter/Setter
     * @param <string> $html
     * @return <string> 
     */
    public static function body($body = false){
        if($body){
            self::$state = self::STATE_READY;
            self::$body = $body;
        }
        return self::$body;
    }
    
    /**
     * Register a class with the parser, this must be done for the parser to
     * call the object.
     * @param <class> $class
     */
    public static function register($class){
        self::$callbacks[] = $class;
    }
    
    /**
     * Pase and excute tag
     * 
     * Tag Format:
     * <*class=method||var1||var2||var3*>
     * @param <string> $string
     */
    private static function parse_tag($string){
        $string = str_replace('<*', '', $string);
        $string = str_replace('*>', '', $string);
        $string = explode('=', $string);
        if(isset($string[1])){
            $vars = explode('||', $string[1]);
        }else{
            return '[tag malformed]';
        }
        
        $class = $string[0];
        $method = array_shift($vars);
        $allow = false;
        foreach(self::$callbacks as $v){
            if($v == $class){
                $allow = true;
            }
        }
       
        
        if($allow){
            return call_user_func(array($class, $method), $vars);
        }else{
            return '[tag method not allowed]';
        }
        
        return '|tag|'.$class.'->'.$method.'|';
    }
    
    /**
     * Parse the loaded $body
     */
    public static function parse(){
        self::$time_start = self::time();
        if(self::$state != self::STATE_READY){
            self::$error = 'Parser is not in a ready stage, current state: '.self::$state;
            self::$state = self::STATE_ERROR;
            return false;
        }
        if(self::$body == ''){
            self::$state = self::STATE_ERROR;
            self::$error = 'Body is empty';
            return false;
        }
        self::$state = self::STATE_ACTIVE;
        
        $run = true;
        while($run){
            $found = false;
            $find_open = strpos(self::$body, PARSER_OPEN_TOKEN);
            if($find_open !== false){
                $find_close = strpos(self::$body, PARSER_CLOSE_TOKEN, $find_open);
                if($find_close !== false){
                    $found = true;
                    $tag = substr(self::$body, $find_open, (($find_close-$find_open)+strlen(PARSER_CLOSE_TOKEN)) );
                    $content = self::parse_tag($tag);
                    self::$body = str_replace($tag, $content, self::$body);
                }
            }
            if(!$found){
                $run = false;
            }
        }
        self::$state = self::STATE_PARSED;
        self::time_exec();
    }
    
    /**
     * wrapper for microtime()
     * 
     * Used for tracking exec time
     * 
     * @return <float>
     */
    private static function time()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    
    /**
     * Used for tracking exec time
     */
    private static function time_exec()
    {
        $current = self::time();
        self::$time_exec = $current-self::$time_start;
    }
}