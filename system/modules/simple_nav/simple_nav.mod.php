<?php

namespace Module;

class Simple_nav extends Module {
    
    public static function __registar_callback() {
        \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE+1);
    }

    public static function parse() {
        if(\HTML::find("{simple_nav}")){
            $nav_array = self::build_nav();
            if(count($nav_array)){
                $nav_html = self::format_nav($nav_array);
                \HTML::set("{simple_nav}", $nav_html);
            }else{
                \HTML::set("{simple_nav}");
            }
        }
    }
    
    public static function build_nav(){
        $sql = 'SELECT * FROM `pages` WHERE `published` = \'yes\' AND `status` = \'active\' ORDER BY `weight`';
        $data = \DB::q($sql);
        $items = array();
        if($data){
            foreach($data as $item){
                $items[] = array(
                    'pid'=>$item['pid'],
                    'parent'=>$item['parent'],
                    'url'=>$item['url'],
                    'menu_title'=>$item['menu_title'],
                    'weight'=>$item['weight'],
                    'place_holder_only'=>$item['place_holder_only']
                 );
            }
        }
        return $items;
    }
    
    public static function format_nav($array){
        $html = array();
        $html[] = '
            <nav class="navbar navbar-default" role="navigation">
                <div class="container-fluid">
                    <div class="collapse navbar-collapse" id="simple-nav">
                        <ul class="nav navbar-nav">
        ';
        foreach($array as $v){
            $html[] = '
                                <li><a href="'.$v['url'].'">'.$v['menu_title'].'</a></li>
            ';    
        }
        $html[] = '
                        </ul>
                    </div>
                </div>
            </nav>';
        return implode('', $html);
    }
}