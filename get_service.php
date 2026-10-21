<?php
include 'db_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// log vela nttn login ekata yavanava
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// --- 1. Account Status Verification Block ---
// Database එකෙන් පරිශීලකයාගේ වත්මන් තත්ත්වය (Status) පරීක්ෂා කිරීම
$status_check = $conn->query("SELECT status FROM users WHERE id = '$user_id'")->fetch_assoc();

// Status එක 0 (Inactive) නම් පද්ධතියෙන් ඉවත් කර (Logout) ලොගින් පිටුවට යොමු කරයි
if ($status_check['status'] == 0) {
    session_destroy();
    header("Location: login.php?msg=account_deactivated");
    exit();
}

// --- 2. Unread messages check කිරීම (Notification Dot සඳහා) ---
$has_unread = false;
$unread_query = $conn->query("SELECT id FROM messages WHERE user_id = '$user_id' AND is_read = 0 LIMIT 1");
if ($unread_query && $unread_query->num_rows > 0) {
    $has_unread = true;
}

// 📥 service eka request krma 
if (isset($_POST['request_service'])) {
    $service_name = $conn->real_escape_string($_POST['service_name']);
    $cost = floatval($_POST['service_cost']);
    $status = "Pending";

    $stmt = $conn->prepare("INSERT INTO services_requests (user_id, service_name, cost, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $user_id, $service_name, $cost, $status);
    
    if ($stmt->execute()) {
        $success_msg = "Request Successful! 🛎️";
    } else {
        $error_msg = "යම් දෝෂයක් සිදුවී ඇත. කරුණාකර නැවත උත්සාහ කරන්න.";
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Hotel Services | Creative Hub</title>
    <style>
        /* Creative Dark Background - Consistent Style */
        body {
            margin: 0;
            padding: 0;
            background: #0f172a;
            background-image: radial-gradient(circle at top right, #1e293b, #0f172a);
            color: #f1f5f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            overflow-x: hidden;
        }

        /* Navigation Bar */
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

        /* Container */
        .customer-container {
            max-width: 1100px;
            margin: 40px auto 50px;
            padding: 0 20px;
        }

        /* Glassmorphism Welcome Header */
        .service-welcome {
            text-align: center;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 40px;
            border-radius: 25px;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        .service-welcome h1 { margin: 0; color: #38bdf8; font-size: 2.2rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .service-welcome p { color: #94a3b8; margin-top: 12px; font-size: 1.05rem; line-height: 1.6; }

        /*  Service Selection Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }

        /* Creative Info Boxes styled as Service Cards */
        .service-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 35px 25px;
            text-align: center;
            transition: all 0.4s ease;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        .service-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.06);
            border-color: #38bdf8;
            box-shadow: 0 20px 40px rgba(56, 189, 248, 0.15);
        }
        .service-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: inline-block;
            transition: 0.3s;
        }
        .service-card:hover .service-icon {
            transform: scale(1.1);
        }

        .service-card h3 { margin: 10px 0 8px; color: #f1f5f9; font-size: 1.3rem; font-weight: 600; }
        .service-price { color: #38bdf8; font-weight: 700; font-size: 1.2rem; display: block; margin-bottom: 20px; letter-spacing: 0.5px; }

        /* Request Button */
        .btn-request {
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .service-card:hover .btn-request {
            background: #38bdf8;
            color: #0f172a;
            border-color: #38bdf8;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.4);
        }

        /*  Modern Alert Styles */
        .alert {
            padding: 15px 25px;
            border-radius: 14px;
            margin-bottom: 30px;
            font-weight: 600;
            text-align: center;
            backdrop-filter: blur(10px);
        }
        .alert-success { background: rgba(22, 101, 52, 0.2); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.3); }
        .alert-error { background: rgba(153, 27, 27, 0.2); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.3); }
    </style>
</head>
<body>

    <nav>
        <a href="welcome.php">Home</a>
        <a href="index.php">Order Food</a>
        <a href="get_service.php" style="color: #38bdf8;">Get Service</a>
        <a href="live_bill.php">Bill</a>
        <a href="about_us.php">About us</a>
        
        <!-- Unread messages තිබේ නම් පමණක් 'notification-dot' class එක එකතු වේ -->
        <a href="messages.php" class="<?php echo $has_unread ? 'notification-dot' : ''; ?>">Messages</a>
        
        
        <a href="public_feedback.php">Reviews</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="customer-container">
        
        <div class="service-welcome">
            <h1>Luxury Guest Service Hub 🛎️</h1>
            <p>You can choose and request any extra services you want from below. All charges will be added to the final bill.</p>
        </div>

        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="services-grid">
            
            <div class="service-card">
                <span class="service-icon">🧺</span>
                <h3>Laundry Service</h3>
                <span class="service-price">Rs. ____</span>
                <form method="POST">
                    <input type="hidden" name="service_name" value="Laundry Service">
                    <input type="hidden" name="service_cost" value="00.00">
                    <button type="submit" name="request_service" class="btn-request">Request Now</button>
                </form>
            </div>

            <div class="service-card">
                <span class="service-icon">🧹</span>
                <h3>Room Cleaning</h3>
                <span class="service-price">Rs. 0.00 (Free)</span>
                <form method="POST">
                    <input type="hidden" name="service_name" value="Room Cleaning">
                    <input type="hidden" name="service_cost" value="0.00">
                    <button type="submit" name="request_service" class="btn-request">Request Now</button>
                </form>
            </div>
            
            <div class="service-card">
                <span class="service-icon">🔌</span>
                <h3>Ironing Service</h3>
                <span class="service-price">Rs. ____</span>
                <form method="POST">
                    <input type="hidden" name="service_name" value="Ironing Service">
                    <input type="hidden" name="service_cost" value="00.00">
                    <button type="submit" name="request_service" class="btn-request">Request Now</button>
                </form>
            </div>

            <div class="service-card">
                <span class="service-icon">🚗</span>
                <h3>Transport</h3>
                <span class="service-price">Rs. ____</span>
                <form method="POST">
                    <input type="hidden" name="service_name" value="Luxury Transport Service">
                    <input type="hidden" name="service_cost" value="00.00">
                    <button type="submit" name="request_service" class="btn-request">Request Now</button>
                </form>
            </div>

            <div class="service-card">
                <span class="service-icon">🛏️</span>
                <h3>Extra Towels / Bedding</h3>
                <span class="service-price">Rs. ____</span>
                <form method="POST">
                    <input type="hidden" name="service_name" value="Extra Towels & Bedding">
                    <input type="hidden" name="service_cost" value="00.00">
                    <button type="submit" name="request_service" class="btn-request">Request Now</button>
                </form>
            </div>

            <div class="service-card">
                <span class="service-icon">🏊‍♂️</span>
                <h3>Pool Side Service</h3>
                <span class="service-price">Rs. ____</span>
                <form method="POST">
                    <input type="hidden" name="service_name" value="Pool Side Floating Setup">
                    <input type="hidden" name="service_cost" value="00.00">
                    <button type="submit" name="request_service" class="btn-request">Request Now</button>
                </form>
            </div>

        </div>

    </div>

<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>
</body>
</html>