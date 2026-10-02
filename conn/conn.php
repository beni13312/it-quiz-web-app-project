<?php
require_once __DIR__ . '/../vendor/autoload.php'; // dotenv autoload file
use Dotenv\Dotenv; // dotenv betöltése

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2), 'php-web-feladatbank.env');
$dotenv->load();

$user = $_ENV["DB_USERNAME"];
$host = $_ENV['DB_HOSTNAME'];
$pass = $_ENV["DB_PASSWORD"];
$db = $_ENV["DB"];


try {
    $conn = mysqli_connect($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    die("Hiba lépett fel az adatbázis kapcsolódása közben: " . $e->getMessage());
}