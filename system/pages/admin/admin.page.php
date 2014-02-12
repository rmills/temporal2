<?php

namespace Page;

class Admin extends Page {
    public static $_isrestricted = true;
    static public $_body = false;
    static public $_quicklinks = array();
    static public $_links = array();
    static public $_subpage = false;

    public static function __registar_callback() {
        if (\CMS::allowed()) {
            self::add_quick_link('<li><a href="{root_doc}logout">Logout</a></li>', 300);
            if (\CMS::$_vars[0] != 'admin') {
                self::add_quick_link('<li><a href="{root_doc}admin">Control Panel</a></li>');
            }
        }
    }

    public static function active() {
        if (\CMS::allowed()) {
            \CMS::$_page_type = 'admin';
            \CMS::$_content_type = 'html';
            \CMS::callstack_add('set_tags', DEFAULT_CALLBACK_CREATE + 1);
            \CMS::callstack_add('parse_tags', DEFAULT_CALLBACK_PARSE + 1);
            \CMS::callstack_add('check_for_subpage', DEFAULT_CALLBACK_OUTPUT - 5);
        } else {
            \CMS::redirect('login');
        }
    }

    public static function set_tags() {
        \Html::template(self::build_admin_template());
        \Html::set('{admin_title}', SITE_NAME . ' Administration');
        Admin::add_link('<li><a href="{root_doc}">Return to site</a></li>');
        Admin::add_link('<li><a href="{root_doc}logout">Logout</a></li>');


        self::build_admin_menu();
        if (!self::$_subpage) {
            self::build_welcome();
        }
    }

    public static function template_body($html) {
        self::$_body = $html;
    }

    public static function parse_tags() {
        \Html::set('{admincontent}', self::$_body);
    }

    public static function build_admin_template() {
        $html = self::block('admin.html');
        return $html;
    }

    public static function build_admin_menu() {
        \Html::set('{admin_nav}', self::build_menu_links());
    }

    public static function build_menu_links() {
        ksort(self::$_links);
        $html = array();
        $html[] = '<li class="divider-vertical"></li>';
        foreach (self::$_links as $v) {
            $html[] = $v . '<li class="divider-vertical"></li>';
        }
        return implode(PHP_EOL, $html);
    }

    public static function add_quick_link($link, $weight = 100) {
        if (!isset(self::$_quicklinks[$weight])) {
            self::$_quicklinks[$weight] = $link;
        } else {
            self::add_quick_link($link, $weight + 1);
        }
    }

    public static function add_link($link) {
        self::$_links[] = $link;
    }

    public static function build_welcome() {
        $html = '<h2>Welcome to Temporal Site Management</h2><p style="text-align:center"><img src="{root_doc}system/inc/images/llama.jpg" /></p>';
        \Html::set('{admin_content}', $html);
    }

}
