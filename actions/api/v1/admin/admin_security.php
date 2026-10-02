<?php
header('Content-Type: application/json');
//if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//    die(json_encode(["request_error" => "Hiba: rossz kérési protokol!"]));
//}

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

$admin_user = $_SESSION_DB['authorized'];


$response = array(); // válasz, amit vissza fog adni a szerver


function check_password_req(string $input): bool { // jelszó ellenőrzés
    return strlen($input) >= 8 &&
        preg_match('/[A-Z]/', $input) &&
        preg_match('/[a-z]/', $input) &&
        preg_match('/[0-9]/', $input) &&
        preg_match('/[!@#$%^&*()\[\]{}\-_=+\\\\|;:\'",.<>\/?`~]/', $input);
}

if(isset($_GET['change_passwd'])){
    if (empty($_POST["current_password"]) || empty($_POST["new_password"])) {
        $response["error"] = "Nem lehet üres egyik mező sem!";
        echo json_encode($response);
        exit;
    }
    if (!check_password_req((string)$_POST["new_password"])) { // új jelszó újra ellenőrzése
        $response["error"] = "A jelszó nem felel meg a követelményeknek!";
        echo json_encode($response);
        exit;
    }
    $current_password = $_POST["current_password"]; // jelenlegi jelszó
// $response['debug_input1'] = $current_password;


// jelenlegi jelszó érvényességének ellenörzése

    $sql = "SELECT jelszo FROM admin_felhasznalok WHERE fnev = ?";
    $query = $conn->prepare($sql);
    $query->bind_param("s", $admin_user);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($current_password, $row["jelszo"])) { // ellenőrzi, hogya beírt jelszó egyezik-e az adatbázisból kiolvasott hash-elt jelszóval
            $max_attempts = 3;
            $i = 0;

            while ($i < $max_attempts) {
                try {
                    $conn->begin_transaction();

                    $sql = "UPDATE admin_felhasznalok SET jelszo = ? WHERE fnev = ?";
                    $query = $conn->prepare($sql);

                    $new_password = password_hash($_POST["new_password"], PASSWORD_BCRYPT); // jelszó tiitkosítása

                    $query->bind_param("ss", $new_password, $admin_user);
                    $query->execute();

                    $conn->commit();

                    $response["success"] = "Jelszó sikeresen módosítva lett " . $admin_user . " számára";
                    break;
                } catch (mysqli_sql_exception $e) {
                    $conn->rollback(); // visszaállítás hiba esetén
                    $response["mysqli_error"] = $e->getMessage(); // hiba üzenet
                    $i++;
                    usleep(100000); // 100 ms
                }
            }

        } else {
            $response["error"] = "A jelenlegi jelszó nem egyezik!";
            echo json_encode($response);
            exit;
        }
    }
    $query->execute();
}

echo json_encode($response);
exit;