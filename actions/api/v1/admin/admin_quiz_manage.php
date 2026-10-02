<?php
header('Content-Type: application/json');
//if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // GET és POST kérés is
//        die(json_encode(["request_error" => "Hiba: rossz kérési protokol!"]));
//}

require_once("../../../../session_db/session_db.php");

global $_SESSION_DB;
$_SESSION_DB->session_db_start();

$headers = getallheaders(); // HTTP header
$auth_id = $headers['X-Auth-Token'] ?? null; // auth_id

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

$category = $_GET['cat_id'] ?? null;


/* feladatok megjelenítése */

if(!empty($category)) {

    $sql = "SELECT kategoria.kategoria FROM kategoria WHERE kategoria.id = ?"; // kategória név
    $query = $conn->prepare($sql);
    $query->bind_param('i', $category);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 0) {
        $response['error'] = "Nem található a kategória!";
    } else {
        while ($row = $result->fetch_assoc()) {
            $response['kategoria'] = $row['kategoria'];
        }
    }

    $sql = "SELECT feladat.id, feladat.kerdes,
        (
            SELECT JSON_ARRAYAGG(valaszok.valasz)
            FROM valaszok
            JOIN feladat_valasz ON feladat_valasz.valasz_id = valaszok.id
            WHERE feladat_valasz.feladat_id = feladat.id
        ) AS valaszok,
        (
            SELECT JSON_ARRAYAGG(megoldasok.megoldas)
            FROM megoldasok
            JOIN feladat_megoldas ON feladat_megoldas.megoldas_id = megoldasok.id
            WHERE feladat_megoldas.feladat_id = feladat.id
        ) AS megoldasok
        FROM feladat
        WHERE feladat.kat_id = ?
        GROUP BY feladat.id, feladat.kerdes
        ORDER BY feladat.id";
    // a külön JOIN lekérdezéssel lehet megoldani, hogy az elemek ne ismétlődjenek, mivel a JSON_ARRAYAGG-nál nincsen DISTINCT parancs - mysqlben

    $query = $conn->prepare($sql);
    $query->bind_param("i", $category);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 0) {
        $response['error'] = "Nem található feladat ebben a kategoriában!";
    } else {
        while ($row = $result->fetch_assoc()) {
            $response['feladat'][] = [
                'id' => $row['id'],
                'kerdes' => $row['kerdes'],
                'valaszok' => $row['valaszok'],
                'megoldasok' => $row['megoldasok'],
            ];
        }
    }
}
/* feladatok módosítása */

$post_data = $_POST['feladat'] ?? [];

if(!empty($post_data)){
    $feladat_id = $post_data['feladat_id'] ?? null;
    $torles = $post_data['torles'] ?? false;

    if(!empty($feladat_id)){
        if($torles == true){ // adott feladat törlése táblákból
            $max_attempts = 3;
            $i = 0;
            while($i < $max_attempts) {
                try{
                    $conn->begin_transaction();

                    $sql = "DELETE FROM feladat WHERE feladat.id = ?";
                    $query = $conn->prepare($sql);
                    $query->bind_param('i', $feladat_id);
                    $query->execute();

                    $sql = "DELETE FROM valaszok
                                        WHERE valaszok.id NOT IN (SELECT valasz_id FROM feladat_valasz)";
                    $query = $conn->prepare($sql);
                    $query->execute();

                    $sql = "DELETE FROM megoldasok
                                        WHERE megoldasok.id NOT IN (SELECT megoldas_id FROM feladat_megoldas)";
                    $query = $conn->prepare($sql);
                    $query->execute();

                    $response['success_msg'] = "Feladat sikeresen törölve!";
                    $conn->commit();

                    break;
                }catch (Exception $e){
                    $conn->rollback();
                    $response['error_mod'] = $e->getMessage();
                    $i++;
                    usleep(100000); // 100 ms

                }
            }
        }


    }else{
        $response['error_mod'] = "Feladat id nincs megadva";
    }
}



echo json_encode($response);
exit;