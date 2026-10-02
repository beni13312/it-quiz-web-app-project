<?php
require_once __DIR__ . '/session_db/session_db.php';

global $_SESSION_DB;
$_SESSION_DB->session_db_start();

/* Előző session törlése */
if(isset($_SESSION_DB['feladat'])){ // elöző feladat id eltávolítása session-ből
  unset($_SESSION_DB['feladat']);
}
if(isset($_SESSION_DB['good_ans_score'])){
    unset($_SESSION_DB['good_ans_score']);
}
if(isset($_SESSION_DB['seen_feladat'])){
    unset($_SESSION_DB['seen_feladat']);
}
if(isset($_SESSION_DB['all_checked_answers'])){
    unset($_SESSION_DB['all_checked_answers']);
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="public/style/index.css">
    <script src="public/script/script.js" type="text/javascript"></script>
    <title>Informatikai Quizek</title>
</head>
<body>
    <?php require "header/header.php"?>
    <div class="body">
    <h1 id="cat-title">Quiz kategóriák</h1>

    <div id="category">
<!--        <img id="htmlpic" class="html2" src="pic/html2.png" alt="" srcset="">-->
        <?php
        require_once("conn/conn.php");
        global $conn;

        $sql = "SELECT id, kategoria FROM kategoria ORDER BY id";
        $query = mysqli_query($conn, $sql);

        if (mysqli_num_rows($query) > 0) {
            while ($row = mysqli_fetch_assoc($query)) {
                echo '<a class="category-element-a" href="quiz/?cat='.$row['id'].'">
                <div class="category-element" id="cat-'.$row['id'].'">'.$row['kategoria'].'</div>
                </a>';
            }
        }
            ?>
    </div>
    </div>
    <?php require "footer/footer.php"?>
</body>
</html>