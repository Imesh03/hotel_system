<?php
include 'db_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// button Action Logic
if (isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $user_id = $_POST['user_id'];
    $action_type = $_POST['action_type'];
    $service_name = $_POST['service_name'];
    
    $msg_text = "";
    $new_status = "";

    switch ($action_type) {
        case 'received':
            $new_status = 'Received';
            $msg_text = " '" . $service_name . "' Service Request Received";
            break;
        case 'ready':
            $new_status = 'Ready';
            $msg_text = " '" . $service_name . "' Staff is on the way";
            break;
        case 'complete':
            $new_status = 'Complete';
            $msg_text = " '" . $service_name . "'  Service Completed";
            break;
    }

    // 1. service status
    $stmt1 = $conn->prepare("UPDATE services_requests SET status = ? WHERE id = ?");
    $stmt1->bind_param("si", $new_status, $request_id);
    $stmt1->execute();

    // 2. send to customer's mssge box
    $stmt2 = $conn->prepare("INSERT INTO messages (user_id, message, is_read) VALUES (?, ?, 0)");
    $stmt2->bind_param("is", $user_id, $msg_text);
    $stmt2->execute();

    header("Location: kitchen_orders.php?success=1");
    exit();
}

// Search Filter Logic
$search_query = "";
if (isset($_GET['search_phone']) && !empty($_GET['search_phone'])) {
    $phone = $conn->real_escape_string($_GET['search_phone']);
    $search_query = " WHERE u.phone_number LIKE '%$phone%' ";
}

// Search
$sql = "SELECT sr.*, u.full_name, u.nic_number, u.phone_number, u.room_number 
        FROM services_requests sr
        JOIN users u ON sr.user_id = u.id 
        $search_query 
        ORDER BY sr.id DESC";

$requests_res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request Management - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Premium Luxury Dark Theme Variables */
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
            --danger-red: #f87171;
            --table-row-hover: #1e293b;
            --nav-bg: rgba(19, 28, 46, 0.7);
        }

        body { 
            background: var(--primary-bg); 
            font-family: 'Segoe UI', Roboto, sans-serif; 
            color: var(--text-main);
            margin: 0; 
            padding: 0; 
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

        /* Active State Style (Service is active here) */
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

        .wrapper { 
            max-width: 1400px; 
            margin: 120px auto 50px; 
            padding: 0 24px; 
            box-sizing: border-box;
        }
        
        /* Header Block */
        .page-header {
            background: var(--card-bg); 
            padding: 25px 30px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex-wrap: wrap;
        }
        .page-header h1 { margin: 0; color: var(--text-main); font-size: 1.6rem; font-weight: 700; }

        /* Search Bar Component */
        .search-panel {
            background: var(--card-bg); 
            padding: 20px; 
            border-radius: 14px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); 
            margin-bottom: 25px;
            display: flex; 
            align-items: center; 
            gap: 15px; 
            border: 1px solid var(--border-color);
        }
        .search-form { display: flex; gap: 10px; width: 100%; max-width: 500px; }
        .search-input { 
            flex: 1; 
            padding: 11px 15px; 
            background: #090d16;
            border: 1px solid var(--border-color); 
            border-radius: 8px; 
            font-size: 0.9rem; 
            color: var(--text-main);
            outline: none; 
            transition: 0.2s;
        }
        .search-input::placeholder { color: #475569; }
        .search-input:focus { border-color: var(--accent-gold); }
        
        .btn-search { 
            background: transparent; 
            color: var(--accent-gold); 
            border: 1px solid var(--accent-gold); 
            padding: 10px 20px; 
            border-radius: 8px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: 0.2s; 
        }
        .btn-search:hover { 
            background: var(--accent-gold); 
            color: var(--primary-bg);
            box-shadow: 0 0 12px rgba(251, 191, 36, 0.25);
        }
        .btn-reset { 
            background: #243552; 
            color: #cbd5e1; 
            padding: 11px 15px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-size: 0.9rem; 
            font-weight: 600; 
            transition: 0.2s; 
            display: inline-flex;
            align-items: center;
        }
        .btn-reset:hover { background: #475569; }

        /* Premium Data Table */
        .table-container { 
            background: var(--card-bg); 
            border-radius: 16px; 
            padding: 25px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
            overflow-x: auto; 
            border: 1px solid var(--border-color);
        }
        .service-table { width: 100%; border-collapse: collapse; text-align: left; }
        .service-table th { 
            background: #090d16; 
            padding: 18px 15px; 
            font-size: 0.85rem; 
            color: var(--text-main); 
            font-weight: 600; 
            text-transform: uppercase; 
            border-bottom: 2px solid var(--border-color); 
        }
        .service-table td { 
            padding: 16px 15px; 
            border-bottom: 1px solid rgba(36, 53, 82, 0.5); 
            font-size: 0.9rem; 
            color: #cbd5e1; 
            vertical-align: middle; 
        }
        tbody tr:hover { background-color: var(--table-row-hover); }
        
        .cust-name { font-weight: 700; color: var(--text-main); display: block; font-size: 1rem; }
        .cust-meta { font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 4px; }
        
        .room-pill { 
            background: rgba(56, 189, 248, 0.1); 
            color: var(--accent-blue); 
            padding: 5px 10px; 
            border-radius: 6px; 
            font-weight: 700; 
            font-size: 0.85rem; 
            display: inline-block;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }
        
        /* Status Badges */
        .status-badge { 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-size: 0.75rem; 
            font-weight: 700; 
            text-transform: uppercase; 
            display: inline-block; 
            letter-spacing: 0.5px;
        }
        .status-pending { background: rgba(248, 113, 113, 0.15); color: var(--danger-red); border: 1px solid rgba(248, 113, 113, 0.3); }
        .status-received { background: rgba(251, 191, 36, 0.15); color: var(--accent-gold); border: 1px solid rgba(251, 191, 36, 0.3); }
        .status-ready { background: rgba(56, 189, 248, 0.15); color: var(--accent-blue); border: 1px solid rgba(56, 189, 248, 0.3); }
        .status-complete { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }

        /* Action Control Buttons */
        .action-flex { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-start; }
        .btn-action { 
            border: 1px solid transparent; 
            padding: 8px 14px; 
            border-radius: 6px; 
            font-size: 0.8rem; 
            font-weight: 600; 
            cursor: pointer; 
            transition: 0.2s; 
            color: white; 
            display: inline-flex; 
            align-items: center; 
            gap: 4px; 
        }
        .btn-received { background: rgba(251, 191, 36, 0.1); color: var(--accent-gold); border-color: rgba(251, 191, 36, 0.4); } 
        .btn-received:hover:not(:disabled) { background: var(--accent-gold); color: var(--primary-bg); box-shadow: 0 0 10px rgba(251, 191, 36, 0.2); }
        
        .btn-ready { background: rgba(56, 189, 248, 0.1); color: var(--accent-blue); border-color: rgba(56, 189, 248, 0.4); } 
        .btn-ready:hover:not(:disabled) { background: var(--accent-blue); color: var(--primary-bg); box-shadow: 0 0 10px rgba(56, 189, 248, 0.2); }
        
        .btn-complete { background: rgba(16, 185, 129, 0.1); color: #34d399; border-color: rgba(16, 185, 129, 0.4); } 
        .btn-complete:hover:not(:disabled) { background: var(--success-green); color: white; box-shadow: 0 0 10px rgba(16, 185, 129, 0.2); }
        
        .btn-action:disabled { background: #243552; color: #64748b; border-color: transparent; cursor: not-allowed; }

        /* Success Alert Toast */
        .success-toast {
            background: rgba(16, 185, 129, 0.1); color: #34d399; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px;
            border-left: 5px solid var(--success-green); border-top: 1px solid rgba(16, 185, 129, 0.2); border-right: 1px solid rgba(16, 185, 129, 0.2); border-bottom: 1px solid rgba(16, 185, 129, 0.2); font-weight: 500;
        }

        @media (max-width: 1100px) {
            .admin-navbar { position: static; transform: none; width: 100%; margin: 10px 0; flex-direction: column; gap: 12px; padding: 15px; border-radius: 10px; }
            .wrapper { margin-top: 20px; }
        }

        @media (max-width: 992px) {
            .service-table th, .service-table td { padding: 12px 10px; font-size: 0.85rem; }
            .action-flex { flex-direction: column; width: 100%; }
            .btn-action { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <!-- 🚀 Updated Floating Glassmorphism Navbar Component -->
    <header class="admin-navbar">
        <div class="logo">PANAMA <span>CONTROL</span></div>
        <nav class="admin-nav-links">
            <a href="admin_home.php">Dashboard</a>
            <a href="register_customer.php">New Guest</a>
            <a href="customer_details.php">Guest List</a>
            <a href="kitchen_orders.php" class="active">Service</a>
            <a href="manage_items.php">Menu</a>
            <a href="manage_orders.php">Orders</a>
            <a href="admin_about.php">About</a>
            <a href="manage_feedback.php">Feedback</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <div class="wrapper">
        
        <?php if(isset($_GET['success'])): ?>
            <div class="success-toast">
                ✅ The status was successfully updated!
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <div>
                <h1>🛎️ Customer Service Request Panel</h1>
                <p style="margin:5px 0 0; color: var(--text-muted); font-size:0.9rem;"></p>
            </div>
        </div>

        <div class="search-panel">
            <form method="GET" class="search-form">
                <input type="text" name="search_phone" placeholder="Enter customer's phone number..." value="<?php echo isset($_GET['search_phone']) ? htmlspecialchars($_GET['search_phone']) : ''; ?>" class="search-input">
                <button type="submit" class="btn-search">Search 🔍</button>
                <?php if(isset($_GET['search_phone']) && !empty($_GET['search_phone'])): ?>
                    <a href="kitchen_orders.php" class="btn-reset">Reset 🔄</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table class="service-table">
                <thead>
                    <tr>
                        <th>Guest Details </th>
                        <th>Room No</th>
                        <th>Requested Service</th>
                        <th>Cost </th>
                        <th>Date & Time</th>
                        <th>Current Status</th>
                        <th>Actions </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requests_res && $requests_res->num_rows > 0): ?>
                        <?php while ($row = $requests_res->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="cust-name"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                    <span class="cust-meta">📞 <?php echo htmlspecialchars($row['phone_number']); ?> | 🆔 <?php echo htmlspecialchars($row['nic_number']); ?></span>
                                </td>
                                <td><span class="room-pill">Room <?php echo htmlspecialchars($row['room_number']); ?></span></td>
                                <td style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['service_name']); ?></td>
                                <td style="font-weight: 700; color: var(--accent-gold);">Rs. <?php echo number_format($row['cost'], 2); ?></td>
                                <td style="font-size: 0.82rem; color: var(--text-muted);"><?php echo date('Y-m-d | h:i A', strtotime($row['requested_at'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-flex">
                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                            <input type="hidden" name="service_name" value="<?php echo htmlspecialchars($row['service_name']); ?>">
                                            <input type="hidden" name="action_type" value="received">
                                            <button type="submit" name="update_status" class="btn-action btn-received" <?php echo ($row['status'] == 'Complete') ? 'disabled' : ''; ?>>Received 📥</button>
                                        </form>

                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                            <input type="hidden" name="service_name" value="<?php echo htmlspecialchars($row['service_name']); ?>">
                                            <input type="hidden" name="action_type" value="ready">
                                            <button type="submit" name="update_status" class="btn-action btn-ready" <?php echo ($row['status'] == 'Complete') ? 'disabled' : ''; ?>>Ready 🚗</button>
                                        </form>

                                        <form method="POST" style="margin:0;">
                                            <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                            <input type="hidden" name="service_name" value="<?php echo htmlspecialchars($row['service_name']); ?>">
                                            <input type="hidden" name="action_type" value="complete">
                                            <button type="submit" name="update_status" class="btn-action btn-complete" <?php echo ($row['status'] == 'Complete') ? 'disabled' : ''; ?>>Complete ✓</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 45px; color: var(--text-muted); font-weight: 500;">මෙතෙක් කිසිදු සේවා ඉල්ලීමක් (Service Requests) ලැබී නැත.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>
</html>