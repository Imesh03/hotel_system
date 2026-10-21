<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    $file_path = NULL;
    $file_type = NULL;

    // ගොනුවක් ඇතුලත් කර ඇත්නම් එය හැසිරවීම
    if (!empty($_FILES['media']['name'])) {
        $target_dir = "uploads/feedback/";
        
        // ෆෝල්ඩරය නැතිනම් සාදන්න
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_name = time() . "_" . basename($_FILES["media"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // පින්තූර හෝ වීඩියෝ පමණක් අවසර දීම
        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'mp4', 'mov'])) {
            if (move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
                $file_path = $file_name;
                $file_type = in_array($file_ext, ['mp4', 'mov']) ? 'video' : 'image';
            }
        }
    }

    $sql = "INSERT INTO feedback (user_id, message, rating, file_path, file_type) 
            VALUES ('$user_id', '$message', '$rating', '$file_path', '$file_type')";
    
    if ($conn->query($sql)) {
        header("Location: feedback.php?status=success");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>