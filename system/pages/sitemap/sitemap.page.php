<?php
namespace Page;
class Sitemap extends Page{
    private static $stack = array();
    
    public static function active() {
        \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
    }
    
    public static function register($url = false, $freq = false, $priority = '0.5'){
        $obj = array();
        
        if($url){
            $obj['loc'] = $url;
        }
        if($freq){
            $obj['changefreq'] = $freq;
        }
        if($priority){
            $obj['priority'] = $priority;
        }
        self::$stack[] = $obj;
    }
    
    public static function parse(){
        \CMS::$_page_type = 'sitemap';
        \CMS::$_content_type = 'xml';
        
        foreach(\CMS::$__modules as $v){
            $module = ucwords($v[1]);
            $name = '\Module\\'.$module;
            $try = method_exists($name, '__static_callback');
            if ($try) {
                $try = call_user_func(array($name, '__static_callback'), 'Sitemap');
                if($try){
                    foreach ($try as $v){
                        self::register($v);
                    }
                }
            }
        }
        
        foreach(\CMS::$__pages as $v){
            $module = ucwords($v[1]);
            $name = '\Page\\'.$module;
            $try = method_exists($name, '__static_callback');
            if ($try) {
                $try = call_user_func(array($name, '__static_callback'), 'Sitemap');
                if($try){
                    foreach ($try as $v){
                        self::register($v);
                    }
                }
            }
        }
        
        $body = array();
        $body[] = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>';
        $body[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach( self::$stack as $item){
            $body[] = '<url>';
            foreach( $item as $k=>$v ){ 
                $body[] = '<'.$k.'>'.$v.'</'.$k.'>';
            }
            $body[] = '</url>';
        }
        $body[] = '</urlset>';
        
        file_put_contents('sitemap.xml', implode(PHP_EOL, $body));
        \XML::$_body = implode(PHP_EOL, $body);
    }
}