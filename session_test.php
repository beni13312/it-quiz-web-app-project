<?php

require_once("session_db/session_db.php");

global $_SESSION_DB;
$_SESSION_DB->session_db_start();

$test_array = ['test1', 'test2', 'test3'];

foreach ($test_array as $test) {
    $_SESSION_DB['xyz'] .= $test.';';
}
// unset($_SESSION_DB['xyz']);

echo $_SESSION_DB['xyz'];