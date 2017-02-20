<?php

if (!defined('IMAGE_CACHE_PATH')) {
    define('IMAGE_CACHE_PATH', PATH_MEDIA . 'cache/image/');
}

if (!defined('IMAGE_ORGINAL_PATH')) {
    define('IMAGE_ORGINAL_PATH', PATH_MEDIA . 'image/');
}

if (!defined('IMAGE_DEFAULT_THUMBNAIL_SIZE')) {
    define('IMAGE_DEFAULT_THUMBNAIL_SIZE', 100);
}

if (!defined('IMAGE_DEFAULT_SQUARE')) {
    define('IMAGE_DEFAULT_SQUARE', 1);
}

if (!defined('IMAGE_DEFAULT_THUMBNAIL_STRICT_W')) {
    define('IMAGE_DEFAULT_THUMBNAIL_STRICT_W', 125);
}

if (!defined('IMAGE_DEFAULT_THUMBNAIL_STRICT_H')) {
    define('IMAGE_DEFAULT_THUMBNAIL_STRICT_H', 300);
}

class Image {
    /**
     * @var string filename 
     */
    private $_file = false;
    
    /**
     *
     * @var string name used for title
     */
    public $_name = false;
    
    /**
     *
     * @var array all data for an image
     */
    private $_data = array();
    
    /**
     * @var int id of Image() 
     */
    public $_id = 0;
    
    /**
     * @var bool load error check
     */
    public $_error = false;
    
    /**
     * @var bool interal check
     */
    public $_status = true;
    
    /**
     * Try to load an image
     * @param int $id
     */
    public function __construct($id = false) {
        $this->check_folders();
        if (is_numeric($id)) {
            $try = $this->load($id);
            if($try){
                return true;
            }else{
                $this->_status = false;
                return false;
            }
        }else{
            $this->_status = false;
            
            return false;
        }
    }
    
    /**
     * Add new image to system
     * @param string $file
     * @param string $name
     */
    public function create($file, $name = false) {
        $orginal = IMAGE_ORGINAL_PATH . $file;
        $ext = pathinfo($orginal, PATHINFO_EXTENSION);
        
        $newname = $this->random_filename($ext);
        $new = LOCAL_PATH.IMAGE_ORGINAL_PATH . $newname;
        copy($orginal, $new);
        $sql = '
            INSERT INTO images (
                `file`,
                `orginal`,
                `name`
            ) VALUES (
                \'' . \DB::clean($newname) . '\',
                \'' . \DB::clean($orginal) . '\',
                \'' . \DB::clean(urlencode($name)) . '\'
            )';
        \DB::q($sql);
        return \DB::$_lastid;
    }
    
    /**
     * Load based on id or filename
     * @param mixed $image_id_or_file
     * @return boolean
     */
    public function load($image_id_or_file) {
        if (is_numeric($image_id_or_file)) {
            $sql = 'SELECT * FROM `images` WHERE `id` = \'' . \DB::clean($image_id_or_file) . '\' LIMIT 1';
        } else {
            $sql = 'SELECT * FROM `images` WHERE `file` = \'' . \DB::clean($image_id_or_file) . '\' LIMIT 1';
        }
        $list = \DB::q($sql);
        if (is_array($list)) {
            foreach ($list as $item) {
                foreach ($item as $k => $v) {
                    $this->_data[$k] = $v;
                }
                $this->_id = $item['id'];
                $this->_name = urldecode($item['name']);
                $this->_file = $item['file'];
                return true;
            }
        }
        $this->_error = 'Image not found';
        return false;
    }
    
    /**
     * Make sure all folders for cache are created
     */
    public function check_folders() {
		if (!is_dir(PATH_MEDIA)) {
            mkdir(PATH_MEDIA);
        }
	
        if (!is_dir(LOCAL_PATH.CACHE_PATH)) {
            mkdir(LOCAL_PATH.CACHE_PATH);
        }

        if (!is_dir(LOCAL_PATH.IMAGE_CACHE_PATH)) {
            mkdir(LOCAL_PATH.IMAGE_CACHE_PATH);
        }
        
        if (!is_dir(LOCAL_PATH.IMAGE_ORGINAL_PATH)) {
            mkdir(LOCAL_PATH.IMAGE_ORGINAL_PATH);
        }
    }
    
    /**
     * Returns name/title of image
     * @param str $name
     * @return type
     */
    public function name() {
        return $this->_name;
    }
    
    /**
     * Place Holder, not used
     */
    public function setname() {
        
    }
    
    /**
     * Place Holder, not used
     */
    public function delete() {
        
    }
    
    /**
     * Returns html tag for thumbnail
     * @param int $size
     * @param bool $square is square
     * @return html <img> tag
     */
    public function thumbnail($size = IMAGE_DEFAULT_THUMBNAIL_SIZE, $square = IMAGE_DEFAULT_SQUARE) {
        if(!$this->_status){
            return '<p>missing/blank image</p>';
        }
        
        $this->check_cache($this->_file, $size, $square);
        $path = PATH_BASE.IMAGE_CACHE_PATH . $square . '/' . $size . '/';
        $path = str_replace('//', '/', $path); //temp path fix
        if (!is_file($path)) {
            $this->create_cache_file($size, $square, $path, $this->_file);
        }
        return '<img src="' . $path . $this->_file . '" title="' . $this->_name . '" alt="' . $this->_name . '">';
    }
    
    public function thumbnail_url($size = IMAGE_DEFAULT_THUMBNAIL_SIZE, $square = IMAGE_DEFAULT_SQUARE) {
        if(!$this->_status){
            return '<p>missing/blank image</p>';
        }
        
        $this->check_cache($this->_file, $size, $square);
        $path = PATH_BASE.IMAGE_CACHE_PATH . $square . '/' . $size . '/';
        $path = str_replace('//', '/', $path); //temp path fix
        if (!is_file($path)) {
            $this->create_cache_file($size, $square, $path, $this->_file);
        }
        return  $path . $this->_file;
    }
    
    
    /*
    public function thumbnail_width($size = IMAGE_DEFAULT_THUMBNAIL_SIZE) {
        $this->check_cache_width($this->_file, $size);
        $path = PATH_BASE.IMAGE_CACHE_PATH . 'width/' . $size . '/';
        $path = str_replace('//', '/', $path); //temp path fix
        if (!is_file($path)) {
            $this->create_cache_file_width($size, $path, $this->_file);
        }
        return '<img src="' . $path . $this->_file . '" title="' . $this->_name . '" alt="' . $this->_name . '">';
    }
    */
    /**
     * Wraps thumbnail() to create a link
     * @param string $href path for link
     * @param int $size thumbnail height
     * @param bool $square thumbnail is square
     * @param string $class class atrib for <a> tag
     * @return html
     */
    public function thumbnail_link($href, $size = IMAGE_DEFAULT_THUMBNAIL_SIZE, $square = IMAGE_DEFAULT_SQUARE, $class = false) {
        if(!$this->_status){
            return '<p>missing/blank image</p>';
        }
        $this->check_cache($this->_file, $size, $square);
        return '<a id="'.$this->_id.'" class="'.$class.'" title="' . $this->_name . '" href="' . $href . '">' . $this->thumbnail($size, $square) . '</a>';
    }
    
    /**
     * Display thumbnail for new image size, like a thumbnail for a larger image
     * @param int $display_size height of image loaded
     * @param int $size height of thumbnail
     * @param type $square thumbnail is square
     * @param type $class class for <a> tag on thumbnail
     * @return type
     */
    public function thumbnail_display_link($display_size, $size = IMAGE_DEFAULT_THUMBNAIL_SIZE, $square = IMAGE_DEFAULT_SQUARE, $class = false) {
        if(!$this->_status){
            return '<p>missing/blank image</p>';
        }
        $this->check_cache($this->_file, $display_size, 0);
        $href = PATH_BASE.IMAGE_CACHE_PATH .'0'.'/' . $display_size . '/'.$this->_file;
        $href = str_replace('//', '/', $href); //temp path fix
        return '<a id="'.$this->_id.'" class="'.$class.'" title="' . $this->_name . '" href="' . $href . '">' . $this->thumbnail($size, $square) . '</a>';
    }
    
    /**
     * Returns html tag for thumbnail
     * @param int $size
     * @param bool $square is square
     * @return html <img> tag
     */
    public function thumbnail_strict($w = IMAGE_DEFAULT_THUMBNAIL_STRICT_W, $h = IMAGE_DEFAULT_THUMBNAIL_STRICT_H) {
        if(!$this->_status){
            return '<p>missing</p>';
        }
        $this->check_strict_cache($this->_file, $w, $h);
        
        $path = PATH_BASE.IMAGE_CACHE_PATH . 'strict/'.$w.'_'.$h. '/'.$this->_file;
        $path = str_replace('//', '/', $path); //temp path fix
        /*
        if (!is_file($path)) {
            $this->create_strict_cache_file($w, $h, $this->_file);
        }
         * */
        return '<img src="' . $path. '" title="' . $this->_name . '" alt="' . $this->_name . '">';
    }
    
    public function thumbnail_width($w = IMAGE_DEFAULT_THUMBNAIL_STRICT_W, $class = false) {
        if(!$this->_status){
            return '<p>missing</p>';
        }
        $this->check_width_cache($this->_file, $w);
        
        $path = PATH_BASE.IMAGE_CACHE_PATH . 'width/'.$w.'/'.$this->_file;
        $path = str_replace('//', '/', $path); //temp path fix
        /*
        if (!is_file($path)) {
            $this->create_strict_cache_file($w, $h, $this->_file);
        }
         * */
        return '<img class="'.$class.'" src="' . $path. '" title="' . $this->_name . '" alt="' . $this->_name . '">';
    }
    
    public function thumbnail_width_link($display_size, $size = IMAGE_DEFAULT_THUMBNAIL_SIZE, $class = false) {
        if(!$this->_status){
            return '<p>missing/blank image</p>';
        }
        $this->check_cache($this->_file, $display_size, 0);
        $href = PATH_BASE.IMAGE_CACHE_PATH .'0'.'/' . $display_size . '/'.$this->_file;
        $href = str_replace('//', '/', $href); //temp path fix
        return '<a id="'.$this->_id.'" class="'.$class.'" title="' . $this->_name . '" href="' . $href . '">' . $this->thumbnail_width($size) . '</a>';
    }
    
    
    
    /**
     * Check if image has cached resize created
     * @param string $file filename
     * @param int $size height of image
     * @param int $square image is square
     */
    private function check_cache($file, $size, $square) {
        $path1 = LOCAL_PATH.IMAGE_CACHE_PATH . $square . '/';
        $path2 = $path1 . $size . '/';
        if (!is_dir($path1)) {
            mkdir($path1);
        }

        if (!is_dir($path2)) {
            mkdir($path2);
        }


        if (!is_file($path2 . $file)) {
            $this->create_cache_file($size, $square, $path2, $file);
        }
    }
    
    /**
     * Check if image has cached resize created
     * @param string $file filename
     * @param int $size height of image
     * @param int $square image is square
     */
    private function check_cache_width($file, $size) {
        $path1 = LOCAL_PATH.IMAGE_CACHE_PATH . 'width/';
        $path2 = $path1 . $size . '/';
        if (!is_dir($path1)) {
            mkdir($path1);
        }

        if (!is_dir($path2)) {
            mkdir($path2);
        }


        if (!is_file($path2 . $file)) {
            $this->create_cache_file_width($size, $path2, $file);
        }
    }
    
    /**
     * Check if image has cached resize created
     * @param string $file filename
     * @param int $size height of image
     * @param int $square image is square
     */
    private function check_strict_cache($file, $w, $h) {
        $path1 = LOCAL_PATH.IMAGE_CACHE_PATH . 'strict/';
        $path2 = $path1  .$w.'_'.$h. '/';
        if (!is_dir($path1)) {
            $try = mkdir($path1);
            if(!$try){
                echo 'unable to create static path 1: '.$path1.':';
            }
        }

        if (!is_dir($path2)) {
            $try = mkdir($path2);
            if(!$try){
                echo 'unable to create static path 2: '.$path2.':';
            }
        }
        if($this->_status){
            if (!is_file($path2 . $file)) {
                $this->create_strict_cache_file($w, $h, $file);
            }
        }else{
            return false;
        }
    }
    
    /**
     * Check if image has cached resize created
     * @param string $file filename
     * @param int $size height of image
     * @param int $square image is square
     */
    private function check_width_cache($file, $w) {
        $path1 = LOCAL_PATH.IMAGE_CACHE_PATH . 'width/';
        $path2 = $path1  .$w. '/';
        if (!is_dir($path1)) {
            $try = mkdir($path1);
            if(!$try){
                echo 'unable to create static path 1: '.$path1.':';
            }
        }

        if (!is_dir($path2)) {
            $try = mkdir($path2);
            if(!$try){
                echo 'unable to create static path 2: '.$path2.':';
            }
        }
        if($this->_status){
            if (!is_file($path2 . $file)) {
                $this->create_width_cache_file($w, $file);
            }
        }else{
            return false;
        }
    }
    
    /**
     * Create a cache version of Image
     * @param int $size height of cache image
     * @param type $square new image is square
     * @param string $path to image (not used, legacy)
     * @param string $file filename
     */
    private function create_cache_file($size, $square, $path, $file) {
        $path = LOCAL_PATH.IMAGE_CACHE_PATH . $square . '/' . $size . '/';
        if(is_file(LOCAL_PATH.IMAGE_ORGINAL_PATH . $file)){
			copy(LOCAL_PATH.IMAGE_ORGINAL_PATH . $file, $path . $file);
            if ($square) {
                Image::resize_square($path . $file, $size);
            } else {
                Image::resize($path . $file, $size);
            }
        }
    }
    
    /**
     * Create a cache version of Image
     * @param int $size height of cache image
     * @param type $square new image is square
     * @param string $path to image (not used, legacy)
     * @param string $file filename
     */
    private function create_cache_file_width($size, $path, $file) {
        $path = LOCAL_PATH.IMAGE_CACHE_PATH . 'width/' . $size . '/';
        if(is_file(LOCAL_PATH.IMAGE_ORGINAL_PATH . $file)){
            copy(LOCAL_PATH.IMAGE_ORGINAL_PATH . $file, $path . $file);
            Image::resize($path . $file, $size, false);
        }
    }
    
    /**
     * Create a cache version of Image
     * @param int $size height of cache image
     * @param type $square new image is square
     * @param string $path to image (not used, legacy)
     * @param string $file filename
     */
    private function create_strict_cache_file($w, $h, $file) {
        $path1 = LOCAL_PATH.IMAGE_CACHE_PATH . 'strict/';
        $path2 = $path1  .$w.'_'.$h. '/';
        copy(LOCAL_PATH.IMAGE_ORGINAL_PATH . $file, $path2 . $file);
        Image::resize_strict($path2 . $file, $w, $h);
    }
    
    /**
     * Create a cache version of Image
     * @param int $size height of cache image
     * @param type $square new image is square
     * @param string $path to image (not used, legacy)
     * @param string $file filename
     */
    private function create_width_cache_file($w, $file) {
        $path1 = LOCAL_PATH.IMAGE_CACHE_PATH . 'width/';
        $path2 = $path1  .$w. '/';
        copy(LOCAL_PATH.IMAGE_ORGINAL_PATH . $file, $path2 . $file);
        Image::resize($path2 . $file, $w, false);
    }
    
    /**
     * Create a url safe filename
     * @param string $ext file ext
     * @param int $len len of filename
     * @return string
     */
    public static function random_filename($ext, $len = 10) {
        $result = "";
        $charPool = '0123456789abcdefghijklmnopqrstuvwxyz';
        for ($p = 0; $p < $len; $p++) {
            $result .= $charPool[mt_rand(0, strlen($charPool) - 1)];
        }
        $result = $result . '.' . $ext;
        $sql = 'SELECT * FROM `images` WHERE `file` = \'' . \DB::clean($result) . '\' LIMIT 1';
        $list = \DB::q($sql);
        if (is_array($list)) {
            foreach ($list as $item) {
                return $this->random_filename($ext);
            }
        }
        return $result;
    }
    
    /**
     * Resize an image
     * @param string $imagepath path to image
     * @param int $size resize
     * @param bool $resize_by_height force resize by height, recomended
     * @return boolean
     */
    protected static function resize($imagepath, $size, $resize_by_height = true) {

        if (!is_file($imagepath)) {
            return false;
        }

        $type = strtolower(pathinfo($imagepath, PATHINFO_EXTENSION));

        $limit_height = $size;
        $limit_width = $size;

        $q = 90;
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
    
    

    /**
     * Resize an image to square
     * @param <type> $imagepath path to src
     * @param <type> $size max size
     * @param <type> $resize_by_height resize by height
     * @return <bool> false if the image is not found
     */
    protected static function resize_square($imagepath, $size) {
        if (!is_file($imagepath)) {
            return false;
        }

        /*
          $limit_height = $size;
          $limit_width = $size;
         */
        $q = 90;

        $type = strtolower(pathinfo($imagepath, PATHINFO_EXTENSION));
        switch ($type) {
            case 'jpg':
                if($_SERVER['REMOTE_ADDR'] == '50.243.100.169'){
                    $temp_image = imagecreatefromjpeg($imagepath);
                }else{
                    $temp_image = @imagecreatefromjpeg($imagepath);
                }
                break;
            
            case 'jpeg':
                $temp_image = @imagecreatefromjpeg($imagepath);
                break;
            
            case 'bmp':
                $temp_image = self::imagecreatefrombmp($imagepath);
                break;

            case 'png':
                $temp_image = @imagecreatefrompng($imagepath);
                break;

            case 'gif':
                $temp_image = @imagecreatefromgif($imagepath);
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
        @imagecopyresampled($new_image, $temp_image, 0, 0, $x_offset, $y_offset, $size, $size, $square_size, $square_size);

        switch ($type) {
            case 'jpg':
                @imagejpeg($new_image, $imagepath, $q);
                break;
            
            case 'jpeg':
                @imagejpeg($new_image, $imagepath, $q);
                break;

            case 'bmp':
                @imagewbmp($new_image, $imagepath);
                break;

            case 'png':
                @imagepng($new_image, $imagepath, 0);
                break;

            case 'gif':
                @imagegif($new_image, $imagepath);
                break;
        }

        imagedestroy($new_image);
        return true;
    }
    
    
    /**
     * Resize an image to square
     * @param <type> $imagepath path to src
     * @param <type> $size max size
     * @param <type> $resize_by_height resize by height
     * @return <bool> false if the image is not found
     */
    protected static function resize_strict($imagepath, $w, $h) {
        if (!is_file($imagepath)) {
            return false;
        }
        $path = $imagepath;
        $image = new \Imagick($path);
        $image->cropThumbnailImage($w,$h);
        $image->setImagePage(0, 0, 0, 0);
        $image->writeImage( $path );
        return true;
    }
    
    private static function thumbnail_box($img, $box_w, $box_h) {
        //create the image, of the required size
        $new = imagecreatetruecolor($box_w, $box_h);
        if($new === false) {
            //creation failed -- probably not enough memory
            return null;
        }


        //Fill the image with a light grey color
        //(this will be visible in the padding around the image,
        //if the aspect ratios of the image and the thumbnail do not match)
        //Replace this with any color you want, or comment it out for black.
        //I used grey for testing =)
        $fill = imagecolorallocate($new, 200, 200, 205);
        imagefill($new, 0, 0, $fill);

        //compute resize ratio
        $hratio = $box_h / imagesy($img);
        $wratio = $box_w / imagesx($img);
        $ratio = min($hratio, $wratio);

        //if the source is smaller than the thumbnail size, 
        //don't resize -- add a margin instead
        //(that is, dont magnify images)
        /*
        if($ratio > 1.0)
            $ratio = 1.0;
            */
        //compute sizes
        $sy = floor(imagesy($img) * $ratio);
        $sx = floor(imagesx($img) * $ratio);

        //compute margins
        //Using these margins centers the image in the thumbnail.
        //If you always want the image to the top left, 
        //set both of these to 0
        $m_y = floor(($box_h - $sy) / 2);
        $m_x = floor(($box_w - $sx) / 2);

        //Copy the image data, and resample
        //
        //If you want a fast and ugly thumbnail,
        //replace imagecopyresampled with imagecopyresized
        if(!imagecopyresampled($new, $img,
            $m_x, $m_y, //dest x, y (margins)
            0, 0, //src x, y (0,0 means top left)
            $sx, $sy,//dest w, h (resample to this size (computed above)
            imagesx($img), imagesy($img)) //src w, h (the full size of the original)
        ) {
            //copy failed
            imagedestroy($new);
            return null;
        }
        //copy successful
        return $new;
    }
    
    
    
    
    public static function imagecreatefrombmp($filename)
    {
//Ouverture du fichier en mode binaire
   if (! $f1 = fopen($filename,"rb")) return FALSE;

 //1 : Chargement des ent�tes FICHIER
   $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
   if ($FILE['file_type'] != 19778) return FALSE;

 //2 : Chargement des ent�tes BMP
   $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
                 '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
                 '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
   $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
   if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
   $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
   $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
   $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
   $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
   $BMP['decal'] = 4-(4*$BMP['decal']);
   if ($BMP['decal'] == 4) $BMP['decal'] = 0;

 //3 : Chargement des couleurs de la palette
   $PALETTE = array();
   if ($BMP['colors'] < 16777216)
   {
    $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
   }

 //4 : Cr�ation de l'image
   $IMG = fread($f1,$BMP['size_bitmap']);
   $VIDE = chr(0);

   $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
   $P = 0;
   $Y = $BMP['height']-1;
   while ($Y >= 0)
   {
    $X=0;
    while ($X < $BMP['width'])
    {
     if ($BMP['bits_per_pixel'] == 24)
        $COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
     elseif ($BMP['bits_per_pixel'] == 16)
     { 
        $COLOR = unpack("n",substr($IMG,$P,2));
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 8)
     { 
        $COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 4)
     {
        $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
        if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     elseif ($BMP['bits_per_pixel'] == 1)
     {
        $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
        if     (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
        elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
        elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
        elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
        elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
        elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
        elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
        elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
        $COLOR[1] = $PALETTE[$COLOR[1]+1];
     }
     else
        return FALSE;
     imagesetpixel($res,$X,$Y,$COLOR[1]);
     $X++;
     $P += $BMP['bytes_per_pixel'];
    }
    $Y--;
    $P+=$BMP['decal'];
   }

 //Fermeture du fichier
   fclose($f1);

 return $res;
return $image;
    } 

}