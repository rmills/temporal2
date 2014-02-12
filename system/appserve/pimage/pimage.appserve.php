<?php
namespace Appserve;
class Pimage extends Appserve{
    public static function __registar_callback() {
        if(\CMS::allowed()){
            Appserve::register('Pimage');
        }
    }
    
    public static function call(){
        $ext = pathinfo($_FILES['qqfile']['name'], PATHINFO_EXTENSION);
        
        $name = \Image::random_filename($ext);
        $fullname = IMAGE_ORGINAL_PATH.$name;
        
        move_uploaded_file($_FILES['qqfile']['tmp_name'], $fullname);
        $image = new \Image();
        $id = $image->create($name);
        $newimage = new \Image($id);
        $html = $newimage->thumbnail( constant('PIMAGE_'.$_POST['zone'].'_H'), constant('PIMAGE_'.$_POST['zone'].'_SQUARE') );
        self::set($_POST['zone'], $id);
        $data = array('success'=>true, 'html'=>$html);
        return $data;
    }
    
    private static function set($zone, $image_id){
        $sql = 'SELECT * FROM `pimage` WHERE `zone` = '.\DB::clean($zone).' LIMIT 1';
        $found = false;
        $list = \DB::q($sql);
        if (is_array($list)) {
            foreach ($list as $v) {
                if($v['zone'] == $zone){
                    $found = true;
                }
            }
        }
        if($found){
            $sql = '
                UPDATE `pimage` SET 
                        `image` = \'' . \DB::clean($image_id) . '\'
                WHERE 
                        `zone` = \'' . \DB::clean($zone) . '\' 
                LIMIT 1
            ';
            \DB::q($sql);
        }else{
           $sql = '
                INSERT INTO pimage (
                    `zone`,
                    `image`
                ) VALUES (
                    \'' . \DB::clean($zone) . '\',
                    \'' . \DB::clean($image_id) . '\'
                )';
        \DB::q($sql); 
        }
    }
}
