<?php
/**
 * @author Ryan Mills <ryan@ryanmills.net>
 * 
 * Dynamic Image
 * 
 * Replaces the core Image() class for dealing with images. This simple baseclass
 * can be extended for a variety of needs.
 * 
 */


if(!defined('DIMAGE_LOCAL_PATH')){
    /**
    * Path to media folder, relative to root, never include starting /
    */
    define('DIMAGE_LOCAL_PATH', false);
}

if(!defined('DIMAGE_TABLE')){
    /**
     * DB Field used for DImage()
     */
    define('DIMAGE_TABLE', 'dimage');
}

if(!defined('DIMAGE_CATAGORY_TABLE')){
    /**
     * DB Field used for DImage() catagory
     */
    define('DIMAGE_CATAGORY_TABLE', 'dimage_catagory');
}

if(!defined('DIMAGE_MAX_IMAGES_PER_FOLDER')){
    /**
     * About of orginals stored per folder
     */
    define('DIMAGE_MAX_IMAGES_PER_FOLDER', 5);
}

if(!defined('DIMAGE_JPEG_COMPRESSION')){
    /**
     * Jpeg compression for all resizes
     */
    define('DIMAGE_JPEG_COMPRESSION', 80);
}

class DImage{
    
    /**
     * Image ID 
     * @var int
     */
    public $id = 0;
    
    /**
     * Folder id 
     * @var int
     */
    public $folder;
    
    /**
     * Filename
     * @var string
     */
    public $filename = false;
    
    /**
     * Raw binary image used for init
     * @var binary
     */
    
    public $raw_image_data = false;
    /**
     * Load Status Codes
     * 4:    load() called on id 0
     * 404:  id not found in db or deleted
     * 200:  db info loaded
     * @var int 
     */
    public $status = 0;
    
    /**
     * Title of image
     * @var string 
     */
    public $title;
    
    /**
     * Desc of image
     * @var string 
     */
    public $desc;
    
    /**
     * Date added to db
     * @var type 
     */
    public $upload_date;
    
    /**
     * Image Catagory
     * @var int 
     */
    public $catagory;
    
    /**
     * Image Sub Catagory
     * @var int 
     */
    public $sub_cat1;
    
    /**
     * Image Sub Catagory
     * @var int 
     */
    public $sub_cat2;
    
    /**
     * Image Sub Catagory
     * @var int 
     */
    public $sub_cat3;
    
    /**
     * User id
     * @var \User() 
     */
    public $owner;
    
    /**
     * Display status
     * 
     * live = image ready
     * deleted = soft delete
     * 
     * @var type 
     */
    public $display;
    
    /**
     * Stack of errors
     * @var array 
     */
    public $error = array();
    
    /*
     * Status Codes
     * 4:    load() called on id 0
     * 404:  id not found in db
     * 200:  db info loaded
     * 
     */
    
    public $admin_mode = false;
    
    /**
     * Dynamic Image
     * @param int $id unqine id to load, 0 for new image
     * @param bool $delay_load
     */
    function __construct($id=0, $delay_load = false, $force_load = false) {
        $this->id = $id;
        if($id !== 0 && !$delay_load){
            return $this->load($id, $force_load);
        }
        return false;
    }
    
    /**
     * Loads image data from database
     * @return boolean load status, on false check $this->error array
     */
    private function load($id = 0, $force = false){
        if(isset($id)){
            if($id !== 0){
                $this->id = $id;
            }
        }
        if($this->id == 0){
            $this->error('$this->load() called on id: 0');
            $this->status = 4;
            return false;
        }
        
        $sql = 'SELECT * FROM `'.DIMAGE_TABLE.'` WHERE `id` = '.\DB::clean($this->id).' LIMIT 1';
        $image_date = \DB::q($sql);
        if(isset($image_date[0])){
            if(isset($image_date[0])){
                
                if($image_date[0]['display'] != 'live' && !$force){
                    $this->status = 404;
                    return false;
                }
                
                $this->id = $image_date[0]['id'];
                $this->filename = $image_date[0]['filename'];
                $this->folder = $image_date[0]['folder'];
                $this->orginal = $image_date[0]['orginal'];

                $this->owner = $image_date[0]['owner'];
                $this->catagory = $image_date[0]['catagory'];
                $this->sub_cat1 = $image_date[0]['sub_cat1'];
                $this->sub_cat2 = $image_date[0]['sub_cat2'];
                $this->sub_cat3 = $image_date[0]['sub_cat3'];
                $this->title = $image_date[0]['title'];
                $this->desc = $image_date[0]['desc'];
                $this->upload_date = $image_date[0]['upload_date'];
                $this->display = $image_date[0]['display'];
                
                $this->status = 200;
                return true;
            }else{
                $this->status = 404;
                return false;
            }
        }else{
           $this->status = 404;
           return false;
        }
    }
    
    /**
     * Add new image to libary
     * 
     * @param binary $raw_image_data raw image data
     * @param string $file_type file type, example: jpg
     * @param string $filename 
     */
    public function create($raw_image_data = false, $file_type = 'jpg', $orginal_filename = false){
        if(!$raw_image_data){
            die('DImage::create() empty $raw_image_data');
        }
        
        $filename = DImage::create_file_name($file_type);
        $folder = DImage::create_folder_name();
        $path = DIMAGE_LOCAL_PATH.'/'.$folder.'/'.$filename;
        $bytes = file_put_contents($path, $raw_image_data);
        if(is_file($path) && $bytes){
            $this->create_db_entry($filename, $folder, $orginal_filename);
            $this->auto_rotate($path);
        }else{
            exit('Unable to create new image file: Dimage::create()<br>file: '.$filename.'<br>folder: '.$folder.'<br>');
        }

    }
    
    function auto_rotate($path) { 
        $image = new \Imagick($_SERVER['DOCUMENT_ROOT'].$path);
        $orientation = $image->getImageOrientation(); 
        $changed = false;
        switch($orientation) { 
            case imagick::ORIENTATION_BOTTOMRIGHT: 
                $changed = true;
                $image->rotateimage("#000", 180); // rotate 180 degrees 
            break; 

            case imagick::ORIENTATION_RIGHTTOP: 
                $changed = true;
                $image->rotateimage("#000", 90); // rotate 90 degrees CW 
            break; 

            case imagick::ORIENTATION_LEFTBOTTOM: 
                $changed = true;
                $image->rotateimage("#000", -90); // rotate 90 degrees CCW 
            break; 
        } 
        
        if($changed){
            $image->setImageOrientation(imagick::ORIENTATION_TOPLEFT); 
            $image->writeImage($_SERVER['DOCUMENT_ROOT'].$path);
        }
    } 
    
    /**
     * Removes all traces expect cache
     */
    public function perm_delete(){
        $orginal_path = DIMAGE_LOCAL_PATH.'/'.$this->folder.'/'.$this->filename;
        $remove_orginal = unlink($orginal_path);
        $sql = 'DELETE FROM `'.DIMAGE_TABLE.'` WHERE `id` = \'' . \DB::clean($this->id) . '\' LIMIT 1';
        \DB::q($sql);
    }
    
    /**
     * Soft delete, only disables image
     */
    public function soft_delete(){
        $sql = 'UPDATE `'.DIMAGE_TABLE.'` SET `display` = \'deleted\' WHERE `id` = \'' . \DB::clean($this->id) . '\' LIMIT 1';
        \DB::q($sql);
    }
    
    /**
     * Adds DImage data to database and gets ID number
     * @param string $filename for image
     * @param string $folder location
     * @param string $orginal_filename not required
     */
    private function create_db_entry($filename, $folder, $orginal_filename){
        if($filename == ''){
            exit('DImage::create_db_entry(); missing $filename');
        }
        if($folder == ''){
            if($folder !== 0){
                exit('DImage::create_db_entry(); missing $folder "'.$folder.'"');
            }
        }
        
        $sql = '
            INSERT INTO `'.DIMAGE_TABLE.'` (
                `folder`,
                `filename`,
                `orginal`,
                `owner`,
                `upload_date`
            ) VALUES (
                \'' . \DB::clean($folder) . '\',
                \'' . \DB::clean($filename) . '\',
                \'' . \DB::clean($orginal_filename) . '\',
                \'' . \DB::clean($_SESSION['user']) . '\',
                \'' . \DB::clean(time()) . '\'
            )';
        
        $try = \DB::q($sql);
        if($try){
            $this->load(\DB::$_lastid);
        }
        
    }
    
    /**
     * 
     * @param int $h height of image 0 for orginal
     * @param int $w width of image 0 for orginal
     * @param mixed $type orginal, resize, resize_square, strict
     * @param bool $by_height only valid with type "resize"
     * @param string $class any css classes
     * @param string $extra any extra attributes
     * @param bool $pathonly return just the path
     * @return html <img> tag
     */
    public function image($h = 0, $w = 0, $type = 'orginal', $by_height = true, $class = false, $extra = '', $pathonly = false){
        if($this->status !== 200){
            return '<span>Image not found/deleted</span>';
        }
        $orginal_path = DIMAGE_LOCAL_PATH.'/'.$this->folder.'/'.$this->filename;
        
        if(!is_file($orginal_path)){
            return '<span>Image not found/deleted</span>';
        }
        
        /* use this to fix to fix global rotate issues*/
        //$this->auto_rotate($orginal_path);
        
        switch($type){
            case'orginal':
                $path = $orginal_path;
                $size = getimagesize($path);
                if($pathonly){
                    return '/'.$path;
                }else{
                    return '<img src="/'.$path.'" class="'.$class.'" '.$extra.' title="'.$this->title.'" height="'.$size[1].'" width="'.$size[0].'">';
                }
                break;
            
            case 'resize':
                if(!is_dir(DIMAGE_LOCAL_PATH.'/'.$this->folder)){
                    
                }
                
                
                if($by_height){
                    $folder = DIMAGE_LOCAL_PATH.'/'.$this->folder.'/resize_by_h_'.$h.'_'.$w.'/';
                }else{
                    $folder = DIMAGE_LOCAL_PATH.'/'.$this->folder.'/resize_'.$h.'_'.$w.'/';
                }
                $path = $folder.$this->filename;
                if(!is_file($path)){
                    if(!is_dir($folder)){
                        $try = mkdir($folder);
                        if(!$try){
                            \SiteDebug::log( 'unable to create: '.$folder);
                        }
                    }
                    copy($orginal_path, $path);
                    if($by_height){
                        DImage::resize($path, $h);
                    }else{
                        DImage::resize($path, $w, false);
                    }
                }
                
                if(!is_file($path)){
                    return '<span>Image not built</span>';
                }
                
                $size = getimagesize($path);
                if($pathonly){
                    return '/'.$path;
                }else{
                    return '<img src="/'.$path.'" class="'.$class.'" '.$extra.' title="'.$this->title.'" height="'.$size[1].'" width="'.$size[0].'">';
                }
                break;
                
            case 'resize_square':
                $folder = DIMAGE_LOCAL_PATH.'/'.$this->folder.'/resize_square_'.$h.'_'.$w.'/';
                $path = $folder.$this->filename;
                if(!is_file($path)){
                    if(!is_dir($folder)){
                        $try = mkdir($folder);
                        if(!$try){
                            \SiteDebug::log( 'unable to create: '.$folder);
                        }
                    }
                    copy($orginal_path, $path);
                    if($by_height){
                        DImage::resize($path, $h);
                    }else{
                        DImage::resize($path, $w, false);
                    }
                }
                
                if(!is_file($path)){
                    return '<span>Image not built</span>';
                }
                
                $size = getimagesize($path);
                if($pathonly){
                    return '/'.$path;
                }else{
                    return '<img src="/'.$path.'" class="'.$class.'" '.$extra.' title="'.$this->title.'" height="'.$size[1].'" width="'.$size[0].'">';
                }
                break;
            
            case 'strict':
                $folder = DIMAGE_LOCAL_PATH.'/'.$this->folder.'/strict_'.$h.'_'.$w.'/';
                $path = $folder.$this->filename;
                if(!is_file($path)){
                    if(!is_dir($folder)){
                        $try = mkdir($folder);
                        if(!$try){
                            \SiteDebug::log( 'unable to create: '.$folder);
                        }
                    }
                    copy($orginal_path, $path);
                    DImage::resize_strict($path, $h, $w);
                }
                
                if(!is_file($path)){
                    return '<span>Image not built</span>';
                }
                
                $size = getimagesize($path);
                if($pathonly){
                    return '/'.$path;
                }else{
                    return '<img src="/'.$path.'" class="'.$class.'" '.$extra.' title="'.$this->title.'" height="'.$size[1].'" width="'.$size[0].'">';
                }
                break;
        }
    }
    
    /**
     * Resize an image to strict size regardelss of ratio
     * @param <type> $imagepath path to src
     * @param <type> $size max size
     * @param <type> $resize_by_height resize by height
     * @return <bool> false if the image is not found
     */
    protected static function resize_strict($path, $h, $w) {
        if (!is_file($path)) {
            return false;
        }
        
        $image = new \Imagick($_SERVER['DOCUMENT_ROOT'].$path);
        $image->setImageCompressionQuality(70); 
        $image->cropThumbnailImage($w,$h);
        $image->setImagePage(0, 0, 0, 0);
        $image->writeImage( $_SERVER['DOCUMENT_ROOT'].$path );
        
        return true;
    }
    
    /**
     * Resize an image to square
     * @param <type> $path path to src
     * @param <type> $size max size
     * @return <bool> false if the image is not found
     */
    protected static function resize_square($path, $size) {
        if (!is_file($path)) {
            return false;
        }
        
        $image = new \Imagick($_SERVER['DOCUMENT_ROOT'].$path);
        $image->cropThumbnailImage($size,$size);
        $image->setImagePage(0, 0, 0, 0);
        $image->writeImage( $_SERVER['DOCUMENT_ROOT'].$path );
        return true;
    }
    /* OLD VERSION
    protected static function resize_square($imagepath, $size) {
        if (!is_file($imagepath)) {
            return false;
        }

        
        $q = DIMAGE_JPEG_COMPRESSION;

        $type = strtolower(pathinfo($imagepath, PATHINFO_EXTENSION));
        switch ($type) {
            case 'jpg':
                $temp_image = imagecreatefromjpeg($imagepath);
                break;
            
            case 'jpeg':
                $temp_image = imagecreatefromjpeg($imagepath);
                break;
            
            case 'bmp':
                $temp_image = self::imagecreatefrombmp($imagepath);
                break;

            case 'png':
                $temp_image = imagecreatefrompng($imagepath);
                break;

            case 'gif':
                $temp_image = imagecreatefromgif($imagepath);
                break;
        }

        $image_data = getimagesize($imagepath);

        if ($image_data[0] > $image_data[1]) {
            // For landscape images
            $x_offset = ($image_data[0] - $image_data[1]) / 2;
            $y_offset = 0;
            $square_size = $image_data[0] - ($x_offset * 2);
        } else {
            // For portrait and square images
            $x_offset = 0;
            $y_offset = ($image_data[1] - $image_data[0]) / 2;
            $square_size = $image_data[1] - ($y_offset * 2);
        }

        $new_image = imagecreatetruecolor($size, $size);
        imagecopyresampled($new_image, $temp_image, 0, 0, $x_offset, $y_offset, $size, $size, $square_size, $square_size);

        switch ($type) {
            case 'jpg':
                imagejpeg($new_image, $imagepath, $q);
                break;
            
            case 'jpeg':
                imagejpeg($new_image, $imagepath, $q);
                break;

            case 'bmp':
                imagewbmp($new_image, $imagepath);
                break;

            case 'png':
                imagepng($new_image, $imagepath, 0);
                break;

            case 'gif':
                imagegif($new_image, $imagepath);
                break;
        }

        imagedestroy($new_image);
        return true;
    }
    */
    
    /**
     * Resize an image
     * @param string $imagepath path to image
     * @param int $size resize
     * @param bool $resize_by_height force resize by height, recomended
     * @return boolean
     */
    protected static function resize($path, $size, $resize_by_height = true) {
        if (!is_file($path)) {
            return false;
        }
        
        $image = new \Imagick($_SERVER['DOCUMENT_ROOT'].$path);
        $d = $image->getImageGeometry();  
        if($resize_by_height){
            $w = $d['width']; 
            $h = $size; 
        }else{
            $w = $size; 
            $h = $d['height']; 
        }
        $image->setImageCompression(Imagick::COMPRESSION_JPEG); 
        $image->setImageCompressionQuality(100); 
        $image->scaleImage ($w,$h, true);
        $image->setImagePage(0, 0, 0, 0);
        $image->writeImage( $_SERVER['DOCUMENT_ROOT'].$path );
        return true;
    }
    
    
    /* OLD VERSION
    protected static function resize($imagepath, $size, $resize_by_height = true) {
        //$size++; //-1 bug

        if (!is_file($imagepath)) {
            return false;
        }

        $type = strtolower(pathinfo($imagepath, PATHINFO_EXTENSION));

        $limit_height = $size;
        $limit_width = $size;

        $q = DIMAGE_JPEG_COMPRESSION;
        switch ($type) {
            case 'jpg':
                $temp_image = imagecreatefromjpeg($imagepath);
                break;
            
            case 'jpeg':
                $temp_image = imagecreatefromjpeg($imagepath);
                break;

            case 'png':
                $temp_image = imagecreatefrompng($imagepath);
                break;
            
            case 'bmp':
                $temp_image = self::imagecreatefrombmp($imagepath);
                break;

            case 'gif':
                $temp_image = imagecreatefromgif($imagepath);
                break;
        }


        $orginal_width = imagesx($temp_image);
        $orginal_height = imagesy($temp_image);


        if ($resize_by_height) {
            if ($orginal_height < $size) {
                return true;
            }

            $new_width = floor($orginal_width * ($limit_height / $orginal_height));
            $scale = $new_width / $orginal_width;
            $new_height = ceil($orginal_height * $scale);
        } else {
            if ($orginal_width < $size) {
                return true;
            }

            $new_height = floor($orginal_height * ($limit_width / $orginal_width));
            $scale = $new_height / $orginal_height;
            $new_width = ceil($orginal_width * $scale);
        }
        $new_image = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($new_image, $temp_image, 0, 0, 0, 0, $new_width, $new_height, $orginal_width, $orginal_height);

        switch ($type) {
            case 'jpg':
                imagejpeg($new_image, $imagepath, $q);
                break;
            
            case 'jpeg':
                imagejpeg($new_image, $imagepath, $q);
                break;

            case 'png':
                imagepng($new_image, $imagepath, 9);
                break;
            
            case 'bmp':
                $temp_image = imagewbmp($imagepath);
                break;

            case 'gif':
                imagegif($new_image, $imagepath, $q);
                break;
        }

        imagedestroy($new_image);
        return true;
    }
    */
    /**
     * Update current DImage in the database
     */
    public function update(){
        $sql = 'UPDATE `'.DIMAGE_TABLE.'` SET 
            `folder` = \'' . \DB::clean($this->folder) . '\',
            `filename` = \'' . \DB::clean($this->filename) . '\',
            `orginal` = \'' . \DB::clean($this->orginal) . '\',
            `owner` = \'' . \DB::clean($this->owner) . '\',
            `catagory` = \'' . \DB::clean($this->catagory) . '\',
            `sub_cat1` = \'' . \DB::clean($this->sub_cat1) . '\',
            `sub_cat2` = \'' . \DB::clean($this->sub_cat2) . '\',
            `sub_cat3` = \'' . \DB::clean($this->sub_cat3) . '\',
            `title` = \'' . \DB::clean($this->title) . '\',
            `desc` = \'' . \DB::clean($this->desc) . '\'
		
	WHERE `id` = \'' . \DB::clean($this->id) . '\' LIMIT 1';
        \DB::q($sql);
    }
    
    /**
     * Return catagory name via ID
     * @param string $field
     * @return mixed string on success, false on fail
     */
    public function catagory_name($field = 'title_pretty'){
        $sql = 'SELECT * FROM `'.DIMAGE_CATAGORY_TABLE.'` WHERE `id` = \''.\DB::clean($this->catagory).'\' LIMIT 1';
        $cat = \DB::q($sql);
        if(is_array($cat)){
            return $cat[0][$field];
        }else{
            return false;
        }
    }
    
    /**
     * Add message to error stack
     * @param string $message msg to add
     */
    private function error($message){
        $this->error[] = $message;
    }
    
    /**
     * 
     * @param string $file_type type of new file
     * @return string filename
     */
    public static function create_file_name($file_type, $len = 10){
        $result = "";
        $charPool = '0123456789abcdefghijklmnopqrstuvwxyz';
        for ($p = 0; $p < $len; $p++) {
            $result .= $charPool[mt_rand(0, strlen($charPool) - 1)];
        }
        $result = $result . '.' . $file_type;
        $sql = 'SELECT * FROM `'.DIMAGE_TABLE.'` WHERE `filename` = \'' . \DB::clean($result) . '\' LIMIT 1';
        $list = \DB::q($sql);
        if (is_array($list)) {
            foreach ($list as $item) {
                return DImage::create_file_name();
            }
        }
        return $result;
    }
    
    /**
     * Used to create folders based on current file count.
     * @return string folder name
     */
    public static function create_folder_name(){
        $folders = array();
        $folder = dir(DIMAGE_LOCAL_PATH);
        while (false !== ($entry = $folder->read())) {
            if($entry != '..' && $entry != '.'){
                if(is_dir(DIMAGE_LOCAL_PATH.'/'.$entry)){
                    $folders[$entry] = DImage::count_files(DIMAGE_LOCAL_PATH.'/'.$entry);
                }
            }
        }
        $folder->close();
        /* first image check, init new folder */
        if(!count($folders)){
            $path = DIMAGE_LOCAL_PATH.'/0/';
            mkdir($path);
            if(is_dir($path)){
                return 0;
            }else{
                exit('DIMAGE: Unable to create 0 folder for first image Dimage::create_folder_name()');
            }
        }
        
        sort($folders);
        foreach($folders as $k=>$v){
            if($v < DIMAGE_MAX_IMAGES_PER_FOLDER){
                return $k;
            }
        }
        
        /* folder full, create new folder */
        $new_folder = (count($folders));
        $path = DIMAGE_LOCAL_PATH.'/'.$new_folder.'/';
        mkdir($path);
        if(is_dir($path)){
            return $new_folder;
        }else{
            exit('DIMAGE: Unable to create new folder '.$new_folder.' for nth image Dimage::create_folder_name()');
        }
    }
    
    /**
     * Used to check how many files are in a folder
     * 
     * @param string $folder_path path to folder to count
     * @return int total files
     */
    public static function count_files($folder_path){
        $count = new \FilesystemIterator($folder_path, \FilesystemIterator::SKIP_DOTS);
        return \iterator_count($count);
    }
    
}