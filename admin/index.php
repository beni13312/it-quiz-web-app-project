<?php
require_once("../session_db/session_db.php");

global $_SESSION_DB;
$_SESSION_DB->session_db_start();

if(isset($_SESSION_DB['authorized']) && isset($_SESSION_DB['auth_id'])){
    header('Location: /admin/admin-dash');
}else{
    header('Location: /admin/admin-login');
}
