<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die(json_encode(["request_error" => "Hiba: rossz kérési protokol!"]));
}

require_once("../../../session_db/session_db.php");

global $_SESSION_DB;
$_SESSION_DB->session_db_start();


require_once('../../../conn/conn.php');
global $conn;


$category_id = $_SESSION_DB['category'] ?? die(json_encode(['error' => 'Error: null - category id']));


$response = array();


$usr_score = $_SESSION_DB['good_ans_score'] ?? die(json_encode(["error" => "Nem található pont"]));

$sql = "SELECT COUNT(megoldasok.id) AS 'ossz_megoldas' FROM feladat -- összes megoldás megszámolása, ami egy kategóriában van 
    JOIN feladat_megoldas ON feladat_megoldas.feladat_id = feladat.id
    JOIN megoldasok ON megoldasok.id = feladat_megoldas.megoldas_id
    JOIN kategoria ON kategoria.id = feladat.kat_id
    WHERE kategoria.id = ?";

$query = $conn->prepare($sql);
$query->bind_param('i', $category_id);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();

if($result->num_rows === 0){
    $response['error'] = "Hiba SQL query";
    echo json_encode($response);
    exit;
}else{
    $response['percentage'] = ((int)$usr_score/(int)$row['ossz_megoldas'])*100; // százalék kiszámítása
    /* session törlése */
    unset($_SESSION_DB['good_ans_score']);
    if(isset($_SESSION_DB['seen_feladat'])){
        unset($_SESSION_DB['seen_feladat']);
    }
    if(isset($_SESSION_DB['all_checked_answers'])){
        unset($_SESSION_DB['all_checked_answers']);
    }
    echo json_encode($response);
    exit;
}