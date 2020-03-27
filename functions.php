<?php

use app\CRUD;

spl_autoload_register(function ($class) {
    $filename = str_replace("\\", "/", $class);
    require_once("{$filename}.php");
});



// $BASE_URL = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . 'epiclients/';



if (!function_exists('old')) {
    function old($field_name)
    {
        return isset($_REQUEST[$field_name]) ? $_REQUEST[$field_name] : '';
    }
}

if (!function_exists('create_token')) {
    function create_token()
    {
        $token = sha1(rand(1, 1000) . date('Y.m.d.H.i.s') . 'token');
        $_SESSION['token'] = $token;
        return $token;
    }
}


if (!function_exists('verify_user')) {
    function verify_user()
    {
        $is_user = false;
        if (isset($_SESSION['user_ip']) &&  $_SESSION['user_ip'] == $_SERVER['REMOTE_ADDR']) {
            if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] == $_SERVER['HTTP_USER_AGENT']) {
                if (isset($_SESSION['lifetime']) && ($_SESSION['lifetime'] > time())) {
                    // if (isset($_SESSION['user_id'])) {
                    $_SESSION['lifetime'] = time() + (30 * 60);
                    $is_user = true;
                    //  }
                } else {
                    session_unset();
                    session_destroy();
                }
            }
        }

        return $is_user;
    }
}
// old admin verify ?
//$x = password_verify($_SESSION['admin'], $_SERVER['REMOTE_ADDR']);

// echo $_SESSION['admin'];


// if(!isset($_SESSION['admin']) ||  password_verify($_SESSION['admin'], $_SERVER['REMOTE_ADDR'])){
//     header("location: login.php");
//     exit();
// }




if (isset($_POST['action'])) {
    $action = $_POST['action'];
    CRUD::$action();
}

if (!function_exists('debug')) {

    function debug($obj)
    {
        echo "<pre dir='lrt' style='text-align:left'>";
        var_dump($obj);
        echo "</pre>";
        die();
    }
}
