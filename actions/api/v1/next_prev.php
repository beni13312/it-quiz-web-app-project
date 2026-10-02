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

$response = array();

$category = $_SESSION_DB['category'] ?? die(json_encode(["error" => "kategória nincs beállítva a session-ben!"]));
$feladat_id = $_SESSION_DB['feladat'] ?? die(json_encode(['error' => 'Error: null - feladat_id']));

$usr_answers = array_map('intval', $_POST['answers'] ?? []); // user bejelölt válaszai


//if(empty($usr_answers)) {
//    $response["error"] = "Nincs válasz bejelölve!";
//    echo json_encode($response);
//    exit;
//}
if(!isset($_SESSION_DB['good_ans_score'])) { // alapértékek definiálása
    $_SESSION_DB['good_ans_score'] = 0;
}
if(!isset($_SESSION_DB['seen_feladat'])) {
    $_SESSION_DB['seen_feladat'] = "";
}


$feladatok = array(); // feladat id tömb, a tovább vissza funkcióhoz


$sql = "SELECT feladat.id FROM feladat WHERE feladat.kat_id = ? ORDER BY feladat.id"; // feladat id lekérése, a tömb feltöltéséhez, az adott kategóriában lévő feladat id-kell
$query = $conn->prepare($sql);
$query->bind_param('i', $category);
$query->execute();
$result = $query->get_result();

if($result->num_rows === 0) {
    echo json_encode(['error' => 'Nem található feladat ebben a kategóriában']);
    exit;
}else{
    while($row = $result->fetch_assoc()) {
        $feladatok[] = $row['id']; // feladat id hozzáadása tömbhöz
    }
    $feladatok[] = "end";
}




/* következő kérdés */

if(isset($_GET['action-next'])){
    if(!empty($usr_answers)){ // ha a user bejelölt minimum egy választ, ami azt jelenti hogy a tovább gombra nyomott és bejelölt egy választ
        $sql_check = "SELECT valaszok.id AS 'good_ans_id', valaszok.valasz AS 'good_ans_name'
            FROM feladat
            JOIN feladat_megoldas ON feladat.id = feladat_megoldas.feladat_id
            JOIN megoldasok ON feladat_megoldas.megoldas_id = megoldasok.id
            JOIN feladat_valasz on feladat.id = feladat_valasz.feladat_id
            JOIN valaszok ON feladat_valasz.valasz_id = valaszok.id
            WHERE feladat.id = ? AND 
                  megoldasok.megoldas = valaszok.valasz"; // jó válaszok kiolvasása, valaszok id

        $query = $conn->prepare($sql_check);
        $query->bind_param("i", $feladat_id);


        if(!in_array((string)$feladat_id, explode(';', $_SESSION_DB['seen_feladat']), true)){
            $query->execute();
            $result = $query->get_result();

            // ha nincs benne a feladat id akkor kaphat pontot a user, task: illetve ha az user visszalép az elöző oldalra, akkor a megoldások megjelenítése, ez akkor történik, ha a user már ellenőrizte a feladatot
            while ($row = $result->fetch_assoc()) {
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


    // tovább kérés kezelése

    $current_index = array_search($_SESSION_DB['feladat'], $feladatok); // a jelenegi index megtalálása a tömbben
    if($current_index < count($feladatok) -1) { // ha nem az utolsó quiz
        $_SESSION_DB['feladat'] = $feladatok[$current_index+1]; // növeljük a sessionben a feladat id-t, a következő id re, a következő feladat érdekében
        $_SESSION_DB['feladat_index'] +=1; // növeljük a sessionben a feladat indexet, hogy megjelenjen a feladat száma az oldalon
        $response['action'] = "next";
        echo json_encode($response);
        exit;
    }else{
        if ($feladatok[$current_index] === "end"){ // ha a végére ért a feladatoknak
            $_SESSION_DB['end'] = true;
            exit;
        }
        $response['action'] = "null";
        echo json_encode($response);
        exit;
    }


}


/* elöző kérdés */

if(isset($_GET['action-before'])){
    $current_index = array_search($_SESSION_DB['feladat'], $feladatok); // a jelenegi index megtalálása a tömbben
    if($current_index > 0) { // ha a jelenlegi index nagyobb mint az első
        $_SESSION_DB['feladat'] = $feladatok[$current_index-1]; // csökkentjük a sessionben a feladat id-t, az előzőre, a következő feladat érdekében
        $_SESSION_DB['feladat_index'] -=1; // csökkentjük a sessionben a feladat indexet, hogy megjelenjen a feladat száma az oldalon
        $response['action'] = "before";
        echo json_encode($response);
        exit;
    }else{
        $response['action'] = "null";
        echo json_encode($response);
        exit;
    }
}
echo json_encode($response);
