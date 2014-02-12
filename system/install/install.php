<?php

if(!defined('INSTALL_SNAPSHOT')){
    define('INSTALL_SNAPSHOT', 'system/install/sql/install.sql.php');
}

if(!defined('ALLOW_INSTALL')){
    exit('not enabled');
}

$template = file_get_contents('system/install/templates/index.html');
//$install_snapshot = file_get_contents(INSTALL_SNAPSHOT);
$notice = array();
$install_ok = false;

if(ALLOW_INSTALL){
    
    include('classes/installer.class.php');
    
    $check = Installer::checkdb();
    if($check){
        $install_ok = true;
        $template = str_replace('{db_status}', '<div class="alert alert-success">DB Status: Connected To DB </div>', $template);
    }else{
        $install_ok = false;
        if(Installer::$db_error != ''){
            $template = str_replace('{db_status}', '<div class="alert alert-error">DB Status: Could not connect to the DB, Error: '.Installer::$db_error.'</div><div class="alert alert-error">You must correct the errors above before you can install.</div>', $template);
        }else{
            $template = str_replace('{db_status}', '<div class="alert alert-error">DB Status: Could not connect to the DB, Error: None provided (is the host address correct?)</div><div class="alert alert-error">You must correct the errors above before you can install.</div>', $template);
        }
    }
    
    if(isset($_POST['install'])){
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        if($name == '' || $email == '' || $password == '' ){
            $notice[] = '<div class="warn"><p>Name, Email and Password are required</p></div>';
        }else{
            $db_install_data = Installer::init_db(INSTALL_SNAPSHOT);
            $notice[] = '<div class="alert alert-success"><p>If the magical powers of the terminal worked your database should not be installed. You can read the output below.</p></div>';
            $try_user = Installer::init_user($name, $email, $password);
            if(!$try_user){
                $notice[] = '<div class="alert alert-success"><p>The user with the email address "'.$email.'" has been created as a super user with the password "'.$password.'"</p></div>';
            }else{
                $notice[] = '<div class="alert alert-error"><p>Could not create user, Error: '.$try_user.'</p></div>';
            }
            $notice[] = '<p>&nbsp;</p><div id="showdb"><a>View db return data</a></div><div id="dbout" style="display:none"><h4>DB Install Return</h4>'.$db_install_data.'</div><p>&nbsp;</p>';
        }
        $load = 'success.html';
    }else{
        $load = 'form.html';
    }
    $template = str_replace('{content}', file_get_contents('system/install/templates/'.$load), $template);
    foreach($notice as $v){
        $template = str_replace('{notice}', $v.'{notice}', $template);
    }
    $template = str_replace('{notice}', '', $template);
    
    if($install_ok){
        $template = str_replace('{hideform}', '', $template);
    }else{
        $template = str_replace('{hideform}', 'display: none;', $template);
    }
    
    $template = str_replace('{dbhost}', DATABASE_HOST, $template);
    $template = str_replace('{dbuser}', DATABASE_USER, $template);
    $template = str_replace('{dbpass}', DATABASE_PASSWORD, $template);
    $template = str_replace('{dbtable}', DATABASE_TABLE, $template);
    
    $form = file_get_contents('system/install/templates/form.html');
    $template = str_replace('{content}', $form, $template);
    echo $template;
}