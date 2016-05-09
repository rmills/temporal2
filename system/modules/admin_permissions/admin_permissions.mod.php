<?php

namespace Module;
class Admin_permissions extends Module{
    private static $_pagemode = false;
    public static $_isrestricted = true;
    private static $_status = false;
    private static $_action_complete = false;
    
    public static function __registar_callback() {
        if (\CMS::allowed()) {
            if (\CMS::$_vars[0] == 'admin' && \CMS::$_vars[1] == 'permissions') {
                \CMS::callstack_add('create', DEFAULT_CALLBACK_CREATE);
                \CMS::callstack_add('parse', DEFAULT_CALLBACK_PARSE);
                \CMS::callstack_add('set_nav', DEFAULT_CALLBACK_CREATE);
            } else {
                \CMS::callstack_add('set_nav', DEFAULT_CALLBACK_CREATE);
            }
            $blocks = array();
            $blocks[] = new \Module\AdminHomeBlock('Add Group', '/admin/permissions/add', 'fa-users');
            $blocks[] = new \Module\AdminHomeBlock('List Groups', '/admin/permissions/list', 'fa-list');
            \Module\Admin_home_blocks::add('Permissions', $blocks, 1200);
        }
    }

    public static function set_nav() {
        \Page\Admin::add_link('
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Permissions<b class="caret"></b></a>
                <ul class="dropdown-menu">
                    <li><a href="{root_doc}admin/permissions/add">Add Group</a></li>
                    <li><a href="{root_doc}admin/permissions/">List Groups</a></li>
                </ul>
            </li>
        ');
    }

    public static function create() {
        \Page\Admin::$_subpage = true;
    }

    public static function parse() {
        /* Pre Action Handlers */
        if (\CMS::$_vars[3] == 'submit') {
            switch (\CMS::$_vars[2]) {
                case 'add':
                    self::add_submit();
                    break;
                
                case 'edit':
                    self::edit_submit();
                    break;
            }
        }


        /* Page Content Handlers */
        switch (\CMS::$_vars[2]) {
            case 'add':
                self::$_pagemode = 'add';
                self::add();
                break;
            
            case 'edit':
                self::$_pagemode = 'edit';
                self::edit();
                break;
            
            case 'confirmdelete':
                self::$_pagemode = 'confirmdelete';
                self::confirmdelete();
                break;
            
            case 'delete':
                self::$_pagemode = 'delete';
                self::delete();
                break;
            
            
            case 'list':
                self::$_pagemode = 'listall';
                self::listall();
                break;
            
            default:
               self::$_pagemode = 'listall';
               self::listall();     
        }

        if (self::$_status) {
            \Html::set('{status}', self::$_status);
        }else{
            \Html::set('{status}', '');
        }
    }
    
    
    public static function add(){
        \Html::set('{admin_content}', self::block('add.html'));
        foreach(\CMS::$__modules as $v){
            $classname = '\Module\\'.ucfirst($v[1]);
            if($classname::$_isrestricted){
                \Html::set('{modules}', '<label class="checkbox"><input type="checkbox" name="'.self::format_name($v).'" value="add">'.self::format_name($v).'</label>');
            }
        }
        
        foreach(\CMS::$__pages as $v){
            $classname = '\Page\\'.ucfirst($v[1]);
            if($classname::$_isrestricted){
                \Html::set('{modules}', '<label class="checkbox"><input type="checkbox" name="'.self::format_name($v).'" value="add">'.self::format_name($v).'</label>');
            }
        }
        
        foreach(\CMS::$__appserve as $v){
            $classname = '\Appserve\\'.ucfirst($v[1]);
            if($classname::$_isrestricted){
                \Html::set('{modules}', '<label class="checkbox"><input type="checkbox" name="'.self::format_name($v).'" value="add">'.self::format_name($v).'</label>');
            }
        }
        
        \Html::set('{modules}');
        \Html::set('{setname}');
    }
    
    public static function edit(){
        if(is_numeric(\CMS::$_vars[3])){
            \Html::set('{admin_content}', self::block('edit.html'));
            $sql = 'SELECT * FROM `groups` WHERE `id` = '.\DB::clean(\CMS::$_vars[3]).' LIMIT 1';
            $items = array();
            $q = \DB::q($sql);
            foreach ($q as $v) {
                $items = explode(',', $v['modules']);
                \Html::set('{setname}', $v['name']);
                \Html::set('{id}', $v['id']);
            }

            foreach(\CMS::$__modules as $v){
                $check = false;
                $classname = '\Module\\'.ucfirst($v[1]);
                if($classname::$_isrestricted){
                    foreach($items as $v2){
                        $name = self::format_name($v);
                        if($v2 == $name){
                            $check = 'checked="yes"';
                        }
                    }
                    \Html::set('{modules}', '<label class="checkbox"><input type="checkbox" name="'.$name.'" value="add" '.$check.' >'.$name.'</label>');
                }
            }
            foreach(\CMS::$__pages as $v){
                $check = false;
                $classname = '\Page\\'.ucfirst($v[1]);
                if($classname::$_isrestricted){
                    foreach($items as $v2){
                        $name = self::format_name($v);
                        if($v2 == $name){
                             $check = 'checked="yes"';
                        }
                    }
                    \Html::set('{modules}', '<label class="checkbox"><input type="checkbox" name="'.$name.'" value="add" '.$check.' >'.$name.'</label>');
                }
            }
            
            foreach(\CMS::$__appserve as $v){
                $check = false;
                $classname = '\Appserve\\'.ucfirst($v[1]);
                if($classname::$_isrestricted){
                    foreach($items as $v2){
                        $name = self::format_name($v);
                        if($v2 == $name){
                             $check = 'checked="yes"';
                        }
                    }
                    \Html::set('{modules}', '<label class="checkbox"><input type="checkbox" name="'.$name.'" value="add" '.$check.' >'.$name.'</label>');
                }
            }
            \Html::set('{modules}');
        }else{
           \Html::set('{admin_content}', self::block('updated.html'));
        }
    }
    
    public static function confirmdelete(){
        if(is_numeric(\CMS::$_vars[3])){
            $id = \CMS::$_vars[3];
            $sql = 'SELECT * FROM `groups` WHERE `id` = '.\DB::clean($id).' LIMIT 1';
            $q = \DB::q($sql);
            foreach ($q as $v) {
                $name = $v['name'];
            }
            \Html::set('{admin_content}', self::block('confirmdelete.html'));
            \Html::set('{name}', $name);
            \Html::set('{id}', $id);
        }
    }
    
    public static function delete(){
        \Html::set('{admin_content}', self::block('delete.html'));
        $id = \CMS::$_vars[3];
        if ($id !== '1' && $id !== '2' ) {
            $sql = 'DELETE FROM `groups` WHERE `id` = \'' . \DB::clean($id) . '\' LIMIT 1';
            \DB::q($sql);

            $html = Notice::success('Group Deleted').'<div class="form-actions"><a class="btn btn-info" href="{root_doc}admin/permissions/">Return</a></div>';
            \Html::set('{admin_content}', $html);
        } else {
            $html = '<h4>Failed</h4><hr /><p>Default "public" and "loggedin" permissions can not be removed, you can only edit them.</p><div class="form-actions"><a class="btn btn-info" href="{root_doc}admin/permissions/">Return</a></div>';
            \Html::set('{admin_content}', $html);
        }
    }
    
    public static function listall(){
        \Html::set('{admin_content}', self::block('list.html'));
        $sql = 'SELECT * FROM `groups`';
        $items = array();
        $q = \DB::q($sql);
        foreach ($q as $v) {
            $items[] = '<tr><td>'.$v['id'].'</td><td>'.$v['name'].'</td><td>'.$v['modules'].'</td><td><a class="btn btn-success" href="{root_doc}admin/permissions/edit/' . $v['id'] . '"><i class="icon-edit icon-white"></i> edit</a> <a class="btn btn-danger" href="{root_doc}admin/permissions/confirmdelete/' . $v['id'] . '"><i class="icon-white icon-remove"></i> delete</a></tr>';
        }
        \Html::set('{list}', implode(PHP_EOL, $items));
    }
    
    public static function add_submit(){
        $modules = false;
        $name = $_POST['name'];
        $first = true;
        foreach($_POST as $k=>$v){
            if($v == 'add'){
                if($first){
                    $first = false;
                    $modules .= $k;
                }else{
                    $modules .= ','.$k;
                }
            }
        }
        $sql = '
            INSERT INTO groups (
                `name`,
                `modules`
            ) VALUES (
                \'' . \DB::clean($name) . '\',
                \'' . \DB::clean($modules) . '\'
            )';
        \DB::q($sql);
        self::$_status = Notice::success('Group Added');
    }
    
    public static function edit_submit(){
        $modules = false;
        $name = $_POST['name'];
        $first = true;
        foreach($_POST as $k=>$v){
            if($v == 'add'){
                if($first){
                    $first = false;
                    $modules .= $k;
                }else{
                    $modules .= ','.$k;
                }
            }
        }
        $sql = 'UPDATE `groups` SET 
            `name` = \'' . \DB::clean($name) . '\',
            `modules` = \'' . \DB::clean($modules) . '\'
            WHERE `id` = \'' . \DB::clean($_POST['id']) . '\' LIMIT 1';
        \DB::q($sql);
        self::$_status = Notice::success('Group Updated');
    }
    
    private static function format_name($array){
        $name = explode('.', $array[0]);
        switch($name[1]){
            case 'mod':
                $name = 'module';
                break;
            default:
                $name =  $name[1];
        }
        return \strtolower($name).'\\'.strtolower($array[1]);
    }
}