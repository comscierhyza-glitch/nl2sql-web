<?php

$host     = "sql106.infinityfree.com";
$user     = "if0_42839730";
$password = "AIp9ucUxbdTlnen"; 
$database = "if0_42839730_sqlg1_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

?>