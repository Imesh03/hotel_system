<?php
include 'db_connection.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 'PHPMailer/src/' ඉවත් කර කෙලින්ම File Names පමණක් ඇතුළත් කරන්න
require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

// 1. Status Update logic
if (isset($_GET['update_status'])) {
    $u_id = intval($_GET['u_id']); 
    $status = intval($_GET['update_status']);
    $phone = $_GET['phone'];
    $items_summary = urldecode($_GET['items']);

    // Database  Status  Update 
    $sql_update = "UPDATE orders SET status = $status WHERE user_id = $u_id AND status < 4";
    $conn->query($sql_update);

    // get customer details in database
    $user_query = $conn->query("SELECT full_name, email FROM users WHERE id = $u_id");
    $user_data = $user_query->fetch_assoc();
    $customer_name = $user_data['full_name'] ?? 'Valued Guest';
    $customer_email = $user_data['email'] ?? '';

    // select sending mssge
    $msg_text = "";
    if ($status == 2) $msg_text = "Your order accepted ($items_summary) .";
    if ($status == 3) $msg_text = "Your order is ready ($items_summary) ";
    if ($status == 4) $msg_text = "Your order is complete($items_summary) . Thank you!";

    // send mssge to mssge box
    if ($msg_text != "") {
        $conn->query("INSERT INTO messages (user_id, message, is_read, created_at) VALUES ('$u_id', '$msg_text', 0, NOW())");
    }

    //  WhatsApp , Email yvna ek Ready Status 
    if ($status == 3) {
        
        //  PHPMailer email
        if (!empty($customer_email)) {
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'lasadaimesh@gmail.com';          // Gmail 
                $mail->Password   = 'replzijwagofybtz';               // Gmail App Password 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Localhost එකේදී එන SSL Error මඟහරවා ගැනීමට (Live Server එකේදී මෙය ඉවත් කරන්න පුළුවන්)
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                // Recipients
                $mail->setFrom('lasadaimesh@gmail.com', 'Hotel Panama');
                $mail->addAddress($customer_email, $customer_name);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your Order is Ready! - Hotel Panama';
                
                // HTML Email Body
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #ffffff;'>
                        <h2 style='color: #fbbf24; text-align: center;'>🏨 HOTEL PANAMA</h2>
                        <p>Dear <strong>{$customer_name}</strong>,</p>
                        <p>Great news! Your delicious order is prepared and ready to be served.</p>
                        <div style='background-color: #f8fafc; padding: 15px; border-left: 4px solid #fbbf24; margin: 20px 0;'>
                            <strong style='color: #0f172a;'>Ordered Items:</strong><br>
                            <span style='color: #334155;'>{$items_summary}</span>
                        </div>
                        <p>Our service team will deliver it to your room shortly, or you can collect it from the counter.</p>
                        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='font-size: 0.85rem; color: #94a3b8; text-align: center;'>Thank you for choosing Hotel Panama Command Center.</p>
                    </div>";
                
                // Plain Text Version (Spam Box එකට නොයා Inbox එකටම යාමට උදව් වේ)
                $mail->AltBody = "Dear {$customer_name},\n\nYour order ($items_summary) is Ready now!\n\nThank you for choosing Hotel Panama.";

                $mail->send();
            } catch (Exception $e) {
                // Email එක යැවීමට නොහැකි වුවහොත් Error එකක් පෙන්වීමට අවශ්‍ය නම් මෙතැන ලිවිය හැක.
            }
        }
        // Email කොටස අවසන් 

        // WhatsApp  Redirect කිරීම
        $wa_url = "https://wa.me/$phone?text=" . urlencode("Hello! Your order ($items_summary) is Ready now. - Hotel Panama");
        echo "<script>window.open('$wa_url', '_blank'); window.location.href='manage_orders.php';</script>";
        exit();
    } else {
        header("Location: manage_orders.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Order Management | Premium Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* 🎨 Premium Luxury Dark Theme Variables */
        :root {
            --primary-bg: #090d16;      
            --card-bg: #131c2e;         
            --border-color: #243552;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-blue: #38bdf8;
            --accent-gold: #fbbf24;
            --success-green: #10b981;
            --warning-orange: #f97316;
            --danger-red: #f87171;
            --input-bg: #090d16;
            --nav-bg: rgba(19, 28, 46, 0.7);
        }

        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            background-color: var(--primary-bg);
            margin: 0;
            padding: 0;
            color: var(--text-main);
        }

        /* 🚀 Floating Glassmorphism Navigation Bar Styles */
        .admin-navbar {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 92%;
            max-width: 1300px;
            z-index: 1000;
            background: var(--nav-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 28px;
            box-sizing: border-box;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .admin-navbar .logo {
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: var(--text-main);
        }

        .admin-navbar .logo span {
            color: var(--accent-gold);
        }

        .admin-nav-links {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .admin-nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 10px 14px;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        /* Active State Style (Manage orders) */
        .admin-nav-links a.active {
            color: #090d16;
            background: var(--accent-gold);
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .admin-nav-links a:hover:not(.active) {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.06);
        }

        .admin-nav-links a.logout-btn {
            color: var(--danger-red);
            background: rgba(248, 113, 113, 0.08);
            margin-left: 10px;
            border: 1px solid rgba(248, 113, 113, 0.15);
        }

        .admin-nav-links a.logout-btn:hover {
            background: var(--danger-red);
            color: #fff;
            box-shadow: 0 4px 15px rgba(248, 113, 113, 0.3);
        }

        .main-container {
            max-width: 1300px;
            margin: 120px auto 40px; 
            padding: 0 25px;
            box-sizing: border-box;
        }

        /* Luxury Header Title Container */
        .header-panel {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        
        .header-panel h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: 0.5px;
        }
        
        .header-panel h1 span {
            color: var(--accent-gold);
        }
        
        .header-panel p {
            margin: 8px 0 0 0; 
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Order Cards Grid */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 25px;
        }

        /* Premium Order Card Design */
        .order-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border: 1px solid var(--border-color);
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .order-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            border-color: #3b5278;
        }

        /* Room Tag */
        .room-tag {
            background: rgba(251, 191, 36, 0.1);
            color: var(--accent-gold);
            border: 1px solid rgba(251, 191, 36, 0.2);
            padding: 5px 14px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .guest-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 18px 0 4px 0;
        }

        /* Order Description Box */
        .items-box {
            background: var(--input-bg);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0 22px 0;
            border: 1px dashed var(--border-color);
        }

        .status-indicator {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            margin-bottom: 12px;
            display: block;
        }
        .status-pending { color: var(--accent-gold); }
        .status-accepted { color: var(--accent-blue); }
        .status-ready { color: var(--warning-orange); }

        /* Button Layout Inside Card */
        .btn-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            padding: 13px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            font-size: 0.9rem;
            transition: 0.2s ease;
            display: block;
            box-sizing: border-box;
        }
        
        .btn-accept { 
            background-color: transparent; 
            color: var(--accent-blue); 
            border: 1px solid var(--accent-blue); 
        }
        .btn-accept:hover { background-color: var(--accent-blue); color: #090d16; box-shadow: 0 0 12px rgba(56, 189, 248, 0.2); }

        .btn-ready { 
            background-color: transparent; 
            color: #fb923c; 
            border: 1px solid #fb923c; 
        }
        .btn-ready:hover { background-color: var(--warning-orange); color: white; box-shadow: 0 0 12px rgba(249, 115, 22, 0.2); }

        .btn-complete { 
            background-color: transparent; 
            color: #34d399; 
            border: 1px solid #34d399; 
        }
        .btn-complete:hover { background-color: var(--success-green); color: white; box-shadow: 0 0 12px rgba(16, 185, 129, 0.2); }

        /* Empty State */
        .empty-msg {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 20px;
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .empty-msg h2 {
            color: var(--accent-gold);
            margin-top: 0;
            font-size: 1.6rem;
        }

        /* Responsive Navbar Optimization */
        @media (max-width: 1150px) {
            .admin-navbar { position: static; transform: none; width: 100%; margin: 10px 0; flex-direction: column; gap: 12px; padding: 15px; border-radius: 10px; }
            .main-container { margin-top: 20px; }
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
            .header-panel {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- 🚀 Floating Glassmorphism Navbar Component -->
    <header class="admin-navbar">
        <div class="logo">PANAMA <span>CONTROL</span></div>
        <nav class="admin-nav-links">
            <a href="admin_home.php">Dashboard</a>
            <a href="register_customer.php">New Guest</a>
            <a href="customer_details.php">Guest List</a>
            <a href="kitchen_orders.php">Service</a>
            <a href="manage_items.php">Menu</a>
            <a href="manage_orders.php" class="active">Orders</a>
            <a href="admin_about.php">About</a>
            <a href="manage_feedback.php">Feedback</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <div class="main-container">

        <div class="header-panel">
            <h1>Hotel <span>Command Center</span></h1>
            <p>Live Order Management System</p>
        </div>

        <div class="grid-container">
            <?php
            // GROUP BY මගින් එකම පාරිභෝගිකයාගේ ඇණවුම් එකට ගොනු කිරීම
            $sql = "SELECT users.full_name, users.room_number, users.phone_number, orders.user_id, orders.status, 
                    GROUP_CONCAT(CONCAT(menu_items.item_name, ' (x', orders.quantity, ')') SEPARATOR ', ') as all_items,
                    MIN(orders.order_date) as order_time
                    FROM orders 
                    JOIN users ON orders.user_id = users.id 
                    JOIN menu_items ON orders.item_id = menu_items.id 
                    WHERE orders.status IN (1, 2, 3) 
                    GROUP BY orders.user_id, orders.status
                    ORDER BY order_time ASC";
            
            $res = $conn->query($sql);

            if ($res && $res->num_rows > 0):
                while($row = $res->fetch_assoc()): 
                    $uid = $row['user_id'];
                    $status = $row['status'];
                    $items = $row['all_items'];
                    $phone = $row['phone_number'];
            ?>
            <div class="order-card">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="room-tag">Room <?php echo htmlspecialchars($row['room_number']); ?></span>
                        <small style="color: var(--text-muted); font-weight: 600;">⏱️ <?php echo date('h:i A', strtotime($row['order_time'])); ?></small>
                    </div>
                    
                    <h3 class="guest-name"><?php echo htmlspecialchars($row['full_name']); ?></h3>

                    <div class="items-box">
                        <span class="status-indicator">
                            <?php 
                                if($status == 1) echo "<span class='status-pending'>edit_calendar 🟡 ලැබී ඇත (Pending)</span>";
                                if($status == 2) echo "<span class='status-accepted'>soup_kitchen 🔵 පිළියෙළ කරමින් (Accepted)</span>";
                                if($status == 3) echo "<span class='status-ready'>done_all 💡 සූදානම් (Ready)</span>";
                            ?>
                        </span>
                        <div style="font-weight: 500; line-height: 1.6; color: var(--text-main); font-size: 0.95rem;">
                            <strong style="color: var(--accent-gold);">ORDER:</strong> <?php echo htmlspecialchars($items); ?>
                        </div>
                    </div>
                </div>

                <div class="btn-stack">
                    <a href="?update_status=2&u_id=<?php echo $uid; ?>&items=<?php echo urlencode($items); ?>&phone=<?php echo $phone; ?>" 
                       class="btn btn-accept">
                        📥 Order Accept
                    </a>

                    <a href="?update_status=3&u_id=<?php echo $uid; ?>&items=<?php echo urlencode($items); ?>&phone=<?php echo $phone; ?>" 
                       class="btn btn-ready">
                        🔔 Order is Ready
                    </a>

                    <a href="?update_status=4&u_id=<?php echo $uid; ?>&items=<?php echo urlencode($items); ?>&phone=<?php echo $phone; ?>" 
                       class="btn btn-complete">
                        ✅ Order Complete
                    </a>
                </div>
            </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="empty-msg">
                    <h2>End all orders!</h2>
                    <p>Not have orders.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>
</html>