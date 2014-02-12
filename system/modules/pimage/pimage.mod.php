<?php
namespace Module;

if (!defined('PIMAGE_1_H')) {
    define('PIMAGE_1_H', 400);
}

if (!defined('PIMAGE_1_SQUARE')) {
    define('PIMAGE_1_SQUARE', 0);
}

if (!defined('PIMAGE_2_H')) {
    define('PIMAGE_2_H', 400);
}

if (!defined('PIMAGE_3_SQUARE')) {
    define('PIMAGE_3_SQUARE', 0);
}

if (!defined('PIMAGE_4_H')) {
    define('PIMAGE_4_H', 400);
}

if (!defined('PIMAGE_5_SQUARE')) {
    define('PIMAGE_5_SQUARE', 0);
}

if (!defined('PIMAGE_6_H')) {
    define('PIMAGE_6_H', 400);
}

if (!defined('PIMAGE_7_SQUARE')) {
    define('PIMAGE_7_SQUARE', 0);
}

if (!defined('PIMAGE_8_H')) {
    define('PIMAGE_8_H', 400);
}

if (!defined('PIMAGE_9_SQUARE')) {
    define('PIMAGE_9_SQUARE', 0);
}

if (!defined('PIMAGE_10_H')) {
    define('PIMAGE_10_H', 400);
}

if (!defined('PIMAGE_11_SQUARE')) {
    define('PIMAGE_11_SQUARE', 0);
}

if (!defined('PIMAGE_12_H')) {
    define('PIMAGE_12_H', 400);
}

if (!defined('PIMAGE_13_SQUARE')) {
    define('PIMAGE_13_SQUARE', 0);
}

if (!defined('PIMAGE_13_H')) {
    define('PIMAGE_13_H', 400);
}

if (!defined('PIMAGE_14_SQUARE')) {
    define('PIMAGE_14_SQUARE', 0);
}

if (!defined('PIMAGE_15_H')) {
    define('PIMAGE_15_H', 400);
}

if (!defined('PIMAGE_16_SQUARE')) {
    define('PIMAGE_16_SQUARE', 0);
}

if (!defined('PIMAGE_17_H')) {
    define('PIMAGE_17_H', 400);
}

if (!defined('PIMAGE_18_SQUARE')) {
    define('PIMAGE_18_SQUARE', 0);
}

if (!defined('PIMAGE_19_H')) {
    define('PIMAGE_19_H', 400);
}

if (!defined('PIMAGE_20_SQUARE')) {
    define('PIMAGE_20_SQUARE', 0);
}

class Pimage extends Module{
    public static function __registar_callback() {
        \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
        \CMS::callstack_add('admin', DEFAULT_CALLBACK_PARSE);
    }
    
    public static function parse(){
        if(\CMS::$_content_type == 'html'){
            $images = array();
            
            $sql = 'SELECT * FROM `pimage`';
            $list = \DB::q($sql);
            if (is_array($list)) {
                foreach ($list as $v) {
                    $image = new \Image($v['image']);
                    $images[$v['zone']] = $image->thumbnail( constant('PIMAGE_'.$v['zone'].'_H'), constant('PIMAGE_'.$v['zone'].'_SQUARE') );
                }
            }
            $i = 1;
            while($i<21){
                if(isset($images[$i])){
                    \Html::set('{pimage'.$i.'}','<div id="pimage-'.$i.'-image">'.$images[$i].'</div>');
                }else{
                    \Html::set('{pimage'.$i.'}','<div id="pimage-'.$i.'-image"></div>');
                }
                $i++;
            }
            
        }
    }
    
    public static function admin(){
        
        if(\CMS::$_content_type == 'html'){
            $i = 1;
            while($i<21){
                $block = self::block('upload.html');
                $block = str_replace('{zone}', $i, $block);
                \Html::set('{pimage'.$i.'}', $block );
                $i++;
            }
        }
    }
    
}