<?php
require_once("../session_db/session_db.php");

global $_SESSION_DB;
$_SESSION_DB->session_db_start();

if(isset($_GET["cat"]) && ctype_digit($_GET["cat"])){ // ha integer, egész szám
    $_SESSION_DB['category'] = $_GET["cat"];
}else{
    header('location: /'); // home
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="../public/style/quiz.css">
    <script src="../public/script/quiz.js" type="module"></script>
    <title id="documentTitle">Quiz</title>
</head>
<body>
<?php require "../header/header.php"?>
<div class="body">
    <div id="quiz-feladat-frame">
        <div id="quiz-feladat" hidden="hidden">

            <div class="progress-bar-container">
                <div class="progress-bar" style="width: 0%;"></div>
            </div>

            <h1 id="quiz-cat-title">

            </h1>
            <div id="quiz-kerdes">

            </div>

            <div id="quiz-szamlalo">

            </div>

            <div id="quiz-answers">

            </div>
            <div id="quiz-next-prev">
                <input id="quiz-check" type="button" name="quiz-submit" value="Ellenőrzés">


                <div id="quiz-next-previous">
                    <div id="quiz-previous-container">

                </div>
                <div id="quiz-next-container">
                    <input id="quiz-next" type="button" name="quiz-advance" value="Tovább">
                </div>
            </div>
            </div>

        </div>
    </div>

</div>


<?php //require "../footer/footer.php"?>

</body>
</html>