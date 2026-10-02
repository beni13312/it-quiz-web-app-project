<?php
/* SESSION_DB - */

date_default_timezone_set('Europe/Budapest'); // időzona beállítása, egyenlő kell legyen a szerver időzonájával

require_once __DIR__.'/../vendor/autoload.php'; // dotenv autoload file
use Dotenv\Dotenv; // dotenv betöltése

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2), 'php-web-feladatbank.env');
$dotenv->load();

class session_db implements ArrayAccess { // SESSION_DB['XYZ']

    private  mysqli $db; // adatbázis kapcsolat
    private string $_session_id = ""; // PHP session azonosító
    
    public function __construct(){}

    public function session_db_start(): void{ // adatbázis kapcsolat, session érvényességének ellenörzése
        $user = $_ENV['DB_USERNAME'];
        $host = $_ENV['DB_HOSTNAME'];
        $pass = $_ENV['DB_PASSWORD'];
        $db = $_ENV['DB'];

        try {
            $conn = mysqli_connect($host, $user, $pass, $db);
        } catch (mysqli_sql_exception $e) {
            die("Hiba lépett fel az adatbázis kapcsolódása közben: " . $e->getMessage());
        }
        $this->db = $conn;
//        if(session_status() === PHP_SESSION_NONE){ // PHP session elindítása, ha még nincsen, létrehoz egy php sessint a böngészőben is
//            session_name('usr_id');
//
//            // user id cookie
//            if(!isset($_COOKIE['usr_id'])){
//                $session_id = bin2hex(openssl_random_pseudo_bytes(64)); // session token létrehozása
//                session_id($session_id);
//            }
//
//            ini_set('session.sid_length', 128); // session token hossza
//            session_set_cookie_params([
//                "lifetime" => 60*60, // 60 min
//                "path" => "/",
//                "secure" => true,
//                "httponly" => true,
//                "samesite" => "Strict"
//            ]);
//            ini_set('session.gc_maxlifetime', 60 * 60);
//           // session_start();
//
//        }
        if(!isset($_COOKIE['usr_id']) || strlen((string)$_COOKIE['usr_id']) !== 128){ // ha nincs létre hozva session cookie
            $session_id = bin2hex(random_bytes(64)); // session token létrehozása 128 hosszú string

            setcookie('usr_id', $session_id,[
                "expires" => time() + 60*60, // 60 min
                "path" => "/",
                "secure" => true,
                "httponly" => true,
                "samesite" => "Strict"
            ]);
            $_COOKIE['usr_id'] = $session_id;
            $this->insert_session_db_id($session_id);
            $this->_session_id = $session_id;
        }else { // ha létezik már cookie
            $sql = 'SELECT session_id FROM session_db WHERE session_id = ?'; // ellenőrzi hogy létezik-e a beállított
            $query = $conn->prepare($sql);
            $query->bind_param('s', $_COOKIE['usr_id']);
            $query->execute();
            $result = $query->get_result();

            if ($result->num_rows === 1) {
                $this->_session_id = $_COOKIE['usr_id'];
            } else {
                $this->session_db_regenerate_id();
            }
        }


        // session validation
        // lejárt sessionök törlése

        // összes session, ami nem a jelenlegi

        $max_attempts = 3;
        $i = 0;
        while($i < $max_attempts){
            try {
                $this->db->begin_transaction();

                $sql = "DELETE FROM session_db
                      WHERE session_id NOT LIKE ? AND
                            expires_at < NOW()"; // összes lejárt session törlése az adatbázisból

                $query = $this->db->prepare($sql);
                $query->bind_param("s", $this->_session_id);
                $query->execute();

                $this->db->commit();
                break;
            }catch (mysqli_sql_exception $e){
                $this->db->rollback(); // visszaállítás hiba esetén
                echo $e->getMessage(); // hiba üzenet
                $i++;
                usleep(100000); // 100 ms
            }
        }

        // jelenlegi session

        $sql = "SELECT session_data FROM session_db 
                  WHERE session_id = ? AND
                        expires_at < NOW()"; // Hogyha a session expires_at record értéke kisebb mint a jelenlegi idő, akkor a session lejárt


        $query = $this->db->prepare($sql);
        $query->bind_param("s", $this->_session_id);
        $query->execute();
        $result = $query->get_result();

        if($result->num_rows === 1){ // hogyha lejárt a sessin akkor törli és újragenerálja a session-t
            $row = $result->fetch_assoc();
            $decoded_data = json_decode($row['session_data'], true);

            if(!empty($decoded_data['authorized']) || !empty($decoded_data['auth_id'])){ // ha létezik bejelentkezési adat, akkor törli a jelenlegi session adatokat is
                $this->session_db_unset(); // session törlése teljesen az adatbázisból
            }
            $this->session_db_regenerate_id(); // session újragenerálása, hogy az elöző session id törlődjön a böngészőből is
        }
        
    }
    private function set_session_db_id(): string{
            $session_id = bin2hex(random_bytes(64)); // session token létrehozása

            setcookie('usr_id', $session_id,[
                "path" => "/",
                "secure" => true,
                "httponly" => true,
                "samesite" => "Strict"
            ]);
            return $session_id;
    }
    private function insert_session_db_id(string $session_id): void{ // session token beillesztése adatbázisba
        $expires_at = date('Y-m-d H:i:s',time() + (60*60)); // 60 min

        $max_attempts = 3;
        $i = 0;
        while ($i < $max_attempts){
            try {
                $this->db->begin_transaction(); // SQL tranzaktió, hiba esetén nem fog érvényesüli a modosítás

                $sql = "INSERT IGNORE INTO session_db (session_id, session_data, expires_at) VALUES (?,'{}',?)";
                $query = $this->db->prepare($sql);
                $query->bind_param("ss", $session_id, $expires_at);
                $query->execute();

                $this->db->commit(); // érvényesítés
                break;
            }catch (mysqli_sql_exception $e){
                $this->db->rollback(); // visszaállítás hiba esetén
                echo $e->getMessage(); // hiba üzenet
                $i++;
                usleep(100000); // 100 ms
            }
        }
    }
    public function session_db_status(): bool{ // session status
        if(empty($this->_session_id)){
            return false;
        }

        $sql = "SELECT 1 FROM session_db WHERE session_id = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param("s", $this->_session_id);
        $query->execute();
        $result = $query->get_result();

        if($result->num_rows === 1){ // ha létezik, az adatbázisban az adott jelenlegi session, akkor visszaad true-t
            return true;
        }
        return false;
    }

    // -- ArrayAccess
    public function offsetExists($offset): bool
    {
        $sql = "SELECT session_data FROM session_db WHERE session_id = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param("s", $this->_session_id);
        $query->execute();


        if ($row = $query->get_result()->fetch_assoc()) {
            $session_data = json_decode($row['session_data'], true); // JSON to array
            return isset($session_data[$offset]); // ha létezik az érték a JSON struct-ba
        }
        return false;
    }

    public function &offsetGet($offset) : mixed // viszaadja a direct memoria címet, direct reference
    {
        $sql = "SELECT session_data FROM session_db WHERE session_id = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param("s", $this->_session_id);
        $query->execute();

        if($row = $query->get_result()->fetch_assoc()){
            $session_data = json_decode($row['session_data'], true); // JSON-ből array tömb
            return $session_data[$offset];// ha létezik a JSON ben akkor visszaadja az értéket
        }
        $null = null;
        return $null;
    }

    public function offsetSet($offset, $value): void
    {
        $sql = "SELECT session_data FROM session_db WHERE session_id = ?";
        $query = $this->db->prepare($sql);
        $query->bind_param("s", $this->_session_id);
        $query->execute();
        $result = $query->get_result();

        if($result->num_rows === 1){ // ha létezik a session
            $row = $result->fetch_assoc();
            $session_data = json_decode($row['session_data'], true);

            $session_data[$offset] = $value; // érték beállítása a JSON key-en

            $max_attempts = 3;
            $i = 0;

            while($i < $max_attempts){
                try{
                    $this->db->begin_transaction(); // SQL tranzaktió, hiba esetén nem fog érvényesüli a modosítás

                    $sql = "UPDATE session_db SET session_data = ? WHERE session_id = ?";
                    $query = $this->db->prepare($sql);
                    $json_data = json_encode($session_data);
                    $query->bind_param("ss", $json_data, $this->_session_id);
                    $query->execute();

                    $this->db->commit();
                    break;
                }catch(mysqli_sql_exception $e){
                    $this->db->rollback(); // visszaállítás hiba esetén
                    echo $e->getMessage(); // hiba üzenet
                    $i++;
                    usleep(100000); // 100 ms
                }
            }
        }else{
            $session_data = json_encode([$offset => $value]); // key value JSON encode
            $expires_at = date('Y-m-d H:i:s',time() + (60*60)); // 60 min

            $max_attempts = 3;
            $i = 0;
            while ($i < $max_attempts){
                try {
                    $this->db->begin_transaction(); // SQL tranzaktió, hiba esetén nem fog érvényesüli a modosítás

                    $sql = "INSERT IGNORE INTO session_db (session_id, session_data, expires_at) VALUES (?, ?, ?)";
                    $query = $this->db->prepare($sql);
                    $query->bind_param("sss", $this->_session_id, $session_data, $expires_at);
                    $query->execute();

                    $this->db->commit(); // érvényesítés
                    break;
                }catch (mysqli_sql_exception $e){
                    $this->db->rollback(); // visszaállítás hiba esetén
                    echo $e->getMessage(); // hiba üzenet
                    $i++;
                    usleep(100000); // 100 ms
            }
            }
        }


    }

    public function offsetUnset($offset): void
    {
        $sql = "SELECT session_data FROM session_db WHERE session_id = ?"; // kiolvassa az adott session adatait
        $query = $this->db->prepare($sql);
        $query->bind_param("s", $this->_session_id);
        $query->execute();

        if($row = $query->get_result()->fetch_assoc()){
            $session_data = json_decode($row['session_data'], true) ?? null;
            if(isset($session_data[$offset])){
                unset($session_data[$offset]); // adott érték a JSON-ben nullára állítása
            }

            $json_data = json_encode($session_data); // átalakítás JSON struct-ra

            $max_attempts = 3;
            $i = 0;

            while ($i < $max_attempts){
                try{
                    $this->db->begin_transaction(); // SQL tranzaktió, hiba esetén nem fog érvényesüli a modosítás

                    $sql = "UPDATE session_db SET session_data = ? WHERE session_id = ?"; // firssíti a sessiont
                    $query = $this->db->prepare($sql);
                    $query->bind_param("ss", $json_data, $this->_session_id);
                    $query->execute();

                    $this->db->commit();
                    break;
                }catch(mysqli_sql_exception $e){
                    $this->db->rollback(); // visszaállítás hiba esetén
                    echo $e->getMessage(); // hiba üzenet
                    $i++;
                    usleep(100000); // 100 ms
                }
            }
}
    }
    // --

    public function session_db_regenerate_id(): void // újra generálja a sessiont id-t, és törli a régit, az adatok megmaradnak
    {


        $old_session_id = $this->_session_id; // régi session id

        // session_regenerate_id(true);
        $new_session_id = $this->set_session_db_id();// új session id
        $this->_session_id = $new_session_id;
        $expires_at = date('Y-m-d H:i:s',time() + (60*60)); // 60 min

        $max_attempts = 3;
        $i = 0;

        while ($i < $max_attempts){
            try {
                $this->db->begin_transaction(); // SQL tranzaktió, hiba esetén nem fog érvényesüli a modosítás

                $sql = "UPDATE session_db SET session_id = ?, expires_at = ? WHERE session_id = ?"; // session id frissítése az újra, a session adatai megmaradnak
                $query = $this->db->prepare($sql);
                $query->bind_param("sss", $new_session_id, $expires_at, $old_session_id);
                $query->execute();

                $this->db->commit(); // érvényesítés

//                // Force immediate session write
//                session_write_close();
//                session_start(); // Restart session with new ID

                break;
            }catch (mysqli_sql_exception $e){
                $this->db->rollback(); // visszaállítás hiba esetén
                echo $e->getMessage(); // hiba üzenet
                $i++;
                usleep(100000); // 100 ms
            }
        }



    }
    public function session_db_destroy(): void // törli az egész sessiont
    {
        $max_attempts = 3;
        $i = 0;

        while ($i < $max_attempts){
        try{
            $this->db->begin_transaction();
            $sql = "DELETE FROM session_db WHERE session_id = ?";
            $query = $this->db->prepare($sql);
            $query->bind_param("s", $this->_session_id);
            $query->execute();

            $this->db->commit();
            break;
        }catch (mysqli_sql_exception $e){
            $this->db->rollback(); // visszaállítás hiba esetén
            echo $e->getMessage(); // hiba üzenet
            $i++;
            usleep(100000); // 100 ms
        }
        unset($_COOKIE['usr_id']); // session cookie törlése
    }
    }
    public function session_db_unset(): void // törli a session adatokat
    {
        $max_attempts = 3;
        $i = 0;

        while ($i < $max_attempts){
        try{
            $this->db->begin_transaction();

            $sql = "UPDATE session_db SET session_data = '{}' WHERE session_id = ?"; // törli a session adatokat az a adtbázisból
            $query = $this->db->prepare($sql);
            $query->bind_param("s",$this->_session_id);
            $query->execute();

            $this->db->commit();
            break;
        }catch(mysqli_sql_exception $e){
            $this->db->rollback(); // visszaállítás hiba esetén
            echo $e->getMessage(); // hiba üzenet
            $i++;
            usleep(100000); // 100 ms
        }
        }

    }
}

$_SESSION_DB = new session_db();