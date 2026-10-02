<?php
header('Content-Type: application/json');
//if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
//    die(json_encode(["request_error" => "Hiba: rossz kérési protokol!"]));
//}

require_once("../../../../session_db/session_db.php");

global $_SESSION_DB;
$_SESSION_DB->session_db_start();

require_once('../../../../conn/conn.php');
global $conn;

$headers = getallheaders();
$auth_id = $headers['X-Auth-Token'] ?? null;

if(!isset($_SESSION_DB['authorized']) || $_SESSION_DB['authorized'] != true) { // jog ellenörzése
    http_response_code(401);
    $response["error"] = "Unauthorized session!";
    echo json_encode($response);
    exit;
}

if (!isset($_SESSION_DB['auth_id']) || !$auth_id || !hash_equals($_SESSION_DB['auth_id'], $auth_id)) { // auth id ellenörzése
    http_response_code(403);
    echo json_encode(["error" => "Invalid auth token"]);
    exit;
}


$input = json_decode(file_get_contents("php://input"), true);
$destroy = $input["destroy"] ?? false;
$regenerate = $input["regenerate"] ?? false;

$response = array();


$sql = "SELECT session_db.expires_at FROM session_db
WHERE session_id = ? AND TIMESTAMPDIFF(SECOND, NOW(), DATE_SUB(expires_at, INTERVAL 20 MINUTE)) <= 0"; // ellenőrzi, hogy a sessionlejárati idejéből eltelt-e meghatározott idő

$query = $conn->prepare($sql);
$query->bind_param('s',$_COOKIE["usr_id"]); // session cookie
$query->execute();
$result = $query->get_result();

if($result->num_rows === 1){ // ha létezik ilyen admin session
    $response['active'] = false;
    if($destroy){ // törli a sessiont
        $_SESSION_DB->session_db_destroy();
    }
    if($regenerate){ // újragenerálja a sessiont
        $_SESSION_DB->session_db_regenerate_id();
    }
}else{
    $response['active'] = true;
}


//if(!isset($_SESSION_DB['authorized']) && !isset($_SESSION_DB['auth_id'])){
//    $response['active'] = false;
//}else{
//    $response['active'] = true;
//}
echo json_encode($response);
exit;