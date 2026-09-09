<?php
$host     = getenv('MYSQLHOST') ?: 'localhost';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'railway';
$port     = getenv('MYSQLPORT') ?: 3306;

$conn = mysqli_connect($host, $user, $password, $database, (int)$port);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>