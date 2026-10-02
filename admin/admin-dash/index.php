<?php

require_once("../../session_db/session_db.php");

global $_SESSION_DB;

if ($_SESSION_DB->session_db_status() === false) {
    $_SESSION_DB->session_db_start();
}
if(!isset($_SESSION_DB['authorized']) && !isset($_SESSION_DB['auth_id'])){
    header('Location: /admin');
    exit;
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../../public/style/admin/admin_dash.css">
    <link rel="stylesheet" href="../../public/style/modules/load_spin.css">
    <link rel="stylesheet" href="../../public/style/admin/admin_quiz_upload.css">
    <link rel="stylesheet" href="../../public/style/admin/admin_quiz_manage.css">
    <link rel="stylesheet" href="../../public/style/admin/admin_security.css">
    <script src="../../public/script/admin/admin_dash.js" type="module"></script>
    <title>Admin felület</title>
</head>
<body>
<?php require "../../header/header.php"?>
<?php require_once "../../conn/conn.php"; global $conn; ?>
<div class="body">
    <div id="admin-flexbox">
        <h1 id="admin-flexbox-title">Admin felület</h1>

        <div id="admin-flexbox-menu">
            <input type="button" value="Quiz feltöltése" class="admin-flexbox-menu-b" name="admin_quiz_upload.php" id="quiz-upload">
            <input type="button" value="Quizek kezelése" class="admin-flexbox-menu-b" name="admin_quiz_manage.php" id="quiz-manage">
            <input type="button" value="Biztonság" class="admin-flexbox-menu-b" name="admin_security.php" id="security">

        </div>

        <div id="admin-logout">
            <form method="POST" action="/actions/logout.php">
                <input type="submit" name="submit" value="Kijelentkezés" id="admin-submit-logout">
            </form>
        </div>
    </div>
    <div id="admin-dashboard">
    <div id="admin-menu-load">
        <?php 
        if(!isset($_GET['site'])){

            include_once("admin_quiz_upload.php"); // alapértelmezett nézet
        }else{
            if(file_exists(str_replace(['/','.', '~'],'_',$_GET['site']).".php")){ // remove any sensitive characters that allow access to unwanted files
                include_once($_GET['site'].".php");
            }else{
                echo("Az oldal nem található");
            }

        }
        ?>

    </div>
    </div>
    <script>
        function updateLogoutPlace() {
            const logoutContainer = document.getElementById("admin-logout");
            const logoutContainerInputB = document.querySelector("#admin-logout input[type='submit']");
            const adminFlexBoxMenu = document.getElementById("admin-flexbox-menu");
            const adminFlexBox = document.getElementById("admin-flexbox");

            const isSmallWith = window.innerWidth < 664; // ha kisebb mint 664 px
            const logoutIsInMenu = adminFlexBoxMenu.contains(logoutContainer);

            if (isSmallWith && !logoutIsInMenu) {
                logoutContainerInputB.classList.add("admin-flexbox-menu-b");
                logoutContainerInputB.id = ""; // id eltávolítása a stílus miatt
                adminFlexBoxMenu.appendChild(logoutContainer);
            } else if (!isSmallWith && logoutIsInMenu) {
                logoutContainerInputB.classList.remove("admin-flexbox-menu-b");
                logoutContainerInputB.id = "admin-submit-logout";
                adminFlexBox.appendChild(logoutContainer);
            }
        }


        document.addEventListener("DOMContentLoaded", updateLogoutPlace);

        window.addEventListener("resize", updateLogoutPlace);
    </script>
</div>
<?php require "../../footer/footer.php"?>
</body>
</html>
