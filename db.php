
<?php

date_default_timezone_set("Asia/Colombo");

$host = "localhost";
$username = "root";
$password = "";
$database = "bitehub_db";

$conn = new mysqli($host, $username, $password, $database, 3307);

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+05:30'");
?>
