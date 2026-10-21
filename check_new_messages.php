<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['new' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];

// කියවා නැති (is_read = 0) සහ තවමත් Popup නොවූ අලුත්ම මැසේජ් එකක් තිබේදැයි බැලීම
// සටහන: ඔබගේ messages table එකේ id, user_id, message, is_read තියෙන්න ඕනේ.
$query = "SELECT id, message FROM messages WHERE user_id = '$user_id' AND is_read = 0 ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    // මැසේජ් එක Popup එකට ගත්තාට පසු නැවත නැවත එකම දේ ඒම වැළැක්වීමට is_read = 1 කරන්න හෝ වෙනත් ක්‍රමයක් ගන්න.
    // මෙතනදී අපි සරලව මැසේජ් එක JSON එකක් ලෙස යවනවා.
    echo json_encode([
        'new' => true,
        'title' => 'නව දැනුම්දීමක්!',
        'body' => $row['message']
    ]);
} else {
    echo json_encode(['new' => false]);
}
?>