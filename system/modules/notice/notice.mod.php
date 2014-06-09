<?php

namespace Module;
class Notice extends Module{
    public static function info($string){
        return '
            <div class="alert alert-info">
                <button type="button" class="close" data-dismiss="alert">×</button>
                '.$string.'
            </div>
            ';
    }
    
    public static function warn($string){
        return '
            <div class="alert alert-warning">
                <button type="button" class="close" data-dismiss="alert">×</button>
                '.$string.'
            </div>
            ';
    }
    
    public static function error($string){
        return '
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>
                '.$string.'
            </div>
            ';
    }
    
    public static function success($string){
        return '
            <div class="alert alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>
                '.$string.'
            </div>
            ';
    }
}