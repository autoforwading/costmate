<?php
// live database configuration
$servername = 'localhost';
$username = 'u928163871_costmate_root';
$password = 'Turan##1211391';
$database = 'u928163871_costmate';

// // local database configuration
// $servername = "localhost";
// $username = "root";
// $password = "";
// $database = "costmate";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}
?>