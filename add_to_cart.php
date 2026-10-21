<?php
session_start();
include 'db_connection.php';

// 1. මුලින්ම පාරිභෝගිකයා ලොග් වී ඇත්දැයි බලන්න
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. දැන් පාරිභෝගිකයාගේ status එක database එකෙන් පරීක්ෂා කරන්න
$status_check = $conn->query("SELECT status FROM users WHERE id = '$user_id'")->fetch_assoc();

// 3. Status එක inactive නම් වහාම ලොග් අවුට් කරන්න
if ($status_check['status'] == 'inactive') {
    session_destroy();
    header("Location: login.php?msg=account_deactivated");
    exit();
}

// --- මින් පහළට ඔබේ පිටුවේ අනෙකුත් කේතයන් ---
?>
<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    
    // පාරිභෝගිකයා තෝරාගත් ප්‍රමාණය ලබා ගැනීම (අවම 1ක් ලෙස)
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($quantity < 1) $quantity = 1;

    // කරත්තය (Cart) පද්ධතියේ තවම නැතිනම් අලුතින් Array එකක් සාදන්න
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    // අයිතමය දැනටමත් කරත්තයේ තිබේ නම්, පවතින ප්‍රමාණයට අලුත් ප්‍රමාණය එකතු කරන්න
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] += $quantity;
    } else {
        // අලුත් අයිතමයක් නම් ප්‍රමාණය කෙලින්ම ඇතුළත් කරන්න
        $_SESSION['cart'][$item_id] = $quantity;
    }

    // නැවත මෙනු එකටම (index.php) යන්න
    header("Location: index.php?status=success");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>