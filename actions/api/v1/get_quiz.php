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

// input JS
$category = $_GET['category'] ?? die(json_encode(['error' => 'Error: null - category'])); // kategória

$_SESSION_DB['category'] = $category;

$response = array(); // válasz, amit vissza fog adni a szerver



// ketegória

$sql_cat = "SELECT kategoria.kategoria
                FROM kategoria
                WHERE kategoria.id = ?";

$query = $conn->prepare($sql_cat);
$query->bind_param("i", $category);
$query->execute();
$result = $query->get_result();

if($result->num_rows === 0){
    $response['error'] = 'Nincs ilyen kategória'; // ha nincsen egyáltalán a feladat az adott kategóriába
    echo json_encode($response);
    exit;

}else{
    $row = $result->fetch_assoc();
    $response['kategoria'] = $row['kategoria'];
}



// ha nincsen feladat beállítva a sessionben, akkor a kategóriából választja ki a legkissebb id-vel rendelkező feladatot
if(!isset($_SESSION_DB['feladat']) || !isset($_SESSION_DB['feladat_index'])) {
    $sql_id = "SELECT feladat.id
                            FROM feladat WHERE feladat.kat_id=? 
                            ORDER BY feladat.id ASC LIMIT 1"; // a legalacsonyabb feladat id kezdésnek
    $query = $conn->prepare($sql_id);
    $query->bind_param("i", $category);
    $query->execute();
    $result = $query->get_result();

    if($result->num_rows === 0){
        $response['error'] = 'Nem található feladat ebben a kategóriában :('; // ha nincsen egyáltalán feladat az adott kategóriába
        echo json_encode($response);
        exit;
    }else{
        $row = $result->fetch_assoc();
        $_SESSION_DB['feladat'] = $row['id']; // legelső feladat beállítása
        $_SESSION_DB['feladat_index'] = 1;
    }
}

if($_SESSION_DB['feladat'] === "end") { // a feladatok vége, akkor visszaad 'end': true JSON-ben
    $response['end'] = true;
    echo json_encode($response);
    exit;
}

 // ha van feladat id és feladat index

    // kérdés
    $sql_kerdes = "SELECT feladat.kerdes
                            FROM feladat WHERE feladat.id = ? AND feladat.kat_id=?";
    $query = $conn->prepare($sql_kerdes);
    $query->bind_param("ii", $_SESSION_DB['feladat'],$category);
    $query->execute();
    $result =  $query->get_result();

    if($result->num_rows === 0){ // ha a a sessionben nem megfelelő a feladat_id akkor ujra kiválasztja a legkisebb elemet a táblából
        if(isset($_SESSION_DB['good_ans_score'])){ // elöző session eltávolítása
            unset($_SESSION_DB['good_ans_score']);
        }
        if(isset($_SESSION_DB['seen_feladat'])){
            unset($_SESSION_DB['seen_feladat']);
        }
        if(isset($_SESSION_DB['all_checked_answers'])){
            unset($_SESSION_DB['all_checked_answers']);
        }


        $sql_id = "SELECT feladat.id
                            FROM feladat WHERE feladat.kat_id=? 
                            ORDER BY feladat.id ASC LIMIT 1"; // a legalacsonyabb feladat id a kategoriából, kezdésnek

        $query = $conn->prepare($sql_id);
        $query->bind_param("i", $category);
        $query->execute();
        $result = $query->get_result();

        if($result->num_rows === 0) {
            $response['error'] = 'Nem található feladat ebben a kategóriában :('; // ha nincsen egyáltalán a feladat az adott kategóriába
            echo json_encode($response);
            exit;
        }else{
            $row = $result->fetch_assoc();
            $_SESSION_DB['feladat'] = $row['id'];
            $_SESSION_DB['feladat_index'] = 1;

            // kerdes SQL újra lefuttatása
            $query = $conn->prepare($sql_kerdes);
            $query->bind_param("ii", $_SESSION_DB['feladat'],$category);
            $query->execute();
            $result = $query->get_result();

            if($result->num_rows === 0) {
                $response['error'] = 'Nem található feladat ebben a kategóriában :('; // ha nincsen egyáltalán a feladat az adott kategóriába
                echo json_encode($response);
                exit;
            }else{
                $row = $result->fetch_assoc();
                $response['kerdes'] = $row['kerdes'];
            }
        }
    }else{
        $row = $result->fetch_assoc();
        $response['kerdes'] = $row['kerdes'];
    }



// számláló, feladat_index
$sql_szam = "SELECT COUNT(feladat.id) AS 'szam'
                            FROM feladat WHERE feladat.kat_id=?";
$query = $conn->prepare($sql_szam);
$query->bind_param("i", $category);
$query->execute();

if($row = $query->get_result()->fetch_assoc()){
    $response['feladat_index'] = $_SESSION_DB['feladat_index'];
    $response['szamlalo'] = $_SESSION_DB['feladat_index']."/".$row['szam']; // pl.: 1/3
}


// megoldás szám

$sql_ans = "SELECT COUNT(megoldasok.id) as 'szam'
                    FROM feladat
                    JOIN feladat_megoldas on feladat.id = feladat_megoldas.feladat_id
                    JOIN megoldasok on feladat_megoldas.megoldas_id = megoldasok.id
                    WHERE feladat.id = ?";

$query = $conn->prepare($sql_ans);
$query->bind_param("i", $_SESSION_DB['feladat']);
$query->execute();
$result = $query->get_result();
$row = $result->fetch_assoc();

if($row['szam'] > 1){ // ha több a megoldás, ami azt jelenti, hogy a rekordok száma nagyobb mint 1
    $response['tobb_megoldas'] = true;
}else{
    $response['tobb_megoldas'] = false;
}


// lehetséges válaszok

$sql_ans2 = "SELECT valaszok.id, valaszok.valasz -- válaszok kiírása
                        FROM feladat
                        JOIN feladat_valasz on feladat_valasz.feladat_id = feladat.id
                        JOIN valaszok on valaszok.id = feladat_valasz.valasz_id
                        WHERE feladat.id = ?;"; // válaszok

$query = $conn->prepare($sql_ans2);
$query->bind_param("i",$_SESSION_DB['feladat']);
$query->execute();
$result = $query->get_result();

if($result->num_rows === 0){
    $response['error'] = 'Hiba történt a válaszok lekérdezésekor!'; // ha nincsen egyáltalán a feladat az adott kategóriába
    echo json_encode($response);
    exit;
}else {
    while ($row = $result->fetch_assoc()) { // válaszok hozzáadása tömbhöz
        $response['valaszok'][] = [
            'id' => $row['id'],
            'valasz' => $row['valasz'],
        ];
    }
}
// a már megoldott feladatok
if(isset($_SESSION_DB['seen_feladat'])){
    if (in_array((string)$_SESSION_DB['feladat'], explode(';', $_SESSION_DB['seen_feladat']), true)) { // ha a jelenlegi feladat az már egyszer volt, akkor megoldás megjelenítése
        $response['seen'] = true;
    }
}


echo json_encode($response); // array enkódolása JSON-be ezt fogja megkapni a kérő
exit;