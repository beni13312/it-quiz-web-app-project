<?php
header('Content-Type: application/json');
//if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
//    die(json_encode(["request_error" => "Hiba: rossz kérési protokol!"]));
//}

require_once("../../../session_db/session_db.php");

global $_SESSION_DB;
$_SESSION_DB->session_db_start();


require_once('../../../conn/conn.php');
global $conn;


$feladat_id = $_SESSION_DB['feladat'] ?? die(json_encode(['error' => 'Error: null - feladat_id']));

$usr_answers = array_map('intval', $_POST['answers'] ?? []); // user bejelölt válaszai

$response = array();


$sql_check = "SELECT valaszok.id AS 'good_ans_id'
            FROM feladat
            JOIN feladat_megoldas ON feladat.id = feladat_megoldas.feladat_id
            JOIN megoldasok ON feladat_megoldas.megoldas_id = megoldasok.id
            JOIN feladat_valasz on feladat.id = feladat_valasz.feladat_id
            JOIN valaszok ON feladat_valasz.valasz_id = valaszok.id
            WHERE feladat.id = ? AND 
                  megoldasok.megoldas = valaszok.valasz"; // jó válaszok kiolvasása, valaszok id

$query = $conn->prepare($sql_check);
$query->bind_param("i", $feladat_id);
$query->execute();
$result = $query->get_result();
$result_array = array();


while($row = $result->fetch_assoc()){
    $response['megoldas'][] = $row['good_ans_id']; // helyes id-k hozzáadása tömbhöz
    $result_array[] = $row;
}
if(isset($_SESSION_DB['all_checked_answers'])){
    $answers = array_map('intval', explode(';', $_SESSION_DB['all_checked_answers'])); // egy kategóriában a user által bejelölt válaszok
    $response['answers'] = $answers;

}



if(!empty($usr_answers)){ // ha a user bejelölt minimum egy választ, válaszok ellenőrzése
//        $query = $conn->prepare($sql_check);
//        $query->bind_param("i", $feladat_id);


        if(!in_array((string)$feladat_id, explode(';', $_SESSION_DB['seen_feladat']?? ""), true)){ // ha nem jelölt be egy választ sem a user az adott feladatnál
//            $query->execute();
//            $result = $query->get_result();

            // ha nincs benne a feladat id akkor kaphat pontot a user, task: illetve ha az user visszalép az elöző oldalra, akkor a megoldások megjelenítése, ez akkor történik, ha a user már ellenőrizte a feladatot
            foreach($result_array as $row) {
                    if (in_array($row['good_ans_id'], $usr_answers, true)) {
                        $_SESSION_DB['good_ans_score'] += 1; // ha a user eltalált egy helyes választ akkor növeljük a pontszámát
                    }
            }
            foreach ($usr_answers as $usr_answer) { // egy kategória összes bejelölt válasza, hogy meglehessen jeleníteni
                $_SESSION_DB['all_checked_answers'] .= $usr_answer . ';';
            }
            $_SESSION_DB['seen_feladat'] .= $feladat_id.';'; // aktuális feladat id hozzáadása session-höz, erre már nem kap pontot még ha vissza is lép
        }
}
unset($result);


echo json_encode($response);
exit;