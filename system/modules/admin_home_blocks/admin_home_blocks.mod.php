<?php

namespace Module;

class Admin_home_blocks extends Module {
    static $groups = array();
    static $group_wrapper = '<h4>{title}</h4><div class="admin-home-icons-wrapper">{icons}</div>';
    
    public static function __registar_callback() {
        if (\CMS::$_vars[0] == 'admin' && \CMS::$_vars[1] == '') {
            \CMS::callstack_add('build_home_blocks', DEFAULT_CALLBACK_PARSE+5);
        }
    }
    
    public static function add($title, $blocks = array(), $weight = 0){
        self::$groups[] = array('title'=>$title, 'blocks'=>$blocks, 'weight'=>$weight);
    }
    
    public static function build_home_blocks(){
        $html = array();
        
        usort(self::$groups, \CMS::make_comparer('weight'));
        foreach(self::$groups as $v){
            $group = str_replace('{title}', $v['title'], self::$group_wrapper);
            foreach($v['blocks'] as $v2){
                $group = str_replace('{icons}', $v2->html_block().'{icons}', $group);
            }
            $group = str_replace('{icons}', '', $group);
            $html[] = $group;
        }
        \Html::set('{admin_home_blocks}', implode(PHP_EOL, $html));
    }
}

class AdminHomeBlock{
    public $title;
    public $link;
    public $icon;
    public $icon_size = 'fa-4x';
    
    /**
     * 
     * @param string $title
     * @param string $link
     * @param string $icon
     */
    public function __construct($title, $link, $icon) {
        $this->title = $title;
        $this->link = $link;
        $this->icon = $icon;
    }
    
    public function html_block(){
        return '<div class="admin-home-icon"><a class="link" href="'.$this->link.'"><i class="image fa '.$this->icon.' '.$this->icon_size.'" aria-hidden="true"></i><br><span class="title">'.$this->title.'</span></a></div>';
    }
}