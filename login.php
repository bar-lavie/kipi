<?php
session_start();

use app\DB;

include 'functions.php';

if (verify_user()) {
    header('location: index.php');
    exit;
}

$new_user = false;

if (!DB::db_exists()) {
    $new_user = true;
    DB::create_db();
}
if (!DB::user_exists()) {
    $new_user = true;
}


include 'views/header.php';
?>
<div id="login" class="login-wrap d-flex justify-content-center align-items-center">

    <div class="login">

        <div class="logo my-5 text-center">
            <h1>Kipi</h1>
            <p class="mb-0">
                <b>Password Keeper</b>
            </p>
        </div>

        <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) : ?>
            <div class="error-box">
                <?php foreach ($_SESSION['errors'] as $error) : ?>
                    <p class="error mb-0">
                        <?= $error ?>
                    </p>
                <?php endforeach ?>
            </div>
        <?php endif; ?>

        <form autocomplete="off" action="post_login_v2.php" method="POST" class="w-100 mw-100">
            <input type="hidden" name="token" value="<?= create_token() ?>">
    
            <div class="form-group w-100 mw-100">
                <label for="password" class="d-none">Password</label>
                <input type="password" class="" name="password" value="" placeholder="Password" autocomplete="off">
            </div>

            <?php if ($new_user) : ?>
                <div class="form-group w-100 mw-100">
                    <label for="password-confirm" class="d-none">Password confirm</label>
                    <input type="password" class="" name="password-confirm" value="" placeholder="Password confirm" autocomplete="off">
                </div>
                <input type="hidden" name="new-user" value="true">
            <?php endif; ?>
            <input type="submit" class="text-uppercase btn btn-success w-100 mw-100" name="submit">

        </form>

    </div>
</div>




<?php
include 'views/footer.php';
?>