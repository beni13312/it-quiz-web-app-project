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
<?php require_once "../../conn/conn.php"; global $conn; ?>

<div id="admindash-security">
    <h2 id="admindash-security-title">Biztonsági beállítások</h2>
    <div id="admindash-manage-account">
        <input type="password" id="admindash-password-current" placeholder="Jelenlegi jelszó" name="current_password"><br>
        <input type="password" id="admindash-password-new" placeholder="Új jelszó" name="new_password"><label for="admindash-password-new">Minimum 8 karakter speciális karakterekkel</label><br>
        <div id="admindash-password-msg"></div>
        <input type="password" id="admindash-password-re-new" placeholder="Új jelszó mégegszer"><br>
        <input type="button" value="Módosítás" name="submit_password" id="admindash-password-submit">
        <div id="admindash-password-submit-msg"></div>
    </div>


</div>