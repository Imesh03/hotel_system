<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_db";

// Connection එක සෑදීම
$conn = new mysqli($servername, $username, $password, $dbname);

// සම්බන්ධතාවය පරීක්ෂා කිරීම
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>