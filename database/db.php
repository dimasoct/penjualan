<?php
$host = 'x7piv.h.filess.io'; // Nama host database
$dbname = 'appku_butterits'; // Nama database
$username = 'appku_butterits'; // Username database
$password = 'b43a706ce7203f4d03ddcc821b592b59d9345cd8'; // Password database
$port = '3307'; // Port database

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}
?>
