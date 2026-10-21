<?php 
include 'db_connection.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//  Date Filtering Logic
$selected_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');
$display_date_text = ($selected_date == date('Y-m-d')) ? "Today" : $selected_date;

// Active & Not Checked-out customers count
$guest_res = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 1");
$total_guests = $guest_res ? $guest_res->fetch_assoc()['total'] : 0;

// 2. orders table eke 'order_date' anuva edina anaum gnana & adayama
$order_analytics_res = $conn->query("SELECT COUNT(id) as total_count, SUM(total_price) as total_sales FROM orders WHERE DATE(order_date) = '$selected_date'");
$order_analytics_row = $order_analytics_res ? $order_analytics_res->fetch_assoc() : null;

$today_orders_count = ($order_analytics_row && $order_analytics_row['total_count']) ? $order_analytics_row['total_count'] : 0;
$today_order_sales = ($order_analytics_row && $order_analytics_row['total_sales']) ? $order_analytics_row['total_sales'] : 0.00;

// 3. extra_orders table eke 'amount' agaya thorgth dinata
$extra_order_res = $conn->query("SELECT SUM(amount) as total_extra FROM extra_orders WHERE DATE(order_date) = '$selected_date'");
$extra_order_row = $extra_order_res ? $extra_order_res->fetch_assoc() : null;
$today_extra_sales = ($extra_order_row && $extra_order_row['total_extra']) ? $extra_order_row['total_extra'] : 0.00;

// 4. checkout nouna siyluma customerslagen adayama
$active_room_revenue_res = $conn->query("SELECT SUM(price_per_session) as total_active_room_sales FROM users WHERE status = 1");
$active_room_revenue_row = $active_room_revenue_res ? $active_room_revenue_res->fetch_assoc() : null;
$current_active_room_sales = ($active_room_revenue_row && $active_room_revenue_row['total_active_room_sales']) ? $active_room_revenue_row['total_active_room_sales'] : 0.00;

//  sampurna adayama (Orders + Extra Orders + Checked-in Room Charges)
$total_gross_revenue = $today_order_sales + $today_extra_sales + $current_active_room_sales;

// 5. Admin Approve nokrpu aluth Feedback pramanaya
$feedback_check = $conn->query("SELECT COUNT(*) as total FROM feedback WHERE is_visible = 0");
$unread_msg = $feedback_check ? $feedback_check->fetch_assoc()['total'] : 0;

// 6. edina siyaluma food orders
$recent_orders = $conn->query("SELECT o.id, o.status, o.total_price, o.order_date, u.room_number, u.full_name 
                               FROM orders o 
                               JOIN users u ON o.user_id = u.id 
                               WHERE DATE(o.order_date) = '$selected_date'
                               ORDER BY o.id DESC LIMIT 5");

// ️ 7. [FIXED] services_requests table ekt anuva venas karana lada sql
$recent_services = $conn->query("SELECT s.id, s.service_name, s.status, s.requested_at, u.room_number, u.full_name 
                                 FROM services_requests s 
                                 JOIN users u ON s.user_id = u.id 
                                 WHERE DATE(s.requested_at) = '$selected_date'
                                 ORDER BY s.id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Premium Luxury Control</title>
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
            --accent-purple: #a855f7;
            --accent-gold: #fbbf24;
            --success-green: #10b981;
            --warning-orange: #f97316;
            --danger-red: #f87171;
            --card-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            --nav-bg: rgba(19, 28, 46, 0.7);
        }

        body { 
            background: var(--primary-bg); 
            font-family: 'Segoe UI', Roboto, sans-serif; 
            margin: 0; 
            padding: 0; 
            color: var(--text-main);
        }

        /*  NEW: Floating Glassmorphism Navigation Bar Styles */
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
            display: flex;
            align-items: center;
            gap: 8px;
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

        /* Active State Style */
        .admin-nav-links a.active {
            color: #090d16;
            background: var(--accent-gold);
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        /* Hover States */
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

        /* Dashboard Main Layout Wrapper */
        .dashboard-wrapper { 
            max-width: 1350px;
            margin: 125px auto 50px; 
            padding: 0 24px; 
            box-sizing: border-box;
        }

        .welcome-section {
            background: var(--card-bg); 
            padding: 25px 30px; 
            border-radius: 16px; 
            box-shadow: var(--card-shadow); 
            margin-bottom: 25px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex-wrap: wrap; 
            gap: 20px;
            border: 1px solid var(--border-color);
        }
        .welcome-text h1 { margin: 0; color: var(--text-main); font-size: 1.7rem; font-weight: 700; }
        .welcome-text h1 span { color: var(--accent-gold); }
        .welcome-text p { margin: 6px 0 0; color: var(--text-muted); font-size: 0.95rem; }

        .filter-panel {
            background: var(--card-bg); 
            padding: 18px 30px; 
            border-radius: 14px; 
            box-shadow: var(--card-shadow); 
            margin-bottom: 30px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            border: 1px solid var(--border-color);
        }
        .filter-title { font-size: 0.95rem; font-weight: 600; color: var(--text-muted); }
        .filter-title span { color: var(--accent-blue); font-weight: 700; }
        .filter-form { display: flex; align-items: center; gap: 10px; }
        .filter-input { 
            background: var(--primary-bg); 
            border: 1px solid var(--border-color); 
            padding: 10px 15px; 
            border-radius: 8px; 
            font-size: 0.9rem; 
            color: var(--text-main); 
            outline: none; 
        }
        .filter-btn { 
            background: var(--accent-blue); 
            color: #0f172a; 
            border: none; 
            padding: 10px 20px; 
            border-radius: 8px; 
            font-weight: 700; 
            cursor: pointer; 
            transition: 0.2s;
        }
        .filter-btn:hover { background: #7dd3fc; }
        .reset-btn { 
            background: rgba(255,255,255,0.05); 
            color: var(--text-muted); 
            text-decoration: none; 
            padding: 10px 18px; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 0.85rem; 
            border: 1px solid var(--border-color);
            transition: 0.2s;
        }
        .reset-btn:hover { background: rgba(255,255,255,0.1); color: var(--text-main); }
        
        .live-status-pill { 
            background: rgba(16, 185, 129, 0.1); 
            color: var(--success-green); 
            padding: 8px 16px; 
            border-radius: 50px; 
            font-weight: 600; 
            font-size: 0.85rem; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .pulse-indicator { width: 8px; height: 8px; background: var(--success-green); border-radius: 50%; animation: live-pulse 1.5s infinite; }
        @keyframes live-pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.5); opacity: 0.4; } 100% { transform: scale(1); opacity: 1; } }

        .analytics-matrix { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-glow-card { 
            background: var(--card-bg); 
            padding: 25px; 
            border-radius: 18px; 
            box-shadow: var(--card-shadow); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            transition: all 0.3s ease; 
            border: 1px solid var(--border-color);
        }
        .stat-glow-card:hover { transform: translateY(-5px); border-color: #475569; }
        .card-info .label { font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .card-info .value { font-size: 1.55rem; font-weight: 700; color: var(--text-main); margin-top: 6px; display: block; }
        .card-info .sub-desc { font-size: 0.78rem; color: var(--text-muted); margin-top: 6px; display: block; }
        
        .count-mini-badge { display: inline-block; background: rgba(56, 189, 248, 0.1); color: var(--accent-blue); padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; margin-top: 6px; border: 1px solid rgba(56, 189, 248, 0.15); }
        .revenue-breakdown { font-size: 0.75rem; color: var(--text-muted); margin-top: 8px; line-height: 1.4; display: block; }

        .layout-split { display: grid; grid-template-columns: 1.8fr 1.8fr 1.2fr; gap: 25px; }
        .content-card-block { background: var(--card-bg); border-radius: 18px; padding: 25px; box-shadow: var(--card-shadow); border: 1px solid var(--border-color); }
        
        .block-header-title { 
            margin-top: 0; 
            margin-bottom: 20px; 
            font-size: 1.15rem; 
            color: var(--text-main); 
            font-weight: 700; 
            border-bottom: 1px solid var(--border-color); 
            padding-bottom: 12px; 
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .premium-data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .premium-data-table th { background: var(--primary-bg); padding: 12px 14px; font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; border-radius: 4px; }
        .premium-data-table td { padding: 14px 14px; border-bottom: 1px solid var(--border-color); font-size: 0.88rem; color: var(--text-main); }
        
        .room-badge { background: rgba(251, 191, 36, 0.1); color: var(--accent-gold); padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; border: 1px solid rgba(251, 191, 36, 0.15); }
        .service-badge { background: rgba(168, 85, 247, 0.1); color: var(--accent-purple); padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 0.85rem; border: 1px solid rgba(168, 85, 247, 0.15); }

        /*  Status Badges Styles */
        .status-pill-ui { 
            padding: 5px 12px; 
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block; 
        }
        .pill-pending { background: rgba(251, 191, 36, 0.12); color: var(--accent-gold); border: 1px solid rgba(251, 191, 36, 0.2); }
        .pill-received { background: rgba(56, 189, 248, 0.12); color: var(--accent-blue); border: 1px solid rgba(56, 189, 248, 0.2); }
        .pill-ready { background: rgba(249, 115, 22, 0.12); color: #fb923c; border: 1px solid rgba(249, 115, 22, 0.2); }
        .pill-complete { background: rgba(16, 185, 129, 0.12); color: var(--success-green); border: 1px solid rgba(16, 185, 129, 0.2); }

        .action-button-matrix { display: grid; grid-template-columns: 1fr; gap: 12px; }
        .action-shortcut-card { 
            background: var(--primary-bg); 
            border: 1px solid var(--border-color); 
            padding: 16px 12px; 
            text-align: center; 
            border-radius: 12px; 
            text-decoration: none; 
            color: var(--text-main); 
            font-weight: 600; 
            font-size: 0.85rem; 
            transition: all 0.2s ease; 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .action-shortcut-card:hover { 
            background: var(--accent-gold); 
            color: #0f172a; 
            border-color: var(--accent-gold); 
            transform: translateY(-2px); 
        }
        .action-shortcut-card .emoji { font-size: 1.3rem; }

        @media (max-width: 1100px) {
            .layout-split { grid-template-columns: 1fr; }
            .filter-panel { flex-direction: column; align-items: flex-start; gap: 15px; }
            .filter-form { width: 100%; }
            .filter-input { flex-grow: 1; }
            .admin-navbar { position: static; transform: none; width: 100%; margin: 10px 0; flex-direction: column; gap: 12px; padding: 15px; border-radius: 10px; }
            .dashboard-wrapper { margin-top: 20px; }
        }
    </style>
</head>
<body>
    <!--  Modified Floating Navbar Component -->
    <header class="admin-navbar">
        <div class="logo">PANAMA <span>CONTROL</span></div>
        <nav class="admin-nav-links">
            <a href="admin_home.php" class="active">Dashboard</a>
            <a href="register_customer.php">New Guest</a>
            <a href="customer_details.php">Guest List</a>
            <a href="kitchen_orders.php">Service</a>
            <a href="manage_items.php">Menu</a>
            <a href="manage_orders.php">Orders</a>
            <a href="admin_about.php">About</a>
            <a href="manage_feedback.php">Feedback</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <div class="dashboard-wrapper">
        
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>Welcome, <span>Administrator</span></h1>
                <p>Hotel Panama system control</p>
            </div>
            <div class="live-status-pill">
                <div class="pulse-indicator"></div> Live Monitoring Active
            </div>
        </div>

        <div class="filter-panel">
            <div class="filter-title">
                <h2>Data Shows: <span><?php echo $display_date_text; ?></span></h2>
            </div>
            <form method="GET" class="filter-form">
                <input type="date" name="filter_date" value="<?php echo $selected_date; ?>" class="filter-input" required>
                <button type="submit" class="filter-btn">Filter</button>
                <?php if(isset($_GET['filter_date'])): ?>
                    <a href="admin_home.php" class="reset-btn">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="analytics-matrix">
            <div class="stat-glow-card">
                <div class="card-info">
                    <span class="label">Active Guests</span>
                    <span class="value"><?php echo $total_guests; ?></span>
                    <span class="sub-desc">Current Guests</span>
                </div>
                <div style="font-size: 2rem;">👥</div>
            </div>
            
            <div class="stat-glow-card">
                <div class="card-info">
                    <span class="label">Food Orders Revenue</span>
                    <span class="value">LKR <?php echo number_format($today_order_sales, 2); ?></span>
                    <span class="count-mini-badge">Orders: <?php echo $today_orders_count; ?></span>
                </div>
                <div style="font-size: 2rem;">🍲</div>
            </div>

            <div class="stat-glow-card" style="border-left: 4px solid var(--accent-gold);">
                <div class="card-info">
                    <span class="label" style="color: var(--accent-gold);">Total Gross Revenue</span>
                    <span class="value" style="color: var(--accent-gold);">LKR <?php echo number_format($total_gross_revenue, 2); ?></span>
                    <span class="revenue-breakdown">
                        🔑 Rooms (Active): Rs. <?php echo number_format($current_active_room_sales, 0); ?><br>
                        🍽️ Food Orders: Rs. <?php echo number_format($today_order_sales, 0); ?><br>
                        ✨ Extra Orders: Rs. <?php echo number_format($today_extra_sales, 0); ?>
                    </span>
                </div>
                <div style="font-size: 2rem;">💰</div>
            </div>
            
            <div class="stat-glow-card">
                <div class="card-info">
                    <span class="label">New Feedbacks</span>
                    <span class="value" style="color: var(--danger-red);"><?php echo $unread_msg; ?></span>
                    <span class="sub-desc">Unapproved responses</span>
                </div>
                <div style="font-size: 2rem;">⭐</div>
            </div>
        </div>
        
        <div class="layout-split">
            <!-- BLOCK 1: Food Orders Table -->
            <div class="content-card-block">
                <div class="block-header-title">
                    <span>📅 Food Orders</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="premium-data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Guest Name</th>
                                <th>Room</th>
                                <th>Bill Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recent_orders && $recent_orders->num_rows > 0): ?>
                                <?php while($ord = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $ord['id']; ?></strong></td>
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($ord['full_name']); ?></td>
                                    <td><span class="room-badge">Room <?php echo htmlspecialchars($ord['room_number']); ?></span></td>
                                    <td style="font-weight: 700; color: var(--accent-blue);">Rs. <?php echo number_format($ord['total_price'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $db_status = trim(strtolower($ord['status']));
                                        if ($db_status == 'pending') {
                                            echo '<span class="status-pill-ui pill-pending">Pending</span>';
                                        } elseif ($db_status == 'processing' || $db_status == 'received') {
                                            echo '<span class="status-pill-ui pill-received">Received</span>';
                                        } elseif ($db_status == 'complete' || $db_status == 'completed') {
                                            echo '<span class="status-pill-ui pill-complete">Completed</span>';
                                        } else {
                                            echo '<span class="status-pill-ui pill-pending">' . ucfirst(htmlspecialchars($ord['status'])) . '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 45px; color: var(--text-muted);">No Orders.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BLOCK 2: Custom Service Requests Table -->
            <div class="content-card-block" style="border-top: 3px solid var(--accent-purple);">
                <div class="block-header-title">
                    <span>🛎️ Requested Services</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="premium-data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Guest Name</th>
                                <th>Room</th>
                                <th>Service</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recent_services && $recent_services->num_rows > 0): ?>
                                <?php while($srv = $recent_services->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo $srv['id']; ?></strong></td>
                                    <td style="font-weight: 600;"><?php echo htmlspecialchars($srv['full_name']); ?></td>
                                    <td><span class="room-badge">Room <?php echo htmlspecialchars($srv['room_number']); ?></span></td>
                                    <td><span class="service-badge"><?php echo htmlspecialchars($srv['service_name']); ?></span></td>
                                    <td>
                                        <?php 
                                        $srv_status = trim(strtolower($srv['status']));
                                        if ($srv_status == 'pending') {
                                            echo '<span class="status-pill-ui pill-pending">Pending</span>';
                                        } elseif ($srv_status == 'received') {
                                            echo '<span class="status-pill-ui pill-received">Received</span>';
                                        } elseif ($srv_status == 'ready') {
                                            echo '<span class="status-pill-ui pill-ready">Ready</span>';
                                        } elseif ($srv_status == 'complete' || $srv_status == 'completed') {
                                            echo '<span class="status-pill-ui pill-complete">Complete</span>';
                                        } else {
                                            echo '<span class="status-pill-ui pill-pending">' . ucfirst(htmlspecialchars($srv['status'])) . '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 45px; color: var(--text-muted);">No Service Requests.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BLOCK 3: Quick Controls -->
            <div class="content-card-block">
                <div class="block-header-title">⚡ Quick Controls</div>
                <div class="action-button-matrix">
                    <a href="register_customer.php" class="action-shortcut-card">
                        <span class="emoji">🛎️</span> Register Guest
                    </a>
                    <a href="manage_items.php" class="action-shortcut-card">
                        <span class="emoji">➕</span> Add Food Item
                    </a>
                    <a href="manage_orders.php" class="action-shortcut-card">
                        <span class="emoji">📋</span> View Orders
                    </a>
                    <a href="manage_feedback.php" class="action-shortcut-card">
                        <span class="emoji">💬</span> Approvals
                    </a>
                    <a href="admin_profile.php" class="action-shortcut-card">
                        <span class="emoji">👤</span> Admin Profile
                    </a>
                </div>
            </div>

        </div>
    </div>
</body>
<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>
</html>