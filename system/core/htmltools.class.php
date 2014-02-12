<?php
class HTMLTools {
    public static function c($html){
        $html = strip_tags($html, '<br>');
        $html = str_replace("onmouseover", '', $html);
        $html = str_replace("style=", '', $html);
        $html = str_replace("onclick", '', $html);
        $html = str_replace("javascript", '', $html);
        $html = str_replace("iframe", '', $html);
        $html = str_replace("object", '', $html);
        $html = str_replace("embed", '', $html);
        return $html;
    }
    
    public static function c_all($html){
        $html = strip_tags($html);
        $html = str_replace("onmouseover", '', $html);
        $html = str_replace("style=", '', $html);
        $html = str_replace("onclick", '', $html);
        $html = str_replace("javascript", '', $html);
        $html = str_replace("iframe", '', $html);
        $html = str_replace("object", '', $html);
        $html = str_replace("embed", '', $html);
        return $html;
    }
    
    public static function format_linebreaks($html){
        $html = str_replace("\n", '<br>', $html);
        return $html;
    }
    
    public static function format_linebreaks_edit($html){
        $html = str_replace("<br>", "\n", $html);
        return $html;
    }
}