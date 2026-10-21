<?php
session_start();
include 'db_connection.php';

// 1. checking customer login 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. check customer status
$status_check = $conn->query("SELECT status FROM users WHERE id = '$user_id'")->fetch_assoc();

// 3. Status eka inactive (0) nam system eken logout karanava
if ($status_check['status'] == 0) {
    session_destroy();
    header("Location: login.php?msg=account_deactivated");
    exit();
}

$hotel_name = "Panama Luxury Hotel"; 
$hotel_phone = "+94 11 222 3333";

// --- 4. Unread messages check කිරීම (Notification Dot සඳහා) ---
$has_unread = false;
$unread_query = $conn->query("SELECT id FROM messages WHERE user_id = '$user_id' AND is_read = 0 LIMIT 1");
if ($unread_query && $unread_query->num_rows > 0) {
    $has_unread = true;
}

// --- add extra fee ---
if (isset($_POST['add_extra'])) {
    $desc = $conn->real_escape_string($_POST['extra_desc']);
    $amt = floatval($_POST['extra_amount']);
    $conn->query("INSERT INTO extra_orders (user_id, description, amount, order_date) VALUES ('$user_id', '$desc', $amt, NOW())");
    header("Location: live_bill.php"); 
    exit();
}

// get customer details
$user_query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
$user_res = $conn->query($user_query);
$user = $user_res->fetch_assoc();

// calculate room fee
$check_in = new DateTime($user['check_in_datetime']);
$interval = $check_in->diff(new DateTime());
$sessions = ceil((($interval->days * 24) + $interval->h + ($interval->i / 60)) / 12) ?: 1;
$room_total = $sessions * $user['price_per_session'];

$grand_total = $room_total;
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Bill - Guest View</title>
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
            position: relative; /* Notification Dot එක position කිරීමට */
        }

        nav a:hover {
            color: #38bdf8;
            text-shadow: 0 0 8px rgba(56, 189, 248, 0.5);
        }

        /* Creative Notification Red Dot Style */
        .notification-dot::after {
            content: '';
            position: absolute;
            top: 6px;
            right: 8px;
            width: 8px;
            height: 8px;
            background-color: #ef4444; /* Glowing Red */
            border-radius: 50%;
            box-shadow: 0 0 8px #ef4444, 0 0 15px #ef4444;
            animation: pulse-dot 1.5s infinite;
        }

        /* Dot එක pulse (නිවෙමින් පත්තුවෙමින්) වෙන්න දාපු animation එක */
        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .container { 
            max-width: 1000px; 
            margin: 40px auto; 
            padding: 0 20px; 
        }

        /* Glassmorphic Bill Card */
        .bill-card { 
            background: rgba(255, 255, 255, 0.02); 
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px; 
            padding: 40px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }

        /* Table Styling */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
            margin-top: 20px;
        }
        
        th { 
            text-align: left; 
            background: rgba(15, 23, 42, 0.6); 
            padding: 15px; 
            border-bottom: 2px solid rgba(255, 255, 255, 0.1); 
            color: #38bdf8;
            font-size: 0.85rem; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        
        td { 
            padding: 15px; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.05); 
            font-size: 0.95rem;
            color: #e2e8f0;
        }
        
        .price-col { 
            text-align: right; 
            font-weight: bold; 
            color: #38bdf8;
        }

        /* Total Section Box */
        .total-section { 
            background: rgba(56, 189, 248, 0.1); 
            border: 1px solid rgba(56, 189, 248, 0.2);
            color: white; 
            padding: 25px; 
            border-radius: 16px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .total-amount { 
            font-size: 2.2rem; 
            color: #38bdf8; 
            font-weight: bold;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
        }

        /* Creative Print Button */
        .btn-print {
            background: rgba(56, 189, 248, 0.1); 
            color: #38bdf8; 
            border: 1px solid rgba(56, 189, 248, 0.3); 
            padding: 14px 40px; 
            border-radius: 30px; 
            font-weight: bold; 
            cursor: pointer;
            transition: 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-print:hover {
            background: #38bdf8;
            color: #0f172a;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.4);
        }

        /* ==========================================================================
           PRINT MEDIA STYLES (බිල්පත මුද්‍රණය වන විට පමණක් ක්‍රියාත්මක වන රීති)
           ========================================================================== */
        @media print { 
            /* 1. අනවශ්‍ය කොටස් සඟවන්න */
            .no-print, nav { 
                display: none !important; 
            } 
            
            /* 2. පසුබිම මුද්‍රණයට ගැළපෙන සේ සුදු පාට කරන්න */
            body { 
                background: #ffffff !important; 
                color: #000000 !important; 
                padding: 0 !important;
            }
            
            .bill-card { 
                background: #ffffff !important; 
                border: none !important; 
                box-shadow: none !important; 
                padding: 0 !important;
                backdrop-filter: none !important;
            }

            /* 3. අකුරු සහ වගු පාට කළු කරන්න */
            th {
                background: #f1f5f9 !important;
                color: #000000 !important;
                border-bottom: 2px solid #000000 !important;
            }

            td {
                color: #000000 !important;
                border-bottom: 1px solid #e2e8f0 !important;
            }

            .price-col {
                color: #000000 !important;
            }

            .total-section {
                background: #f1f5f9 !important;
                border: 2px solid #000000 !important;
                color: #000000 !important;
            }

            .total-amount {
                color: #000000 !important;
                text-shadow: none !important;
            }
        }
    </style>
</head>
<body>

    <nav class="no-print">
        <a href="welcome.php">Home</a>
        <a href="index.php">Order Food</a>
        <a href="get_service.php">Get Service</a>
        <a href="live_bill.php" style="color: #38bdf8;">Bill</a>
        <a href="about_us.php">About us</a>
        
        <!-- Unread messages තිබේ නම් පමණක් 'notification-dot' class එක එකතු වේ -->
        <a href="messages.php" class="<?php echo $has_unread ? 'notification-dot' : ''; ?>">Messages</a>
        
        <a href="public_feedback.php">Reviews</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="container">
        <div class="bill-card">
            
            <div style="display:flex; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:20px; margin-bottom:20px;">
                <div>
                    <h2 style="margin:0; color:#38bdf8; font-weight: 700; letter-spacing: 0.5px;"><?php echo strtoupper($hotel_name); ?></h2>
                    <p style="color:#94a3b8; margin: 5px 0 0 0; font-size: 0.9rem;">Official Live Bill</p>
                </div>
                <div style="text-align:right; font-size: 0.95rem; line-height: 1.5;">
                    <p style="margin:0;"><strong>Guest:</strong> <span style="color: #38bdf8;"><?php echo htmlspecialchars($user['full_name']); ?></span></p>
                    <p style="margin:5px 0 0 0;"><strong>Room:</strong> <span style="color: #38bdf8;"><?php echo htmlspecialchars($user['room_number']); ?></span></p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Order Date</th>
                        <th class="price-col">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Room Accommodation</strong></td>
                        <td><small style="color: #94a3b8;"><?php echo date('Y-m-d', strtotime($user['check_in_datetime'])); ?></small></td>
                        <td class="price-col">Rs. <?php echo number_format($room_total, 2); ?></td>
                    </tr>

                    <?php 
                    $food_sql = "SELECT orders.*, menu_items.item_name FROM orders JOIN menu_items ON orders.item_id = menu_items.id WHERE user_id = '$user_id'";
                    $food_res = $conn->query($food_sql);
                    while($f = $food_res->fetch_assoc()): 
                        $grand_total += $f['total_price'];
                    ?>
                    <tr>
                        <td><?php echo $f['item_name']; ?></td>
                        <td><small style="color: #94a3b8;"><?php echo date('Y-m-d', strtotime($f['order_date'])); ?></small></td>
                        <td class="price-col">Rs. <?php echo number_format($f['total_price'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>

                    <?php 
                    $extra_res = $conn->query("SELECT * FROM extra_orders WHERE user_id = '$user_id'");
                    while($ex = $extra_res->fetch_assoc()): 
                        $grand_total += $ex['amount'];
                    ?>
                    <tr>
                        <td><strong><?php echo $ex['description']; ?></strong></td>
                        <td><small style="color: #94a3b8;"><?php echo date('Y-m-d', strtotime($ex['order_date'])); ?></small></td>
                        <td class="price-col">Rs. <?php echo number_format($ex['amount'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="total-section">
                <h2 style="margin:0; font-size: 1.4rem; letter-spacing: 0.5px;">GRAND TOTAL</h2>
                <div class="total-amount">Rs. <?php echo number_format($grand_total, 2); ?></div>
            </div>

            <div class="no-print" style="margin-top:40px; text-align:center;">
                <button onclick="window.print()" class="btn-print">Download / Print Bill 🖨</button>
            </div>
            
        </div>
    </div>

    <footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
        <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
    </footer>

</body>
</html>