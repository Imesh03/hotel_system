<?php
session_start();
include 'db_connection.php';

$user_id = $_SESSION['user_id'];
// කියවා නැති අලුත්ම පණිවිඩය ලබා ගැනීම
$sql = "SELECT message FROM messages WHERE user_id = $user_id AND is_read = 0 ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($row = $result->fetch_assoc()) {
    echo $row['message'];
} else {
    echo "none";
}
?>