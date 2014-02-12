<?php
/**
 * This is the base configuration. You should not make changes in here
 * All changes should be added to the site/config.inc.php
 * 
 */

if(!defined('ALLOW_INSTALL')){
    define('ALLOW_INSTALL', false);
}

if(!defined('ENABLE_DEBUG')){
    define('ENABLE_DEBUG', false);
}

if(!defined('ENABLE_DEBUG_FORCED_OUTPUT')){
    define('ENABLE_DEBUG_FORCED_OUTPUT', false);
}

if(!defined('ENABLE_DEBUG_TRACE')){
    define('ENABLE_DEBUG_TRACE', false);
}

if(!defined('ALLOW_DB_DEBUG')){
    define('ALLOW_DB_DEBUG', false);
}

if(!defined('SITE_NAME')){
    define('SITE_NAME', 'Temporal'); // Name of your site
}

if(!defined('BRANDING_FOLDER')){
    define('BRANDING_FOLDER', 'default'); //Branding package
}

if(!defined('PATH_LAYOUT_ROOT')){
    /*
    * Setting this will disable the default layout, if this folder is not found
    * the system will enable the default template.
    */
    define('PATH_LAYOUT_ROOT', 'site/layout/test/');
}

if(!defined('DATABASE_HOST')){
    define('DATABASE_HOST', 'localhost');
}

if(!defined('DATABASE_USER')){
    define('DATABASE_USER', 'root');
}

if(!defined('DATABASE_PASSWORD')){
    define('DATABASE_PASSWORD', '');
}

if(!defined('DATABASE_TABLE')){
    define('DATABASE_TABLE', 'temporal_v7');
}

if(!defined('DOMAIN')){
    define('DOMAIN', '127.0.0.1');
}

if(!defined('PASS_SALT')){
    define('PASS_SALT', 'guejd;ag873hasf72yuwea_sd38yfaskdfh8skf2');
}

if(!defined('DEFAULT_USER')){
    #Default user loaded, i.e. guest
    define('DEFAULT_USER', 1);
}

if(!defined('PATH_BASE')){
    define('PATH_BASE', '/');
}

if(!defined('LOCAL_PATH')){
    define('LOCAL_PATH', $_SERVER['DOCUMENT_ROOT'] );
}

if(!defined('PATH_LAYOUT_ROOT_DEFAULT')){
    #Path to layout folder
    define('PATH_LAYOUT_ROOT_DEFAULT', 'system/layout/default/');
}

if(!defined('PATH_LAYOUT_ROOT_ADDON')){
    #Path to layout addon folder
    define('PATH_LAYOUT_ROOT_ADDON', 'site/layout/');
}

if(!defined('LAYOUT_DEFAULT')){
    #Default html, includes all wrapping elements
    define('LAYOUT_DEFAULT', 'default.html');
}

if(!defined('PATH_MODULE_ROOT')){
    #Path to module folder
    define('PATH_MODULE_ROOT', 'system/modules/');
}

if(!defined('PATH_CLASSES_ROOT_ADDON')){
    #Path to module folder
    define('PATH_CLASSES_ROOT_ADDON', 'site/classes/');
}

if(!defined('PATH_MODULE_ROOT_ADDON')){
    #Path to module folder addon
    define('PATH_MODULE_ROOT_ADDON', 'site/modules/');
}

if(!defined('PATH_USERMOD_ROOT')){
    #Path to user module folder addon
    define('PATH_USERMOD_ROOT', 'system/usermod/');
}

if(!defined('PATH_USERMOD_ROOT_ADDON')){
    #Path to user module folder addon
    define('PATH_USERMOD_ROOT_ADDON', 'site/usermod/');
}

if(!defined('PATH_APPSERVE_ROOT')){
    #Path to user module folder addon
    define('PATH_APPSERVE_ROOT', 'system/appserve/');
}

if(!defined('PATH_APPSERVE_ROOT_ADDON')){
    #Path to user module folder addon
    define('PATH_APPSERVE_ROOT_ADDON', 'site/appserve/');
}

if(!defined('PATH_PAGE_ROOT')){
    define('PATH_PAGE_ROOT', 'system/pages/');
}

if(!defined('PATH_PAGE_ROOT_ADDON')){
    define('PATH_PAGE_ROOT_ADDON', 'site/pages/');
}

if(!defined('PATH_CONTENT_TYPES_ROOT')){
    define('PATH_CONTENT_TYPES_ROOT', 'system/content_types/');
}

if(!defined('PATH_CONTENT_TYPES_ROOT_ADDON')){
    define('PATH_CONTENT_TYPES_ROOT_ADDON', 'site/content_types/');
}

if(!defined('PATH_MEDIA')){
    define('PATH_MEDIA', 'site/media/');
}

if(!defined('CACHE_PATH')){
    define('CACHE_PATH', PATH_MEDIA.'cache/');
}

if(!defined('DEFAULT_PAGE_AUTH')){
    define('DEFAULT_PAGE_AUTH', 'Zpage');
}

if(!defined('DEFAULT_PAGE_GUEST')){
    define('DEFAULT_PAGE_GUEST', 'Zpage');
}

if(!defined('REGISTER_DEFAULT_GROUPS')){
    define('REGISTER_DEFAULT_GROUPS', '1');
}

/**
 * Only "admin" and "community" are supported
 */
if(!defined('USER_TYPE')){
    define('USER_TYPE', 'admin');
}

if(!defined('DEFAULT_ZPAGE')){
    define('DEFAULT_ZPAGE', 1);
}

if(!defined('MAX_ZONES')){
    define('MAX_ZONES', 20);
}

if(!defined('DEFAULT_PROTOCOL')){
    define('DEFAULT_PROTOCOL', 'http://');
}

if(!defined('DEFAULT_CALLBACK_SETUP')){
    define('DEFAULT_CALLBACK_SETUP', 10);
}

if(!defined('DEFAULT_CALLBACK_CREATE')){
    define('DEFAULT_CALLBACK_CREATE', 20);
}

if(!defined('DEFAULT_CALLBACK_PARSE')){
    define('DEFAULT_CALLBACK_PARSE', 30);
}

if(!defined('DEFAULT_CALLBACK_OUTPUT')){
    define('DEFAULT_CALLBACK_OUTPUT', 50);
}
if(!defined('ENABLE_MAIL')){
    define('ENABLE_MAIL',false);
}

if(!defined('MAIL_HOST')){
    define('MAIL_HOST', false);
}
if(!defined('MAIL_SMTP_DEBUG')){
    define('MAIL_SMTP_DEBUG', 0);
}
if(!defined('MAIL_SMTP_AUTH')){
    define('MAIL_SMTP_AUTH', true);
}
if(!defined('MAIL_PORT')){
    define('MAIL_PORT', 465);
}
if(!defined('MAIL_USERNAME')){
    define('MAIL_USERNAME', false);
}
if(!defined('MAIL_PASSWORD')){
    define('MAIL_PASSWORD', false);
}
if(!defined('MAIL_FROM_EMAIL')){
    define('MAIL_FROM_EMAIL', false);
}
if(!defined('MAIL_FROM_NAME')){
    define('MAIL_FROM_NAME', false);
}
if(!defined('MAIL_SECURE')){
    define('MAIL_SECURE', 'ssl');
}
if(!defined('ENABLE_CACHE')){
    define('ENABLE_CACHE', false); //experimental
}

if(!defined('CACHE_TIME')){
    define('CACHE_TIME', "+24 hour"); // this is wrapped into strtotime();
}

if(!defined('ZPAGE_HISTORY_LIMIT')){
    define('ZPAGE_HISTORY_LIMIT', 10);
}

$config = array();
$config['vars'] = array
(
    'uvar1',
    'uvar2',
    'uvar3',
    'uvar4',
    'uvar5',
    'uvar6',
    'uvar7'
);

if( is_dir(PATH_LAYOUT_ROOT) ){
    $config['path_layout'] = PATH_LAYOUT_ROOT;
    $config['path_layout_default'] = PATH_LAYOUT_ROOT.LAYOUT_DEFAULT;
}else{
    $config['path_layout'] = PATH_LAYOUT_ROOT_DEFAULT;
    $config['path_layout_default'] = PATH_LAYOUT_ROOT_DEFAULT.LAYOUT_DEFAULT; 
}