<?php
session_start();
use app\DB;

include 'functions.php';







// login attempt check
$_SESSION['errors'] = [];
$max_login_attempts = 4;

$pdo = DB::connect();


if (isset($_POST['submit']) && isset($_POST['signup'])) {
    $email = !empty($_POST['email']) ? trim($_POST['email']) : '';
    $password = !empty($_POST['password']) ? trim($_POST['password']) : '';
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":email" => $email));
    $user = $stmt->fetch();
    if ($user['email']) {
        $_SESSION['email'] = $email;
        $_SESSION['errors'][] = 'Email is already taken!';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        $sql = "INSERT INTO users VALUES ('', ? ,?)";
        $stmt = $pdo->prepare($sql);
        $data = [$email, password_hash($password, PASSWORD_DEFAULT)];
        $stmt->execute($data);
        $last_id = $pdo->lastInsertId();
        $sql = "INSERT INTO accounts VALUES ('', $last_id ,'')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $_SESSION['user'] = $email;
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['lifetime'] = time() + (30*60);

        header('location: index.php');
    }
}




$sql = "SELECT * FROM login_attempts WHERE ip = :ip";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":ip" => $_SERVER['REMOTE_ADDR']));
$login_attempts = $stmt->fetch();


is_null($login_attempts['attempts']) ? $attempts = 0 : $attempts = intval($login_attempts['attempts']);

if (isset($_POST['submit']) && $attempts !== $max_login_attempts) {
    if (!empty($_POST['email']) || !empty($_POST['password'])) {
        if (isset($_POST['token']) && isset($_SESSION['token']) && $_POST['token'] == $_SESSION['token']) {
            if ($attempts === 0) {
                $attempts++;
                $sql = "INSERT INTO login_attempts VALUES ('', ? ,NOW(), ?)";
                $stmt = $pdo->prepare($sql);
                $data = [$_SERVER['REMOTE_ADDR'], $attempts];
                $stmt->execute($data);
            } else {
                $attempts++;
                $sql = "UPDATE login_attempts SET attempts = ?, time = NOW() WHERE ip = ?";
                $stmt = $pdo->prepare($sql);
                $data = [$attempts, $_SERVER['REMOTE_ADDR']];
                $stmt->execute($data);
            }

            $email = !empty($_POST['email']) ? trim($_POST['email']) : '';
            $password = !empty($_POST['password']) ? trim($_POST['password']) : '';
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(":email" => $email));
            $user = $stmt->fetch();
            // email & password validation
            if ($user['email']) {
                $checkp = password_verify($password, $user['password']);
                if ($checkp) {
                    $_SESSION['user'] = $user['email'];
                    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                    $_SESSION['lifetime'] = time() + (30*60);
                    $sql = "DELETE FROM login_attempts WHERE ip = :ip";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array(":ip" => $_SERVER['REMOTE_ADDR']));
                    header('location: index.php');
                } else {// if password is wrong
                    $_SESSION['email'] = $email;
                    $_SESSION['errors'][] = 'Are you sure is that your password?';
                }
            } else {// if email is wrong
                $_SESSION['email'] = $email;
                $_SESSION['errors'][] = 'Are you sure is that your mail?';
            }
        } else {// if tokens not match
            $_SESSION['email'] = $email;
            $_SESSION['errors'][] = 'There is a problem to valid you please try again';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    } else {// if not email / password
        $_SESSION['email'] = $email;
        $_SESSION['errors'][] = 'Please provide valid Email & Password';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}


    if ($attempts && $attempts < $max_login_attempts) {
        $_SESSION['errors'][] = $max_login_attempts - $attempts . ' attempts remaining';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

    if ($attempts === $max_login_attempts) {
        $releasetime = strtotime(date('Y-m-d H:i:00', strtotime($login_attempts['time']))) + 10 * 60;
        $now = strtotime(date('Y-m-d H:i:00')) + 60 * 60;
        $diff = $releasetime - $now;
        $release = intval(date('i', $diff));

        $_SESSION['errors'] = [];
        $_SESSION['errors'][] =  "You are blocked,<br>try again in <b> $release </b> minutes";

        if ($now >= $releasetime) {
            $sql = "DELETE FROM login_attempts WHERE ip = :ip";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(":ip" => $_SERVER['REMOTE_ADDR']));
            $_SESSION['errors'] = [];
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }


// DB::disconnect();
