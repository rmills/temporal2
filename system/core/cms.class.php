<?php

/**
 * @author Ryan Mills <ryan@ryanmills.net> (Primary)
 * 
 * Core CMS class
 */
class CMS {
    
    /**
     * Stores all dyn config vars
     * @var array
     */
    static public $_config;
    
    /**
     * URI Vars
     * 
     * Example:
     * /avar/avar2
     * [0] avar
     * [1] avar
     * @var array
     */
    static public $_vars;
    
    /**
     * Sets the page, all pages must set this
     * zpage etc
     * @var string
     */
    static public $_page_type = false;
    
    /**
     * Sets the page content_type
     * only html, json supported by default
     * @var string 
     */
    static public $_content_type = false;
    
    /**
     * Used to store a stack of currently loaded modules
     * This should never be changed outside of the CMS class.
     * @var array 
     */
    static public $__modules = array();
    
    /**
     * Used to store a stack of currently loaded pages
     * This should never be changed outside of the CMS class.
     * @var array 
     */
    static public $__pages = array();
    
    /**
     * Used to store a stack of currently loaded user mods
     * This should never be changed outside of the CMS class.
     * @var array 
     */
    static public $_usermod = array();
    
    /**
     * Used to store a stack of currently loaded appserve pages
     * This should never be changed outside of the CMS class.
     * @var array 
     */
    static public $__appserve = array();
    
    /**
     * User Object. Loads the User() object for the loaded users. Will
     * default to the guest user when a user is not logged in.
     * @var \User() 
     */
    static public $_user = false;
    
    /**
     * For CacheDB()
     * 
     * Set in your page mod if a pages content should be cached. Use this carefully.
     * @var bool 
     */
    static public $_cacheable = false;
    
    /**
     * Set block cache type. If false the whole page will be cached and display
     * without the engine parsing it. With block enabled the page mod must load 
     * via \CacheDB($URI) then set it. This should be used with elements of the
     * page must loaded dynmaticly like anything related to user or community 
     * login type.
     * @var bool 
     */
    static public $_cacheblock = false;
    
    /**
     * Event Log, used internaly only
     * @var array 
     */
    static private $__log = array();
    
    /**
     * Do not use. This has replace in favor of BUILD_TIME
     * @var int 
     */
    static private $__time;
    
    /**
     * DO NOT MODIFY, this stores function calls and should never be modified
     * unless you really understand the system.
     * @var array 
     */
    static private $__callstack = array();

    /**
     * Init CMS system
     * @param array $config
     */
    public static function init($config = array()) {
        self::$_config = $config;
        self::init_vars();
        self::init_content_types();
        self::init_pages();
        self::init_modules();
        self::init_usermod();
        self::init_appserve();
        self::init_user();
        self::init_addon_classes();
        self::init_modules_callbacks();
        self::init_page_callbacks();
        self::init_appserve_callbacks();
        if(self::$_vars[0] == 'appserve'){
            self::init_active_appserve();
        }else{
            self::init_active_page();
        }
    }
    
    /**
     * Init the URI vars into the \CMS::$_vars array
     */
    private static function init_vars() {
        foreach (self::$_config['vars'] as $v) {
            self::$_vars[] = false;
        }
        foreach (self::$_config['vars'] as $key => $v) {
            if (isset($_GET[$v])) {
                self::$_vars[$key] = strtolower($_GET[$v]);
            } elseif (isset($_POST[$v])) {
                self::$_vars[$key] = strtolower($_POST[$v]);
            }
        }
    }
    
    public static function init_addon_classes(){
        if (is_dir(PATH_CLASSES_ROOT_ADDON)) {
            self::log('CMS', 'CMS::init_addon_classes() searching: ' . PATH_CLASSES_ROOT_ADDON);
            $folder = scandir(PATH_CLASSES_ROOT_ADDON);
            foreach ($folder as $v) {
                if ($v != '.' && $v != '..') {
                    if(is_file(PATH_CLASSES_ROOT_ADDON.$v)){
                        include_once PATH_CLASSES_ROOT_ADDON.$v;
                    }
                }
            }
        }
    }

    /**
     * Used to load files and add them to the callstack
     */
    public static function init_modules() {
        self::log('CMS', 'CMS::init_modules() searching: ' . PATH_MODULE_ROOT);
        self::$__modules = array();

        $folders = array();
        $modules = scandir(PATH_MODULE_ROOT);
        foreach ($modules as $v) {
            if ($v != '.' && $v != '..') {
                $folders[] = $v;
            }
        }
        
        if (is_dir(PATH_MODULE_ROOT_ADDON)) {
            self::log('CMS', 'CMS::init_modules addons() searching: ' . PATH_MODULE_ROOT_ADDON);
            $folders_addon = array();
            $modules = scandir(PATH_MODULE_ROOT_ADDON);
            foreach ($modules as $v) {
                if ($v != '.' && $v != '..') {
                    $folders_addon[] = $v;
                }
            }
        }
        
        if (is_dir(PATH_MODULE_ROOT_ADDON)) {
            foreach ($folders_addon as $v) {
                $folder = dir(PATH_MODULE_ROOT_ADDON . $v);
                while (false !== ($entry = $folder->read())) {
                    if (strpos($entry, '.mod.') !== false) {
                        $path = PATH_MODULE_ROOT_ADDON . $v . '/' . $entry;
                        if (!is_file(PATH_MODULE_ROOT_ADDON . $v . '/ignore.txt')) {
                            self::log('CMS', 'CMS::init_modules addon() found: ' . $path);
                            self::$__modules[] = array($path, $v);
                        } else {
                            self::log('CMS', 'CMS::init_modules addon() ignoring: ' . $path);
                        }
                    }
                }
                $folder->close();
            }
        }
        
        foreach ($folders as $v) {
            $folder = dir(PATH_MODULE_ROOT . $v);
            while (false !== ($entry = $folder->read())) {
                $found = false;
                if (strpos($entry, '.mod.') !== false) {
                    $path = PATH_MODULE_ROOT . $v . '/' . $entry;
                    if (!is_file(PATH_MODULE_ROOT . $v . '/ignore.txt')) {
                        foreach(self::$__modules as $name){
                            if($name[1] == $v){
                                $found = true;
                            }
                        }
                        if(!$found){
                            self::log('CMS', 'CMS::init_modules() found: ' . $path);
                            self::$__modules[] = array($path, $v);
                        }else{
                            self::log('CMS', 'CMS::init_modules() override: ' . $path);
                        }
                    } else {
                        self::log('CMS', 'CMS::init_modules() ignoring: ' . $path);
                    }
                }
            }
            $folder->close();
        }
        
        
        foreach (self::$__modules as $v) {
            include($v[0]);
        }
        
    }
    
    
    /**
     * Used to load modules and add them to the callstack
     */
    public static function init_usermod() {
        self::log('CMS', 'CMS::init_usermod() searching: ' . PATH_USERMOD_ROOT);

        $folders = array();
        $modules = scandir(PATH_USERMOD_ROOT);
        foreach ($modules as $v) {
            if ($v != '.' && $v != '..') {
                $folders[] = $v;
            }
        }
        
        if (is_dir(PATH_USERMOD_ROOT_ADDON)) {
            self::log('CMS', 'CMS::init_usermod addons() searching: ' . PATH_USERMOD_ROOT_ADDON);
            $folders_addon = array();
            $modules = scandir(PATH_USERMOD_ROOT_ADDON);
            foreach ($modules as $v) {
                if ($v != '.' && $v != '..') {
                    $folders_addon[] = $v;
                }
            }
        }

        foreach ($folders as $v) {
            $folder = dir(PATH_USERMOD_ROOT . $v);
            while (false !== ($entry = $folder->read())) {
                if (strpos($entry, '.usermod.') !== false) {
                    $path = PATH_USERMOD_ROOT . $v . '/' . $entry;
                    if (!is_file(PATH_USERMOD_ROOT . $v . '/ignore.txt')) {
                        self::log('CMS', 'CMS::init_usermod() found: ' . $path);
                        include $path;
                    } else {
                        self::log('CMS', 'CMS::init_usermod() ignoring: ' . $path);
                    }
                }
            }
            $folder->close();
        }

        if (is_dir(PATH_USERMOD_ROOT_ADDON)) {
            foreach ($folders_addon as $v) {
                $folder = dir(PATH_USERMOD_ROOT_ADDON . $v);
                while (false !== ($entry = $folder->read())) {
                    if (strpos($entry, '.usermod.') !== false) {
                        $path = PATH_USERMOD_ROOT_ADDON . $v . '/' . $entry;
                        if (!is_file(PATH_USERMOD_ROOT_ADDON . $v . '/ignore.txt')) {
                            self::log('CMS', 'CMS::init_usermod addon() found: ' . $path);
                            include $path;
                        } else {
                            self::log('CMS', 'CMS::init_usermod addon() ignoring: ' . $path);
                        }
                    }
                }
                $folder->close();
            }
        }
        
    }
    
    /**
     * Used to load modules and add them to the callstack
     */
    public static function init_appserve() {
        self::log('CMS', 'CMS::init_appserve() searching: ' . PATH_APPSERVE_ROOT);
        self::$__appserve = array();

        $folders = array();
        $modules = scandir(PATH_APPSERVE_ROOT);
        foreach ($modules as $v) {
            if ($v != '.' && $v != '..') {
                $folders[] = $v;
            }
        }
        
        if (is_dir(PATH_APPSERVE_ROOT_ADDON)) {
            $folders_addon = array();
            $modules = scandir(PATH_APPSERVE_ROOT_ADDON);
            foreach ($modules as $v) {
                if ($v != '.' && $v != '..') {
                    $folders_addon[] = $v;
                }
            }
        }

        foreach ($folders as $v) {
            $folder = dir(PATH_APPSERVE_ROOT . $v);
            while (false !== ($entry = $folder->read())) {
                if (strpos($entry, '.appserve.') !== false) {
                    $path = PATH_APPSERVE_ROOT . $v . '/' . $entry;
                    $__pages[] = strtolower($v);
                    self::log('CMS', 'CMS::init_appserve() found: ' . $path);
                    self::$__appserve[] = array($path, $v);
                }
            }
            $folder->close();
        }
        
        if (is_dir(PATH_APPSERVE_ROOT_ADDON)) {
            foreach ($folders_addon as $v) {
                $folder = dir(PATH_APPSERVE_ROOT_ADDON . $v);
                while (false !== ($entry = $folder->read())) {
                    if (strpos($entry, '.appserve.') !== false) {
                        $path = PATH_APPSERVE_ROOT_ADDON . $v . '/' . $entry;
                        $__pages[] = strtolower($v);
                        self::log('CMS', 'CMS::init_appserve() found: ' . $path);
                        self::$__appserve[] = array($path, $v);
                    }
                }
                $folder->close();
            }
        }
        
        foreach (self::$__appserve as $v) {
            include($v[0]);
        }
        
    }
    
    /**
     * Call all modules __registar_callback() method
     * 
     * In the future this will be repaced with reg system similar to usermods.
     */
    public static function init_modules_callbacks() {
        foreach (self::$__modules as $v) {
            $module = ucwords($v[1]);
            $name = '\Module\\'.$module;
            $try = call_user_func(array($name, '__registar_callback'));
        }
    }
    
    /**
     * Call all modules __registar_callback() method
     * 
     * In the future this will be repaced with reg system similar to usermods.
     */
    public static function init_appserve_callbacks() {
        foreach (self::$__appserve as $v) {
            $module = ucwords($v[1]);
            $name = '\Appserve\\'.$module;
            $try = call_user_func(array($name, '__registar_callback'));
        }
    }

    /**
     * Load the content types
     * 
     * This has only been tested with Html and Json parsers, still not fully tested
     * for new types.
     */
    public static function init_content_types() {
        self::log('CMS', 'CMS::init_content_types() searching: ' . PATH_CONTENT_TYPES_ROOT);

        $folders = array();
        $files = scandir(PATH_CONTENT_TYPES_ROOT);
        foreach ($files as $entry) {
            if (strpos($entry, '.type.') !== false) {
                $path = PATH_CONTENT_TYPES_ROOT . $entry;
                self::log('CMS', 'CMS::init_content_types() found: ' . $path);
                $folders[] = array($path, $entry);
            }
        }

        $folders_addon = array();
        if (is_dir(PATH_CONTENT_TYPES_ROOT_ADDON)) {
            $files = scandir(PATH_CONTENT_TYPES_ROOT_ADDON);
            foreach ($files as $entry) {
                if (strpos($entry, '.type.') !== false) {
                    $path = PATH_CONTENT_TYPES_ROOT_ADDON . $entry;
                    self::log('CMS', 'CMS::init_content_types() found: ' . $path);
                    $folders_addon[] = array($path, $entry);
                }
            }
        }

        foreach ($folders as $v) {
            include($v[0]);
            $module = explode('.', $v[1]);
            $module_name = ucwords($module[0]);
            $try = method_exists($module_name, '__registar_callback');
            if ($try) {
                /** NOTE: format changes in PHP 5.2.3 * */
                call_user_func(array($module_name, '__registar_callback'));
            }
        }

        if (is_dir(PATH_CONTENT_TYPES_ROOT_ADDON)) {
            foreach ($folders_addon as $v) {
                include($v[0]);
                $module = explode('.', $v[1]);
                $module_name = ucwords($module[0]);
                $try = method_exists($module_name, '__registar_callback');
                if ($try) {
                    /** NOTE: format changes in PHP 5.2.3 * */
                    call_user_func(array($module_name, '__registar_callback'));
                }
            }
        }
    }
    
    /**
     * Used to load pages and stack them into the callstack.
     */
    public static function init_pages() {
        self::log('CMS', 'CMS::init_pages() searching: ' . PATH_PAGE_ROOT);
        self::$__pages = array();

        $folders = array();
        $modules = scandir(PATH_PAGE_ROOT);
        foreach ($modules as $v) {
            if ($v != '.' && $v != '..') {
                $folders[] = $v;
            }
        }

        if (is_dir(PATH_PAGE_ROOT_ADDON)) {
            $folders_addon = array();
            $modules = scandir(PATH_PAGE_ROOT_ADDON);
            foreach ($modules as $v) {
                if ($v != '.' && $v != '..') {
                    $folders_addon[] = $v;
                }
            }
        }
        
        if (is_dir(PATH_PAGE_ROOT_ADDON)) {
            foreach ($folders_addon as $v) {
                $folder = dir(PATH_PAGE_ROOT_ADDON . $v);
                while (false !== ($entry = $folder->read())) {
                    $found = false;
                    if (strpos($entry, '.page.') !== false) {
                        $path = PATH_PAGE_ROOT_ADDON . $v . '/' . $entry;
                        $__pages[] = strtolower($v);
                        self::log('CMS', 'CMS::init_pages() found: ' . $path);
                        self::$__pages[] = array($path, $v);
                    }
                }
                $folder->close();
            }
        }

        foreach ($folders as $v) {
            $folder = dir(PATH_PAGE_ROOT . $v);
            while (false !== ($entry = $folder->read())) {
                $found = false;
                if (strpos($entry, '.page.') !== false) {
                    $path = PATH_PAGE_ROOT . $v . '/' . $entry;
                    foreach(self::$__pages as $name){
                        if($name[1] == $v){
                            $found = true;
                        }
                    }
                    if(!$found){
                        $__pages[] = strtolower($v);
                        self::log('CMS', 'CMS::init_pages() found: ' . $path);
                        self::$__pages[] = array($path, $v);
                    }else{
                        self::log('CMS', 'CMS::init_pages() override: ' . $path);
                    }
                }
            }
            $folder->close();
        }
        
        foreach (self::$__pages as $v) {
            include($v[0]);
        }
        
        
    }
    
    /**
     * Calls the active page based on URI, if that fails it calls the default
     * page set via DEFAULT_PAGE_GUEST
     */
    public static function init_active_page() {
        if (self::$_vars[0]) {
            if(method_exists('\Page\\'.ucfirst(self::$_vars[0]), 'active')){
                if (!call_user_func(array('\Page\\'.ucfirst(self::$_vars[0]), 'active'))) {
                } else {
                    call_user_func(array('\Page\\'.ucfirst(DEFAULT_PAGE_GUEST), 'active'));
                }
            }else{
                call_user_func(array('\Page\\'.ucfirst(DEFAULT_PAGE_GUEST), 'active'));
            }
        } else {
            call_user_func(array('\Page\\'.ucfirst(DEFAULT_PAGE_GUEST), 'active'));
        }
    }
    
    public static function init_active_appserve() {
        if(method_exists('\Appserve\\'.ucfirst(self::$_vars[1]), 'active')){
            if (!call_user_func(array('\Appserve\\'.ucfirst(self::$_vars[1]), 'active'))) {
                
            }
        }else{
            die( json_encode( array('status'=>'fail') ) );
        }
    }
    
    /**
     * Load the user based on session data, if no user set, it will set DEFAULT_USER
     */
    public static function init_user() {
        if (!isset($_SESSION['user'])) {
            self::$_user = new User(DEFAULT_USER);
            $_SESSION['user'] = DEFAULT_USER;
            $_SESSION['super_user'] = 'no';
            $_SESSION['user_allow_ext'] = false;
            
        } else {
            if (is_numeric($_SESSION['user']) && $_SESSION['user'] > 0) {
                self::$_user = new User($_SESSION['user']);
                if(self::$_user->mod_user == 'yes'){
                    $_SESSION['mod_user'] = 'yes';
                }else{
                    $_SESSION['mod_user'] = 'no';
                }
                if(self::$_user->super_user == 'yes'){
                    $_SESSION['super_user'] = 'yes';
                }else{
                    $_SESSION['super_user'] = 'no';
                }
                
                if(self::$_user->_error){
                    self::$_user = new User(DEFAULT_USER);
                    $_SESSION['user'] = DEFAULT_USER;
                    $_SESSION['super_user'] = 'no';
                    $_SESSION['user_allow_ext'] = false;
                }
            } else {
                self::$_user = new User(DEFAULT_USER);
                $_SESSION['user'] = DEFAULT_USER;
                $_SESSION['super_user'] = 'no';
                $_SESSION['user_allow_ext'] = false;
            }
        }
    }
    
    /**
     * Call all page __registar_callback() methods
     * 
     * This should not be used much, for better semantics methods called outside
     * a page should be in there own module. Its only here for legacy modules.
     */
    public static function init_page_callbacks() {
        foreach (self::$__pages as $v) {
            $page = ucwords($v[1]);
            $name = '\Page\\'.$page;
            $try = call_user_func(array($name, '__registar_callback'));
        }
    }

    /**
     * Sets a callback
     * 
     * @param string $method name of method
     * @param int $loop index of the callback
     */
    public static function callstack_add($method, $loop = 0) {
        $trace = debug_backtrace();
        //echo '<pre>'.print_r($trace, true).'</pre>-------------------------------';
        $type = explode('.', $trace[0]['file']);
        switch($type[1]){
            case 'mod':
                $type = 'Module\\';
            default:
                $type = $type[1];
        }
        self::$__callstack[$loop][] = array($trace[1]['class'], $method, $type);
    }

    /**
     * runs the callback stack
     * 
     * @param int $loop index of the callback
     */
    public static function callstack_run($loop) {
        ksort(self::$__callstack);
        if (isset(self::$__callstack[$loop])) {
            foreach (self::$__callstack[$loop] as $call) {
                $try = method_exists($call[0], $call[1]);
                if ($try) {
                    /** NOTE: format changes in PHP 5.2.3 * */
                    $try = call_user_func(array($call[0], $call[1]));
                } else {
                    self::log('CMS', 'callback missing: ' . $call[0] . '::' . $call[1]);
                }
            }
        }
        /* this nest limit is only with some debuggers */
        if ($loop < 80) {
            self::callstack_run($loop + 1);
        } else {
            self::log('CMS', 'nest limit reached on loop ' . $loop);
        }
    }

    /**
     * Returns the callstack in a html friendly format
     * 
     * @return html 
     */
    public static function callstack_display_html() {
        $html = array();
        $html[] = '
            <div style="float: left; margin: 10px; width: 400px">
            <table style="width: 400px" id="system-callstack">
            <thead>
                <tr>
                    <th>loop</th>
                    <th>class</th>
                    <th>method</th>
                </tr>
            </thead>
            <tbody>
        ';
        foreach (self::$__callstack as $key => $v) {
            foreach ($v as $v2) {
                $html[] = '
                    <tr>
                        <td>' . $key . '</td>
                        <td>' . $v2[0] . '</td>
                        <td>' . $v2[1] . '</td>
                    </tr>
                ';
            }
        }
        $html[] = '</tbody></table></div>';
        $html[] = '
            <script type="text/javascript">
                oTable = $("#system-callstack").dataTable({
                    "bJQueryUI": true
                });
            </script>
        ';
        return implode(PHP_EOL, $html);
    }

    /**
     * Log event
     * 
     * Level 
     *  0 = notice
     *  1 = warn
     *  2 = error
     *  3 = fatal
     * 
     * @param string $module name of the modules
     * @param string $details details of event
     * @param int $level level of event 0 to 3
     */
    public static function log($module, $details, $level = 0) {
        self::$__log[] = array(
            ( microtime() - BUILD_TIME ),
            $module,
            $details,
            $level
        );
    }

    /**
     * Returns the log in a html friendly format
     * 
     * @return html 
     */
    public static function log_display_html() {
        $html = array();
        $html[] = '
            <div style="float: left; margin: 10px; width: 900px">
            <table style="width: 900px" id="system-log">
                <thead>
                    <tr>
                        <th>time</th>
                        <th>module</th>
                        <th>details</th>
                        <th>level</th>
                    </tr>
                </thead>
                <tbody>
                ';
        foreach (self::$__log as $v) {
            $html[] = '
                <tr>
                    <td>' . $v[0] . '</td>
                    <td>' . $v[1] . '</td>
                    <td>' . $v[2] . '</td>
                    <td>' . $v[3] . '</td>
                </tr>
            ';
        }
        $html[] = '</tbody></table></div>';
        $html[] = '
            <script type="text/javascript">
                oTable = $("#system-log").dataTable({
                    "bJQueryUI": true
                });
            </script>
        ';
        return implode(PHP_EOL, $html);
    }
    
    /**
     * Redirect to either a supported page or ext location.
     * Only home, login supported for internal.
     * 
     * @param string $target home, login supported
     */
    public static function redirect($target) {
        switch ($target) {
            case 'home':
                header("Location: " . DEFAULT_PROTOCOL . DOMAIN . PATH_BASE);
                die();
                break;

            case 'login':
                header("Location: " . DEFAULT_PROTOCOL . DOMAIN . PATH_BASE . 'login');
                die();
                break;

            default;
                header("Location: " . $target);
                die();
        }
    }
    
    /**
     * Checks if a user has permission to access a method. Anytime you want to
     * restrit a user just add to the __registar_callback() call in your module
     * to check.
     * @return bool
     */
    public static function allowed() {
        return self::$_user->allowed();
    }
    
    /**
     * Register a callback, currently only the UserMod's are supported but 
     * future modules will add this to replace __registar_callback
     * @param string $class
     * @param string $type
     * @throws Exception if a type is not set, only UserMod is supported
     */
    public static function register($class, $type){
        switch($type){
            case 'UserMod':
                self::$_usermod[] = $class;
                break;
            default:
                throw new Exception('Unknown type "'.$type.'" can not be registered in CMS::register. Caller: '.$class);
        }
    }
    
    /**
     * returns epoch time since page started
     * @return float
     */
    public static function get_build_time(){
        return round(microtime(true) - BUILD_TIME, 3);
    }

}
