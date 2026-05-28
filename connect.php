<?php

ob_start();

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "chungchi_ngoaingu";

$conn = new mysqli($host, $user, $pass, $dbname);

if($conn->connect_error){
    die("Kết nối database thất bại!");
}

$conn->set_charset("utf8mb4");

date_default_timezone_set("Asia/Ho_Chi_Minh");

?>