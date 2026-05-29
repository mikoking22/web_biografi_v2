<?php
/**
 * includes/koneksi.php
 * Koneksi ke database MySQL
 */

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_biografi";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
