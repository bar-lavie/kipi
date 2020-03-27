<?php
session_start();

use app\DB;

include 'functions.php';

$pdo = DB::connect();
if (isset($_POST['submit']) && isset($_POST['password']) && !empty($_POST['password'])) {
    $is_new_user = isset($_POST['new-user']) && !empty($_POST['new-user']);
    $password = !empty($_POST['password']) ? trim($_POST['password']) : '';
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if ($is_new_user) {
        $sql = "INSERT INTO user VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $data = array(password_hash($password, PASSWORD_DEFAULT));
        $stmt->execute($data);
        $_SESSION['user_verify'] = true;
        header('location: index.php');
    } else {
        $res = $pdo->query("SELECT * FROM user")->fetch();
        $checkp = password_verify($password, $res);

        if ($checkp) {
            $_SESSION['user_verify'] = true;
            header('location: index.php');
        } else {
            $_SESSION['errors'][] = 'Are you sure is that your password?';
        }
    }
}
