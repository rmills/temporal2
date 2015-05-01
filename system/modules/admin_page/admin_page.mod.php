<?php

namespace Module;

class Admin_page extends Module {
    public static $_isrestricted = true;
    private static $_pagemode = false;
    private static $_status = false;
    private static $_restricted_url = array('admin', 'edit', 'page', 'login', 'logout');
    private static $_new_page_added = false;

    public static function __registar_callback() {
        if (\CMS::allowed()) {
            if (\CMS::$_vars[0] == 'admin' && \CMS::$_vars[1] == 'page') {
                \CMS::callstack_add('create', DEFAULT_CALLBACK_CREATE);
                \CMS::callstack_add('set_nav', DEFAULT_CALLBACK_CREATE);
                \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
            } else {
                \CMS::callstack_add('set_nav', DEFAULT_CALLBACK_CREATE);
            }
        }
    }

    public static function create() {
        \Page\Admin::$_subpage = true;
    }

    public static function set_nav() {
        \Page\Admin::add_link('
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Pages<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="{root_doc}admin/page/add">Add Page</a></li>
                    <li><a href="{root_doc}admin/page/list">List Pages</a></li>
                </ul>
            </li>
        ');
    }

    public static function parse() {
        /* Pre Action Handlers */
        if (\CMS::$_vars[3] == 'submit') {
            switch (\CMS::$_vars[2]) {
                case 'edit':
                    self::edit_page_submit();
                    break;
            }
        }

        /* Post Action Handlers */
        if (\CMS::$_vars[3] == 'submit') {
            switch (\CMS::$_vars[2]) {
                case 'add':
                    self::add_page_submit();
                    break;
            }
        }
        
        /* Page Content Handlers */
        switch (\CMS::$_vars[2]) {
            case 'add':
                self::$_pagemode = 'add';
                self::add_page();
                break;

            case 'edit':
                self::$_pagemode = 'edit';
                self::edit_page();
                break;

            case 'list':
                self::$_pagemode = 'list';
                self::edit_list();
                break;

            case 'confirmdelete':
                self::$_pagemode = 'confirmdelete';
                self::confirmdelete_page();
                break;

            case 'confirmpdelete':
                self::$_pagemode = 'confirmpdelete';
                self::confirmpdelete_page();
                break;

            case 'delete':
                self::$_pagemode = 'delete';
                self::delete_page();
                break;

            case 'restore':
                self::$_pagemode = 'restore';
                self::restore_page();
                break;

            case 'pdelete':
                self::$_pagemode = 'pdelete';
                self::permanently_remove_page();
                break;
        }
        

        if (self::$_status) {
            \Html::set('{status}', self::$_status);
        }
    }

    public static function add_page() {
        $html = self::block('add.html');
        \Html::set('{admin_content}', $html);
        \Html::set('{templates}', self::build_template_list('option_list'));
        \Html::set('{parent}', self::build_parent_list(false));
        if(!self::$_new_page_added){
            if (isset($_POST['title'])) {
                \Html::set('{settitle}', $_POST['title']);
            } else {
                \Html::set('{settitle}');
            }

            if (isset($_POST['menu_title'])) {
                \Html::set('{menu_title}', $_POST['menu_title']);
            } else {
                \Html::set('{menu_title}');
            }

            if (isset($_POST['menu_weight'])) {
                \Html::set('{menu_weight}', $_POST['menu_weight']);
            } else {
                \Html::set('{menu_weight}', 100);
            }

            if (isset($_POST['publish'])) {
                \Html::set('{publish}', 'checked="checked"');
            } else {
                \Html::set('{publish}');
            }

            if (isset($_POST['url'])) {
                \Html::set('{seturl}', $_POST['url']);
            } else {
                \Html::set('{seturl}');
            }
        }else{
            \Html::set('{settitle}');
            \Html::set('{menu_title}');
            \Html::set('{menu_weight}', 100);
            \Html::set('{publish}');
            \Html::set('{seturl}');
        }
        \Html::set('{status}');
    }

    public static function add_page_submit() {
        $title = trim($_POST['title']);
        $url = strtolower(trim($_POST['url']));
        $url = self::format_url($url);
        $template = pathinfo(trim($_POST['template']), PATHINFO_FILENAME) . '.' . pathinfo(trim($_POST['template']), PATHINFO_EXTENSION);

        $weight = trim($_POST['menu_weight']);
        $menu_title = trim($_POST['menu_title']);
        $parent = trim($_POST['parent']);

        if (isset($_POST['publish'])) {
            if ($_POST['publish'] == 'on') {
                $publish = 'yes';
            } else {
                $publish = 'no';
            }
        } else {
            $publish = 'no';
        }

        if (!self::vailidate_url($url)) {
            self::$_status = '<p class="alert alert-danger">Clean URL already in use, please select a new one</p>';
            return false;
        }

        $sql = '
            INSERT INTO pages (
                `title`,
                `url`,
                `template`,
                `weight`,
                `menu_title`,
                `parent`,
                `published`
            ) VALUES (
                \'' . \DB::clean($title) . '\',
                \'' . \DB::clean($url) . '\',
                \'' . \DB::clean($template) . '\',
                \'' . \DB::clean($weight) . '\',
                \'' . \DB::clean($menu_title) . '\',
                \'' . \DB::clean($parent) . '\',
                \'' . \DB::clean($publish) . '\'
                
		)';
        \DB::q($sql);
        $new_page_id = \DB::$_lastid;

        $i = 1;
        while ($i <= MAX_ZONES) {
            self::init_zone('z' . $i, $new_page_id);
            $i++;
        }
        self::$_new_page_added = true;
        self::$_status = '<p class="alert alert-success"><i class="icon icon-ok"></i> Page Created</p><p><a class="btn btn-info" href="{root_doc}' . $url . '">View Page</a></p>';
    }

    public static function edit_page() {
        $html = self::block('edit.html');
        $pid = false;
        if (is_numeric(\CMS::$_vars[3])) {
            $pid = \CMS::$_vars[3];
        }elseif( isset($_POST['pid']) ) {
            $pid = $_POST['pid'];
        }
        
        if(!$pid){
            \Html::set('{admin_content}', '<h1>Oops, you can not edit the URL for this page</h1>');
            return true;
        }
        

        $zdata = \Page\Zpage::fetch_by_id($pid, true);

        

        \Html::set('{admin_content}', $html);
        \Html::set('{templates}', self::build_template_list('option_list', $zdata['template']));
        \Html::set('{status}');
        \Html::set('{settitle}', $zdata['title']);
        \Html::set('{seturl}', $zdata['url']);
        \Html::set('{setdescription}', $zdata['meta_description']);
        \Html::set('{setkeywords}', $zdata['meta_keywords']);
        \Html::set('{pid}', $pid);
        \Html::set('{returnlink}', $zdata['url']);
        \Html::set('{menu_weight}', $zdata['weight']);
        \Html::set('{menu_title}', $zdata['menu_title']);

        if ($zdata['published'] == 'yes') {
            \Html::set('{publish}', 'checked="checked"');
        } else {
            \Html::set('{publish}');
        }

        if ($zdata['parent'] == '0' || !is_numeric($zdata['parent'])) {
            \Html::set('{parent}', self::build_parent_list($zdata['pid']));
        } else {
            $parent = \Page\Zpage::fetch_by_id($zdata['parent']);
            if (is_array($parent)) {
                \Html::set('{parent}', self::build_parent_list($zdata['pid'], array($parent['pid'], $parent['title'])));
            } else {
                \Html::set('{parent}', self::build_parent_list($zdata['pid']));
            }
        }
    }

    public static function edit_page_submit() {
        $title = trim($_POST['title']);
        $url = strtolower(trim($_POST['url']));
        $url = self::format_url($url);
        $template = pathinfo(trim($_POST['template']), PATHINFO_FILENAME) . '.' . pathinfo(trim($_POST['template']), PATHINFO_EXTENSION);
        $description = trim($_POST['description']);
        $keywords = trim($_POST['keywords']);
        $pid = $_POST['pid'];

        $weight = trim($_POST['menu_weight']);
        $menu_title = trim($_POST['menu_title']);
        $parent = trim($_POST['parent']);

        if (isset($_POST['publish'])) {
            if ($_POST['publish'] == 'on') {
                $publish = 'yes';
            } else {
                $publish = 'no';
            }
        } else {
            $publish = 'no';
        }

        if (!self::vailidate_url($url, $pid)) {
            self::$_status = '<p class="alert alert-danger">Clean URL already in use, please select a new one</p>';
            return false;
        }


        $sql = 'UPDATE `pages` SET 
		`title` = \'' . \DB::clean($title) . '\',
		`url` = \'' . \DB::clean($url) . '\',
		`template` = \'' . \DB::clean($template) . '\',
		`meta_description` = \'' . \DB::clean($description) . '\',
		`meta_keywords` = \'' . \DB::clean($keywords) . '\',
        `weight` = \'' . \DB::clean($weight) . '\',
        `menu_title` = \'' . \DB::clean($menu_title) . '\',
        `parent` = \'' . \DB::clean($parent) . '\',
        `published` = \'' . \DB::clean($publish) . '\'
		
		WHERE `pid` = \'' . \DB::clean($pid) . '\' LIMIT 1';

        \DB::q($sql);
        self::$_status = '<p class="alert alert-success"><i class="icon icon-ok"></i> Page Updated</p><p><a class="btn btn-info" href="{root_doc}' . $url . '">View Page</a></p>';
    }

    public static function confirmdelete_page() {
        $pid = \CMS::$_vars[3];
        $zdata = \Page\Zpage::fetch_by_id($pid);

        $html = '<h4>Confirm Delete</h4><hr /><p>Are you sure you want to delete the page titled "' . $zdata['title'] . '"?<div class="form-actions"><a class="btn btn-danger" href="{root_doc}admin/page/delete/' . $pid . '">Delete</a> <a class="btn btn-info" href="{root_doc}admin/page/list">Cancel</a></div>';
        \Html::set('{admin_content}', $html);
        \Html::set('{admin_title}', 'Page Options');
    }

    public static function confirmpdelete_page() {
        $pid = \CMS::$_vars[3];
        $zdata = \Page\Zpage::fetch_by_id($pid, true);
        $html = '<h4>Confirm Permanently Remove</h4><hr /><p>Are you sure you want to permanently delete the page titled "' . $zdata['title'] . '"?<div class="form-actions"><a class="btn btn-danger" href="{root_doc}admin/page/pdelete/' . $pid . '">Delete</a> <a class="btn btn-info" href="{root_doc}admin/page/list">Cancel</a></div>';
        \Html::set('{admin_content}', $html);
    }

    public static function delete_page() {
        $pid = \CMS::$_vars[3];
        if ($pid !== '1') {
            $sql = 'UPDATE `pages` SET `status` = \'deleted\' WHERE `pid` = \'' . \DB::clean($pid) . '\' LIMIT 1';
            \DB::q($sql);
            $html = '<h4>Pages</h4><hr /><p class="alert alert-success">Success: Page Deleted</p><div class="form-actions"><a class="btn btn-info" href="{root_doc}admin/page/list/">Return</a></div>';
            \Html::set('{admin_content}', $html);
        } else {
            $html = '<h4>Pages</h4><hr /><p class="alert alert-danger">Failed: Default home page can not be removed.</p><div class="form-actions"><a class="btn btn-info" href="{root_doc}admin/page/list/">Return</a></div>';
            \Html::set('{admin_content}', $html);
        }
    }

    public static function permanently_remove_page() {
        $pid = \CMS::$_vars[3];
        if ($pid !== '1') {
            $sql = 'DELETE FROM `pages` WHERE `pid` = \'' . \DB::clean($pid) . '\' LIMIT 1';
            \DB::q($sql);
            $html = '<h4>Pages</h4><hr /><p class="alert alert-success">Success: Page Permanently Deleted</p><div class="form-actions"><a class="btn btn-info" href="{root_doc}admin/page/list/">Return</a></div>';
            \Html::set('{admin_content}', $html);
        } else {
            $html = '<h4>Pages</h4><hr /><p class="alert alert-danger">Failed: Default home page can not be removed.</p><div class="form-actions"><a class="btn btn-info" href="{root_doc}admin/page/list/">Return</a></div>';
            \Html::set('{admin_content}', $html);
        }
    }

    public static function restore_page() {
        $pid = \CMS::$_vars[3];
        $zdata = \Page\Zpage::fetch_by_id($pid, true);

        $sql = 'UPDATE `pages` SET `status` = \'active\' WHERE `pid` = \'' . \DB::clean($pid) . '\' LIMIT 1';
        \DB::q($sql);
        $html = '<h4>Success</h4><hr /><p>Page "' . $zdata['title'] . '" restored.</p><div class="form-actions"><a class="btn btn-info" href="{root_doc}admin/page/list/">Return</a></div>';

        \Html::set('{admin_content}', $html);
        return true;
    }

    public static function build_template_list($format = 'option_list', $first = false) {
        $files = array();
        $folder = dir(\CMS::$_config['path_layout']);
        while (false !== ($entry = $folder->read())) {
            if (pathinfo($entry, PATHINFO_EXTENSION) == 'html' && !is_numeric(pathinfo($entry, PATHINFO_FILENAME))) {
                if($entry{0} != '_'){
                    $files[] = $entry;
                }
            }
        }
        $folder->close();

        switch ($format) {
            case 'option_list':
                $html = array();
                if ($first) {
                    $html[] = '<option value="' . $first . '">' . $first . '</option>';
                }
                foreach ($files as $v) {
                    $html[] = '<option value="' . $v . '">' . $v . '</option>';
                }
                return implode(PHP_EOL, $html);
        }
    }

    public static function build_parent_list($ignore_id, $first = false) {
        $html = array();
        if (is_array($first)) {
            $html[] = '<option value="' . $first[0] . '">' . $first[1] . '</option>';
        }
        $html[] = '<option value="0">Root</option>';
        $sql = 'SELECT * FROM `pages` WHERE `status` = \'active\' ORDER BY `title` ASC';
        $response = \DB::q($sql);
        foreach ($response as $item) {
            if ($ignore_id != $item['pid']) {
                $html[] = '<option value="' . $item['pid'] . '">' . $item['title'] . '</option>';
            }
        }
        return implode(PHP_EOL, $html);
    }

    public static function init_zone($zid, $pid) {
        $zdate = date("m-d-Y");
        $zdata = 'New Editable Zone';
        $sql = '
		INSERT INTO zones (
			`z_data`,
			`z_creation`
		) VALUES (
			\'' . \DB::clean($zdata) . '\',
			\'' . $zdate . '\'
		)';
        \DB::q($sql);

        $sql = '
			UPDATE `pages` SET 
				`' . \DB::clean($zid) . '` = \'' . \DB::clean(\DB::$_lastid) . '\'
			WHERE 
				`pid` = \'' . \DB::clean($pid) . '\' 
			LIMIT 1
		';
        \DB::q($sql);
    }

    public static function vailidate_url($url, $pid = 0) {
        $sql = 'SELECT url, pid FROM `pages` WHERE `status` = \'active\'';
        $list = \DB::q($sql);
        foreach ($list as $v) {
            if ($url == $v['url']) {
                if ($v['pid'] != $pid) {
                    return false;
                }
            }
        }

        foreach (self::$_restricted_url as $v) {
            if ($url == $v) {
                return false;
            }
        }

        return true;
    }

    public static function edit_list() {
        $html = self::block('list.html');
        $sql = 'SELECT * FROM `pages`';
        $list = \DB::q($sql);
        $pages = array();
        foreach ($list as $v) {

            if ($v['status'] != 'active') {
                $status = '<a class="btn btn-danger disabled">' . $v['status'] . '</a>';
            } else {
                $status = '<a class="btn btn-info disabled">' . $v['status'] . '</a>';
            }
            /*
              if($v['super_user'] == 'yes'){
              $super = '<a class="btn btn-warning disabled">Enabled</a>';
              }else{
              $super = '<a class="btn btn-info disabled">Disabled</a>';
              }
             */
            $pages[] = '
                <tr>
                    <td>' . $v['pid'] . '</td>
                    <td>' . $v['title'] . '</td>
                    <td>' . $v['url'] . '</td>
                    <td>' . $status . '</td>
                    <td>';
            $pages[] = '
                <div class="btn-group">
                <button type="button" class="btn btn-primary">Options</button>
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                  <span class="caret"></span>
                  <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="{root_doc}' . $v['url'] . '">view</a></li>
                     <li><a href="{root_doc}admin/page/edit/' . $v['pid'] . '"><i class="icon-edit icon-white"></i> edit</a></li>
            ';

            if ($v['status'] == 'active') {
                $pages[] = '     <li><a href="{root_doc}admin/page/confirmdelete/' . $v['pid'] . '"><i class="icon-white icon-ban-circle"></i> delete</a></li>';
            } else {
                $pages[] = '     <li><a href="{root_doc}admin/page/restore/' . $v['pid'] . '"><i class="icon-white icon-share-alt"></i> restore</a></li>';
            }

            $pages[] = '     <li><a href="{root_doc}admin/page/confirmpdelete/' . $v['pid'] . '"><i class="icon-white icon-remove"></i> remove</a></li> 
                    </ul></td>
                    </div>
                </tr>
            ';
        }
        $html = str_replace('{pages}', implode(PHP_EOL, $pages), $html);
        \Html::set('{admin_content}', $html);

        \Html::set('{status}');
    }
    
    public static function format_url($url){
        $url = trim($url);
        $filter = array(" ", "'", "_", '"', '/', '$', '#', '/', '\'','.', '--');
        foreach($filter as $v){
           $url = str_replace($v, '-', $url); 
        }
        return $url;
    }

}
