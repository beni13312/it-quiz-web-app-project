<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die("Hiba: rossz kérési protokol!");
}

require_once("../session_db/session_db.php");

global $_SESSION_DB;
$_SESSION_DB->session_db_start();

if(isset($_SESSION_DB['authorized'])){
    unset($_SESSION_DB['authorized']);
    unset($_SESSION_DB['auth_id']);
    unset($_COOKIE['auth_id']);
    header('Location: /admin');
    exit;
}