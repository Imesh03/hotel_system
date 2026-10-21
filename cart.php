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
if ($status_check['status'] == 0) {
    session_destroy();
    header("Location: login.php?msg=account_deactivated");
    exit();
}
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
if ($status_check && $status_check['status'] == 'inactive') {
    session_destroy();
    header("Location: login.php?msg=account_deactivated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>මගේ කරත්තය - Shopping Cart</title>
    
    <style>
        /* Consistent Dark Luxury Theme */
        body {
            margin: 0;
            padding: 0;
            background: #0f172a;
            background-image: radial-gradient(circle at top right, #1e293b, #0f172a);
            color: #f1f5f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            overflow-x: hidden;
        }

        /* Consistent Navigation Bar */
        nav {
            display: flex;
            justify-content: center;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(15px);
            padding: 15px;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        nav a {
            color: #94a3b8;
            text-decoration: none;
            padding: 10px 20px;
            font-weight: 600;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 13px;
        }

        nav a:hover {
            color: #38bdf8;
            text-shadow: 0 0 8px rgba(56, 189, 248, 0.5);
        }

        /* Cart Wrapper Container */
        .cart-wrapper { 
            max-width: 1100px;
            margin: 50px auto; 
            padding: 0 20px; 
        }

        .cart-title {
            margin-bottom: 30px;
            color: #38bdf8; 
            border-left: 5px solid #38bdf8; 
            padding-left: 15px;
            font-size: 1.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Glassmorphic Cart Table */
        .cart-table { 
            width: 100%;
            border-collapse: collapse; 
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px; 
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .cart-table th { 
            background: rgba(15, 23, 42, 0.6);
            color: #38bdf8; 
            padding: 18px; 
            text-align: left; 
            font-weight: 600;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .cart-table td { 
            padding: 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05); 
            color: #e2e8f0;
        }

        /* Glassmorphic Total Summary Box */
		.total-box { 
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 30px; 
            margin-top: 30px; 
            border-radius: 20px; 
            text-align: right;
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }

        .remove-link { 
            color: #ef4444;
            text-decoration: none; 
            font-size: 1.3rem; 
            transition: 0.2s;
        }
        .remove-link:hover {
            color: #f87171;
            text-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
        }
        .empty-msg { 
            text-align: center;
            padding: 60px; 
            color: #94a3b8; 
            font-size: 1.1rem;
        }

        /* Action Buttons */
        .admin-btn { 
            padding: 12px 25px;
            border-radius: 12px; 
            color: white; 
            border: none; 
            cursor: pointer; 
            display: inline-block; 
            font-weight: 600;
            transition: 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
        }
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-checkout {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .btn-checkout:hover {
            background: #10b981;
            color: #0f172a;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
        }

        /* 🎨 Custom Offline Luxury Alert Styles */
        .luxury-alert-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.85);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .luxury-alert-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .luxury-alert-box {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 20px;
            padding: 35px 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            transform: scale(0.8);
            transition: transform 0.3s ease;
            position: relative;
        }

        .luxury-alert-overlay.active .luxury-alert-box {
            transform: scale(1);
        }

        .luxury-alert-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: #10b981;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .luxury-alert-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
        }

        .luxury-alert-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 12px;
        }

        .luxury-alert-message {
            color: #94a3b8;
            font-size: 0.98rem;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .luxury-alert-btn {
            background: #10b981;
            color: #0f172a;
            border: none;
            padding: 12px 35px;
            font-size: 0.95rem;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            text-transform: uppercase;
        }
		.luxury-alert-btn:hover {
            background: #34d399;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <nav>
        <a href="welcome.php">Home</a>
        <a href="index.php">Order Food</a>
        <a href="get_service.php">Get Service</a>
        <a href="live_bill.php">Bill</a>
        <a href="about_us.php">About us</a>
        <a href="messages.php">Messages</a>
        
        <a href="public_feedback.php">Reviews</a>
        <a href="logout.php">Logout</a>
    </nav>

<div class="cart-wrapper">
    
    <h2 class="cart-title">Shopping Cart</h2>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Food</th>
                <th style="text-align: center;">price9Rs.)</th>
                <th style="text-align: center;">Qty</th>
                
                <th style="text-align: right;">Subtotal</th>
                <th style="text-align: center;">delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_total = 0;
            if (!empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $item_id => $qty) {
                    $item_id = $conn->real_escape_string($item_id);
                    $sql = "SELECT * FROM menu_items WHERE id = '$item_id'";
                    $result = $conn->query($sql);
                    if ($result && $row = $result->fetch_assoc()) {
                        $subtotal = $row['price'] * $qty;
                        $grand_total += $subtotal;
                        ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                     <img src="uploads/<?php echo $row['image_path']; ?>" width="60" height="60" style="border-radius: 12px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);">
                                    <strong style="color: #f1f5f9;"><?php echo $row['item_name']; ?></strong>
                                </div>
                            </td>
                            <td style="text-align: center; color: #94a3b8;"><?php echo number_format($row['price'], 2); ?></td>
                            <td style="text-align: center; font-weight: bold; color: #38bdf8;"><?php echo $qty; ?></td>
                            <td style="text-align: right; font-weight: bold; color: #38bdf8;">Rs. <?php echo number_format($subtotal, 2); ?></td>
                            <td style="text-align: center;"><a href="remove_item.php?id=<?php echo $item_id; ?>" class="remove-link" title="Remove">🗑</a></td>
                        </tr>
                        <?php
                    }
                }
            } else {
                echo "<tr><td colspan='5' class='empty-msg'>Cart is empty. <br><br> <a href='index.php' class='admin-btn btn-checkout' style='text-decoration:none;'>🍽 View Menu</a></td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php if ($grand_total > 0): ?>
        <div class="total-box">
            <span style="font-size: 1.1rem; color: #94a3b8;">Grand Total:</span>
            <div style="font-size: 2.2rem; color: #38bdf8; font-weight: bold; margin-top: 5px; letter-spacing: 0.5px;">Rs. <?php echo number_format($grand_total, 2); ?></div>
            
            <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 15px;">
                <a href="index.php" class="admin-btn btn-back" style="text-decoration: none;">🏠 Add more</a>
                <a href="checkout.php" class="admin-btn btn-checkout" style="text-decoration: none;">✅ complete your order</a>
            </div>
         </div>
    <?php endif; ?>
</div>
<div class="luxury-alert-overlay" id="customAlertModal">
    <div class="luxury-alert-box">
        <div class="luxury-alert-icon">✅</div>
        <div class="luxury-alert-title">Order Successful!</div>
        <div class="luxury-alert-message">Your order is successfull!</div>
        <button class="luxury-alert-btn" id="modalOkBtn">OK</button>
    </div>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
<script>
    // පද්ධතිය සක්‍රීය කර Modal එක පෙන්වීම
    const alertModal = document.getElementById('customAlertModal');
    const modalOkBtn = document.getElementById('modalOkBtn');

    alertModal.classList.add('active');

    // OK ක්ලික් කළ විට සෘජුවම index.php වෙත යොමු කිරීම
    modalOkBtn.addEventListener('click', function() {
        window.location.href = 'index.php';
    });
</script>
<?php endif; ?>

<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Panama Luxury Hotel Management System | All Rights Reserved.</p>
</footer>

</body>
</html>