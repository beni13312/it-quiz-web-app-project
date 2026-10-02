<?php
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die("Hiba: rossz kérési protokol!");
}

require_once("../session_db/session_db.php");

global $_SESSION_DB;
$_SESSION_DB->session_db_start();



$username = $_POST['username'];
$password = $_POST['password'];


if(empty($username) || empty($password)){
    $_SESSION_DB['error'] = "Felhasználónév és a jelszó nem lehet üres!";
    header('Location: /admin/admin-login');
    exit;
}


require_once("../conn/conn.php");
global $conn;
// lekérdezés, prepare() - sql nem megfelelő adatok kiszűrése, pl.: SQL parancsok

$sql = "SELECT jelszo FROM admin_felhasznalok WHERE fnev= ?";
$query = $conn->prepare($sql);
$query->bind_param("s", $username);
$query->execute();
$result = $query->get_result();

if($result->num_rows == 1){
    $row = $result->fetch_assoc();

    if(password_verify($password, $row['jelszo'])){
        $_SESSION_DB->session_db_regenerate_id(); // session újragenerálása
        $_SESSION_DB['authorized'] = $username;
        $_SESSION_DB['auth_id'] = hash('sha256', uniqid(mt_rand(), true)); // bejelentkezési session id random azonosító

        // auth cookie
        setcookie("auth_id", $_SESSION_DB['auth_id'], [
            'secure' => true,
            'httponly' => false, // JavaScript elérhetőség miatt false, a JS-nek vissza kell tudnia küldeni a generált tokent
            'samesite' => 'Strict',
            'path' => '/admin',
        ]);

        header('Location: /admin/admin-dash');
        exit;
    }else{
        $_SESSION_DB['error'] = "Felhasználónév vagy a jelszó helytelen!";
        header('Location: /admin/admin-login');
        exit;
    }
}else{
    $_SESSION_DB['error'] = "Felhasználónév vagy a jelszó helytelen!";
    header('Location: /admin/admin-login');
    exit;
}

