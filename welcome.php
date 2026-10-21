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

// 3. Status eka inactive nam system eken logout karanava
if ($status_check['status'] == 0) {
    session_destroy();
    header("Location: login.php?msg=account_deactivated");
    exit();
}

// --- anith codes ---

$user_id = $_SESSION['user_id'];

//paribogikayage nama database eken ganna eka (User profile ekak pennana eka)
$user_name = "Guest";
$user_query = $conn->query("SELECT name FROM users WHERE id = '$user_id'");
if($user_query && $user_query->num_rows > 0) {
    $user_row = $user_query->fetch_assoc();
    $user_name = $user_row['name'];
}

// --- Unread messages check karan ek ---
$has_unread = false;
$unread_query = $conn->query("SELECT id FROM messages WHERE user_id = '$user_id' AND is_read = 0 LIMIT 1");
if ($unread_query && $unread_query->num_rows > 0) {
    $has_unread = true;
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Premium Hotel Experience</title>
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
            position: relative; /* Notification Dot ek position krima */
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

        /* Dot එක pulse (නිවෙමින් පත්තුවෙමින්) වෙන්න දාපු animation එකක් */
        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Container */
        .home-container {
            max-width: 1100px;
            margin: 50px auto;
            padding: 0 20px;
        }

        /* Hero / Welcome Panel */
        .welcome-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 50px 40px;
            border-radius: 30px;
            text-align: center;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            margin-bottom: 50px;
            animation: fadeIn 1s ease-out;
        }

        .welcome-panel h1 {
            margin: 0;
            color: #f1f5f9;
            font-size: 2.8rem;
            font-weight: 700;
        }

        .welcome-panel h1 span {
            color: #38bdf8;
            text-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
        }

        .welcome-panel p {
            color: #94a3b8;
            margin-top: 15px;
            font-size: 1.2rem;
            line-height: 1.6;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
        }

        /* Shortcut Cards */
        .menu-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 24px;
            padding: 40px 20px;
            text-align: center;
            text-decoration: none;
            color: #f1f5f9;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            display: block;
        }

        .menu-card:hover {
            transform: translateY(-12px);
            background: rgba(255, 255, 255, 0.06);
            border-color: #38bdf8;
            box-shadow: 0 20px 40px rgba(56, 189, 248, 0.15);
        }
        .card-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .menu-card:hover .card-icon {
            transform: scale(1.15) rotate(5deg);
        }

        .menu-card h3 {
            margin: 0 0 10px 0;
            font-size: 1.4rem;
            color: #f1f5f9;
            font-weight: 600;
        }

        .menu-card p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Creative Luxury Contact Panel Styles --- */
        .footer-contact-wrapper {
            margin-top: 60px;
            padding: 30px 20px;
            background: rgba(255, 255, 255, 0.01);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.04);
            text-align: center;
        }

        .contact-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 25px;
        }

        .contact-item {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 50px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            color: #38bdf8;
            border-color: rgba(56, 189, 248, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(56, 189, 248, 0.1);
        }

        .contact-icon {
            font-size: 1.2rem;
        }

        /* Specific Hover Accent for WhatsApp */
        .contact-item.whatsapp-btn:hover {
            color: #4ade80;
            border-color: rgba(74, 222, 128, 0.3);
            box-shadow: 0 5px 15px rgba(74, 222, 128, 0.1);
        }

        footer {
            color: #475569;
            font-size: 0.85rem;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <nav>
        <a href="welcome.php" style="color: #38bdf8;">Home</a>
        <a href="index.php">Order Food</a>
        <a href="get_service.php">Get Service</a>
        <a href="live_bill.php">Bill</a>
        <a href="about_us.php">About us</a>
        
        <!-- Unread messages තිබේ නම් පමණක් 'notification-dot' class එක එකතු වේ -->
        <a href="messages.php" class="<?php echo $has_unread ? 'notification-dot' : ''; ?>">Messages</a>
        
        <a href="public_feedback.php">Reviews</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="home-container">
        
        <div class="welcome-panel">
            <h1>Ayubowan , <span><?php echo htmlspecialchars($user_name); ?></span>! </h1>
            <p>Welcome to Hotel Panama. Please choose from the following amenities to make your stay more comfortable.</p>
        </div>

        <div class="dashboard-grid">
            
            <a href="index.php" class="menu-card">
                <span class="card-icon">🍽</span>
                <h3>Order Food</h3>
                <p>Enjoy delicious breakfast, lunch, and dinner all delivered to your room.</p>
            </a>

            <a href="get_service.php" class="menu-card">
                <span class="card-icon">⚙️</span>
                <h3>Get Service</h3>
                <p>Laundry, all services including room cleaning are available from here.</p>
            </a>

            <a href="live_bill.php" class="menu-card">
                <span class="card-icon">🧾</span>
                <h3>Live Bill</h3>
                <p>Check out the live bill that's currently accumulated for the services you've received.</p>
            </a>

            <a href="about_us.php" class="menu-card">
                <span class="card-icon">🧩</span>
                <h3>About Us</h3>
                <p>Check out the history, views, and beautiful scenery of our hotel.</p>
            </a>

        </div>

    </div>

    <!-- Creative Contact Panel & Footer Section -->
    <div class="footer-contact-wrapper">
        <div class="contact-container">
            <!-- Hotline / Telephone -->
            <a href="tel:0112345678" class="contact-item">
                <span class="contact-icon">📞</span>
                <span>Contact: 0112345678</span>
            </a>

            <!-- WhatsApp Chat -->
            <a href="https://wa.me/94785769838" target="_blank" class="contact-item whatsapp-btn">
                <span class="contact-icon">💬</span>
                <span>WhatsApp: +94785769838</span>
            </a>

            <!-- Email Support -->
            <a href="mailto:lasadaimesh@gmail.com" class="contact-item">
                <span class="contact-icon">✉️</span>
                <span>Email: lasadaimesh@gmail.com</span>
            </a>
        </div>
        
        <footer>
            &copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.
        </footer>
    </div>

</body>
</html>