<?php
namespace Appserve;
class Upload extends Appserve{
    public static $fail =  array('status'=>'fail', 'reason'=>'Bad/missing handler or type:');
    static $_isrestricted = true;
    public static function __registar_callback() {
        if(\CMS::allowed()){
            \CMS::callstack_add('setup', DEFAULT_CALLBACK_SETUP);
        }
    }
    
    public static function active(){
        \CMS::$_page_type = 'upload';
        \CMS::$_content_type = 'json';
        if( isset(\CMS::$_vars['2']) ){
            $handler = (strtolower(\CMS::$_vars['2']));
            if( array_key_exists($handler, Appserve::$_apps) ){
                $callback = '\Appserve\\'.ucfirst($handler);
                $data = $callback::call();
                \Json::body($data);
            }else{
                \Json::body(array('status'=>'fail', 'reason'=>'bad handler :'.$handler.':'));
            }
        }else{
            \Json::body(array('status'=>'fail', 'reason'=>'Bad/missing handler or type'));
        }
    }
    
    public static function setup(){
        /*
        \Html::set('{scripts}', '<script type="text/javascript" src="http://blueimp.github.io/JavaScript-Load-Image/js/load-image.min.js"></script>');
        \Html::set('{scripts}', '<script type="text/javascript" src="http://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>');
        
        \Html::set('{scripts}', '<script type="text/javascript" src="{root_doc}system/inc/js/vendor/jquery.ui.widget.js"></script>');
        \Html::set('{scripts}', '<script type="text/javascript" src="{root_doc}system/inc/js/jquery.iframe-transport.js"></script>');
        \Html::set('{scripts}', '<script type="text/javascript" src="{root_doc}system/inc/js/jquery.fileupload.js"></script>');
        \Html::set('{scripts}', '<script type="text/javascript" src="{root_doc}system/inc/js/jquery.fileupload-process.js"></script>');
        \Html::set('{scripts}', '<script type="text/javascript" src="{root_doc}system/inc/js/jquery.fileupload-image.js"></script>');
        \Html::set('{scripts}', '<script type="text/javascript" src="{root_doc}system/inc/js/jquery.fileupload-audio.js"></script>');
        \Html::set('{scripts}', '<script type="text/javascript" src="{root_doc}system/inc/js/jquery.fileupload-video.js"></script>');
        \Html::set('{scripts}', '<script type="text/javascript" src="{root_doc}system/inc/js/jquery.fileupload-validate.js"></script>');
        */
    }
}