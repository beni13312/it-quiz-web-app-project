<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["request_error" => "Hiba: rossz kérési protokol!"]));
}

require_once("../../../../session_db/session_db.php");

global $_SESSION_DB;
$_SESSION_DB->session_db_start();

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



require_once('../../../../conn/conn.php');
global $conn;

$response = array();



$cat = $_POST['category'] ?? null; // kategória admin_dash.php oldalról
$question = $_POST['exam-question'] ?? null; // kérés /admin/admin-dash/?site=admin_quiz_upload oldalról
$answers = array(); // válaszok
$solutions = array(); // megoldások

// válaszok hozzáadása tömbhöz
for($ans = 1; $ans<=10; $ans++){ // max 10
    if(isset($_POST['ans-'.$ans]) && !empty(trim($_POST['ans-'.$ans]))){
        $answers[] = $_POST['ans-' . $ans];
    }
}

// megoldások hozzáadása tömbhöz
for($sol = 1; $sol<=10; $sol++){ // max 10
    if(isset($_POST['isSol-'.$sol])){
        $solutions[] = $_POST['ans-' . $sol]; // valasz hozzáadása, ha a azonosító száma megegyezik az adott válasszal, a válasz kerül be a tömbbe
    }
}
if(empty($cat) || empty($question) || count($answers) < 2 || count($solutions) == 0){ // ellenörzés, megvan-e minden adat
    $response['error'] = "Nem lehet egy mező sem üres!!";
    echo json_encode($response);
    exit;


}else{
    // SQL tranzakció ujraprobálások max 3
    $max_attempts = 3;
    $i = 0;
    while($i < $max_attempts) {
        try {
            $conn->begin_transaction(); // tranzakciós query, ha hiba történne akkor az adatok visszaálnak
            /* ADATOK BEÍRÁSA */


            /* feladat */


            $sql_feladat = "INSERT INTO feladat (kat_id, kerdes) VALUES (?,?)";

            $query = $conn->prepare($sql_feladat);
            $query->bind_param("is", $cat, $question);
            $query->execute();
            $feladat_id = $query->insert_id; // feladat_id


            /* válaszok */


            $sql_ans = "INSERT INTO valaszok (valasz) VALUES (?)";

            $query = $conn->prepare($sql_ans);

            $valaszok_ids = []; // valaszok_id

            foreach ($answers as $ans) {
                $query->bind_param("s", $ans);
                $query->execute();
                $valaszok_ids[] = $query->insert_id;
            }

            /* megoldások */


            $sql_sol = "INSERT INTO megoldasok (megoldas) VALUES (?)";

            $query = $conn->prepare($sql_sol);

            $megoldasok_ids = []; // megoldasok_id

            foreach ($solutions as $sol) {
                $query->bind_param("s", $sol);
                $query->execute();
                $megoldasok_ids[] = $query->insert_id;
            }




            /* feladat_valasz */


            $sql_feladat_valasz = "INSERT INTO feladat_valasz (feladat_id, valasz_id) VALUES (?,?)";

            $query = $conn->prepare($sql_feladat_valasz);

            foreach ($valaszok_ids as $valasz_id) {
                $query->bind_param("ii", $feladat_id, $valasz_id);
                $query->execute();
            }


            /* feladat_megoldas */


            $sql_feladat_megoldas = "INSERT INTO feladat_megoldas (feladat_id, megoldas_id) VALUES (?,?)";

            $query = $conn->prepare($sql_feladat_megoldas);

            foreach ($megoldasok_ids as $megoldas_id) {
                $query->bind_param("ii", $feladat_id, $megoldas_id);
                $query->execute();

            }
            $conn->commit();
            $response['success'] = "Az adatok bekerültek az adatbázisba!";
            echo json_encode($response);
            exit;

        } catch (Exception $e) {
            $conn->rollback(); // hibaesetén visszaállítás
            $response['mysqli_error'] = "Nemsikerült feltölteni az adatokat az adatbázisba! " . $e->getMessage();
            $i++;
            usleep(100000); // 100 ms
        }
    }
    $response['error'] = "Nemsikerült feltölteni az adatokat az adatbázisba! ";
    echo json_encode($response);
    exit;
}