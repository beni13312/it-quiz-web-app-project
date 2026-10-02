<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../../public/style/admin/admin_login.css">
    <script src="../../public/script/script.js" type="text/javascript"></script>
    <title>Feladatbank</title>
</head>
<body>
<?php require "../../header/header.php"?>
<div class="body" id="admin-body">
    <form method="POST" action="../../actions/login.php" id="login-form">
        <h2 id="admin-title">Admin felület</h2>
        <label for="admin-uname"></label><input type="text" name="username" id="admin-uname" placeholder="Felhasználónév">
        <label for="admin-password"></label><input type="password" name="password" id="admin-password" placeholder="Jelszó">
        <input type="submit" name="submit" id="admin-submit" value="Bejelentkezés">

        <div class="error-message">
            <?php
            require_once("../../session_db/session_db.php");

            global $_SESSION_DB;
            $_SESSION_DB->session_db_start();


            if(isset($_SESSION_DB['error'])){
                echo $_SESSION_DB['error'];
                unset($_SESSION_DB['error']);
            }
            ?>
        </div>
    </form>
</div>
<?php require "../../footer/footer.php"?>

</body>
</html>