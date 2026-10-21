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

// customerge ID eka labaganima
$id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

if ($id > 0) {
    // 3. Database eke users table eke id haraha status eka pariksha krnva
    $status_result = $conn->query("SELECT status FROM users WHERE id = '$id'");
    
    if ($status_result && $status_result->num_rows > 0) {
        $row = $status_result->fetch_assoc();
        
        // 4. Status eka 0 (Deactivated) load vnne na
        if ($row['status'] == 0) {
            echo "<div style='text-align:center; margin-top:100px; font-family:sans-serif; color: #f1f5f9; background: #0f172a; padding: 50px;'>
                    <h1 style='color:#f87171;'>Access Denied</h1>
                    <p style='color:#94a3b8;'>ඔබේ ගිණුම දැනට අක්‍රීය කර ඇත. කරුණාකර සහාය සඳහා හෝටල් කළමනාකාරීත්වය අමතන්න.</p>
                    <a href='welcome.php' style='color:#38bdf8; text-decoration: none; font-weight: bold;'>Back to Home</a>
                  </div>";
            exit(); 
        }
    }
}

// --- Unread messages check කිරීම ---
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
    <title>Hotel Menu - Order Now</title>
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

        .menu-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Category Navigation Bar */
        .category-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            position: sticky;
            top: 70px;
            z-index: 99;
        }

        .admin-btn {
            background: rgba(255, 255, 255, 0.05);
            color: #f1f5f9;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s ease;
            font-size: 0.9rem;
        }

        .admin-btn:hover {
            background: rgba(56, 189, 248, 0.1);
            border-color: #38bdf8;
            color: #38bdf8;
        }

        /* Menu Section Headers */
        .menu-title {
            margin: 40px 0 20px 0; 
            color: #38bdf8; 
            border-bottom: 2px solid rgba(56, 189, 248, 0.3); 
            display: inline-block; 
            padding-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 1.5rem;
        }
        /* Food Grid Layout */
        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        /* Glassmorphic Item Cards */
        .admin-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.4s ease;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .admin-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.06);
            border-color: #38bdf8;
            box-shadow: 0 20px 40px rgba(56, 189, 248, 0.15);
        }

        .admin-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .item-title {
            color: #f1f5f9;
            margin: 0 0 10px 0; 
            font-size: 1.15rem; 
            height: 40px; 
            overflow: hidden;
            font-weight: 600;
        }

        .item-price {
            color: #38bdf8; 
            font-weight: bold; 
            font-size: 1.25rem; 
            margin-bottom: 15px;
        }

        .order-flex {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }

        .qty-input {
            width: 70px;
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 8px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }

        .btn-add-cart {
            width: 100%;
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
        }

        .admin-card:hover .btn-add-cart {
            background: #38bdf8;
            color: #0f172a;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.4);
        }

        /* Alert Styles */
        .alert-success {
            background: rgba(22, 101, 52, 0.2);
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.3);
            padding: 15px;
            border-radius: 14px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }

        /* Real-time Popup Alert Notification Style */
        .message-popup {
            position: fixed;
            bottom: 30px;
            right: -400px;
            background: rgba(15, 23, 42, 0.95);
            border-left: 5px solid #38bdf8;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            width: 320px;
            padding: 20px;
            border-radius: 16px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 9999;
            backdrop-filter: blur(10px);
        }
        .message-popup.show { right: 30px; }
        .popup-title { font-weight: bold; color: #38bdf8; margin-bottom: 5px; }
        .popup-body { color: #94a3b8; font-size: 0.9rem; line-height: 1.4; }
    </style>
</head>
<body>

    <nav>
        <a href="welcome.php">Home</a>
        <a href="index.php" style="color: #38bdf8;">Order Food</a>
        <a href="get_service.php">Get Service</a>
        <a href="live_bill.php">Bill</a>
        <a href="about_us.php">About us</a>
        
        <!-- Unread messages තිබේ නම් පමණක් 'notification-dot' class එක එකතු වේ -->
        <a href="messages.php" class="<?php echo $has_unread ? 'notification-dot' : ''; ?>">Messages</a>
        
        <a href="public_feedback.php">Reviews</a>
        <a href="logout.php">Logout</a>
    </nav>
    <div class="menu-container">
    <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert-success">✅ Your order is success full!</div>
    <?php endif; ?>

    <div class="category-nav shadow-sm">
        <a href="#breakfast" class="admin-btn" style="text-decoration:none;">Breakfast 🍳</a>
        <a href="#lunch" class="admin-btn" style="text-decoration:none;">Lunch 🍛</a>
        <a href="#dinner" class="admin-btn" style="text-decoration:none;">Dinner 🍽</a>
        <a href="#beverages" class="admin-btn" style="text-decoration:none;">Beverages 🥤</a>
        <a href="#snacks" class="admin-btn" style="text-decoration:none;">Snacks 🍿</a>
        <a href="cart.php" class="admin-btn" style="text-decoration:none; background: #10b981; color: white; border-color: #10b981;">🛒 View Cart</a>
    </div>

    <?php
    $categories = [
        ['title' => 'Breakfast Selection', 'id' => 'breakfast', 'sql' => "SELECT * FROM menu_items WHERE category='food' AND sub_category='breakfast'"],
        ['title' => 'Lunch Specials', 'id' => 'lunch', 'sql' => "SELECT * FROM menu_items WHERE category='food' AND sub_category='lunch'"],
        ['title' => 'Dinner Menu', 'id' => 'dinner', 'sql' => "SELECT * FROM menu_items WHERE category='food' AND sub_category='dinner'"],
        ['title' => 'Refreshing Beverages', 'id' => 'beverages', 'sql' => "SELECT * FROM menu_items WHERE category='beverages'"],
        ['title' => 'Quick Snacks', 'id' => 'snacks', 'sql' => "SELECT * FROM menu_items WHERE category='snacks'"]
    ];

    foreach ($categories as $cat) {
        $result = $conn->query($cat['sql']);
        if ($result && $result->num_rows > 0) {
            echo "<h2 class='menu-title' id='{$cat['id']}'>{$cat['title']}</h2>";
            echo "<div class='food-grid'>";
            while($row = $result->fetch_assoc()) {
                $is_available = $row['is_available'];
                ?>
                <div class="admin-card" style="<?php echo !$is_available ? 'opacity: 0.4; filter: grayscale(1);' : ''; ?>">
                    <img src="uploads/<?php echo $row['image_path']; ?>" alt="food item">
                    <div style="padding: 20px; text-align: center;">
                        <h5 class="item-title"><?php echo $row['item_name']; ?></h5>
                        <p class="item-price">Rs. <?php echo number_format($row['price'], 2); ?></p>
                        <?php if($is_available): ?>
                            <form action="add_to_cart.php" method="POST" class="order-flex">
                                <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" max="20" class="qty-input">
                                <button type="submit" class="btn-add-cart">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <div style="height: 85px; display: flex; align-items: center; justify-content: center;">
                                <button class="admin-btn" style="background: #475569; color: #94a3b8; width: 100%; cursor: not-allowed; border: none;" disabled>Out of Stock</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
            echo "</div>";
        }
    }
    ?>
</div>

<div id="msgPopup" class="message-popup">
    <div class="popup-title">🔔 New Message Received</div>
    <div id="popupContent" class="popup-body"></div>
</div>

<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>

</body>
</html>