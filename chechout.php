<?php
session_start();
include 'db_connection.php';

// පාරිභෝගිකයා ලොග් වී නැතිනම් හෝ කරත්තය හිස් නම්
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_type = 'Online'; 
$status = 1; // 1 = Pending (Manage Orders වල පෙන්වීමට මෙය අනිවාර්යයි)
$success = false;

foreach ($_SESSION['cart'] as $item_id => $qty) {
    // ආහාරයේ මිල ලබා ගැනීම
    $item_query = $conn->query("SELECT price FROM menu_items WHERE id = '$item_id'");
    if ($item_row = $item_query->fetch_assoc()) {
        $unit_price = $item_row['price'];
        $total_price = $unit_price * $qty;

        // ඇණවුම Database එකට ඇතුළත් කිරීම
        $sql = "INSERT INTO orders (user_id, item_id, quantity, total_price, order_type, status, order_date) 
                VALUES ('$user_id', '$item_id', '$qty', '$total_price', '$order_type', '$status', NOW())";
        
        if ($conn->query($sql)) {
            $success = true;
        }
    }
}

if ($success) {
    unset($_SESSION['cart']); // කරත්තය හිස් කිරීම
    // සාර්ථක පණිවිඩය පෙන්වීමට status=success ලෙස cart.php වෙත යැවීම
    header("Location: cart.php?status=success");
    exit();
} else {
    echo "දෝෂයකි: " . $conn->error;
}
?>