<?php

include 'upload.class.php';

namespace Module;

class Media_manager extends Module {
    public static $_isrestricted = true;
    public static function __registar_callback() {
        if (\CMS::allowed()) {
            \CMS::callstack_add('set_nav', DEFAULT_CALLBACK_CREATE);
            if (\CMS::$_vars[0] == 'admin' && \CMS::$_vars[1] == 'media') {
                \CMS::callstack_add('create', DEFAULT_CALLBACK_CREATE);
            }
        }
    }

    public static function set_nav() {
        Admin::add_link('Media', '{root_doc}admin/media');
    }

    public static function create() {
        Admin::$_subpage = true;
        switch (\CMS::$_vars[2]) {
            case 'upload':
                \CMS::$_content_type = 'json';
                self::process_upload();
            default:
                self::display_home();
        }
    }

    public static function display_home() {
        $html = self::block('home.html');
        $single = self::block('single.html');
        \Html::set('{admin_content}', $html);
        \Html::set('{media_content}', $single);
        //\Html::set('{folders}', self::build_folder_selector_list() );
    }

    public static function process_upload() {

        $upload_handler = new UploadHandler();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                break;
            case 'HEAD':
            case 'GET':
                $upload_handler->get();
                break;
            case 'POST':
                if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
                    $upload_handler->delete();
                } else {
                    $upload_handler->post();
                }
                break;
            case 'DELETE':
                $upload_handler->delete();
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
        }
    }

    public static function build_folder_selector_list($ignore_id = null, $first = null) {
        $html = array();
        if (is_array($first)) {
            $html[] = '<option value="' . $first[0] . '">' . $first[1] . '</option>';
        }
        $html[] = '<option value="1">Root</option>';
        $sql = 'SELECT * FROM `media_folders` ORDER BY `name` ASC';
        $response = \DB::q($sql);
        foreach ($response as $item) {
            if ($ignore_id != $item['fid'] && $item['fid'] != '1') {
                $html[] = '<option value="' . $item['fid'] . '">' . $item['name'] . '</option>';
            }
        }
        return implode(PHP_EOL, $html);
    }

}