<?php
// 1. session data store
session_start();
include 'db_connection.php';

// check admin is active
/*
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
*/

// 2. Feedback Show/Hide Logic
if (isset($_GET['toggle_id']) && isset($_GET['current'])) {
    $id = intval($_GET['toggle_id']);
    $new_status = ($_GET['current'] == 1) ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE feedback SET is_visible = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    $stmt->execute();
    header("Location: manage_feedback.php?msg=updated");
    exit();
}

// 3. Feedback Delete Logic
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    
    // file delete in server
    $file_res = $conn->query("SELECT file_path FROM feedback WHERE id = $del_id");
    if($file_row = $file_res->fetch_assoc()) {
        if ($file_row['file_path']) {
            unlink("uploads/feedback/" . $file_row['file_path']);
        }
    }

    $conn->query("DELETE FROM feedback WHERE id = $del_id");
    header("Location: manage_feedback.php?msg=deleted");
    exit();
}

// get all feedbacks
$sql = "SELECT f.*, u.full_name 
        FROM feedback f 
        JOIN users u ON f.user_id = u.id 
        ORDER BY f.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Feedback Admin — Luxury Theme</title>
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
            --danger-red: #f87171;
            --input-bg: #090d16;
            --nav-bg: rgba(19, 28, 46, 0.7);
        }

        body { 
            font-family: 'Segoe UI', Roboto, sans-serif; 
            background-color: var(--primary-bg); 
            color: var(--text-main); 
            margin: 0;
            padding: 0;
        }

        /* 🚀 Floating Glassmorphism Navigation Bar */
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

        /* Active State Style (Feedback) */
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

        /* Container Main Wrapper */
        .admin-wrapper { 
            max-width: 1300px; 
            margin: 120px auto 40px; 
            padding: 0 25px; 
            box-sizing: border-box;
        }
        
        /* Luxury Header Panel */
        .page-header { 
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 40px;
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }

        .page-header h1 { 
            margin: 0; 
            font-size: 1.8rem;
            font-weight: 700; 
            color: var(--text-main); 
            letter-spacing: 0.5px; 
        }

        .page-header h1 span {
            color: var(--accent-gold);
        }
        
        .admin-btn {
            background: transparent;
            color: var(--accent-gold);
            border: 1px solid var(--accent-gold);
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: 0.3s ease;
        }

        .admin-btn:hover {
            background: var(--accent-gold);
            color: #090d16;
            box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
        }
        
        /* Alert Notification Badge */
        .alert-notify { 
            background: rgba(56, 189, 248, 0.06); 
            color: var(--accent-blue); 
            padding: 16px; 
            border-radius: 12px; 
            margin-bottom: 30px; 
            text-align: center; 
            border: 1px solid rgba(56, 189, 248, 0.2);
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Card Container View */
        .feedback-card-container { 
            display: grid; 
            grid-template-columns: 1fr; 
            gap: 25px; 
        }
        
        /* Premium Luxury Glassmorphism Feedback Card */
        .f-card { 
            background: var(--card-bg); 
            border-radius: 16px; 
            padding: 26px; 
            display: flex; 
            gap: 26px; 
            align-items: center; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); 
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            border: 1px solid var(--border-color);
            box-sizing: border-box;
        }

        .f-card:hover { 
            transform: translateY(-4px); 
            border-color: #3b5278;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }

        .f-media { position: relative; flex-shrink: 0; }
        .f-media img, .f-media video { 
            width: 125px; 
            height: 125px; 
            border-radius: 12px; 
            object-fit: cover; 
            background: var(--primary-bg);
            border: 1px solid var(--border-color);
        }

        .f-info { flex-grow: 1; }
        
        .f-user { 
            font-weight: 700; 
            font-size: 1.25rem; 
            color: var(--text-main); 
            margin-bottom: 6px; 
            display: flex; 
            align-items: center; 
            gap: 14px;
        }
        
        .f-stars { 
            color: var(--accent-gold); 
            margin-bottom: 14px; 
            font-size: 1.15rem; 
            letter-spacing: 3px; 
        }
        
        .f-msg { 
            color: #cbd5e1; 
            line-height: 1.6; 
            font-style: italic; 
            background: rgba(9, 13, 22, 0.5);
            padding: 14px 18px;
            border-radius: 10px;
            border-left: 4px solid var(--accent-gold);
            font-size: 0.95rem;
        }

        .f-actions { 
            display: flex; 
            flex-direction: column; 
            gap: 12px; 
            min-width: 210px; 
            flex-shrink: 0;
        }

        /* Action Buttons Styling */
        .btn-action { 
            text-align: center; 
            padding: 12px 14px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-size: 0.85rem; 
            font-weight: 700; 
            transition: 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-sizing: border-box;
        }

        .btn-show { 
            background: transparent; 
            color: var(--success-green); 
            border: 1px solid rgba(16, 185, 129, 0.6); 
        }
        .btn-show:hover { 
            background: var(--success-green); 
            color: #fff; 
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.25); 
        }

        .btn-hide { 
            background: transparent; 
            color: var(--accent-gold); 
            border: 1px solid rgba(251, 191, 36, 0.6); 
        }
        .btn-hide:hover { 
            background: var(--accent-gold); 
            color: #090d16; 
            box-shadow: 0 0 12px rgba(251, 191, 36, 0.25); 
        }

        .btn-delete { 
            background: transparent; 
            color: var(--danger-red); 
            border: 1px solid rgba(248, 113, 113, 0.5); 
        }
        .btn-delete:hover { 
            background: var(--danger-red); 
            color: #fff; 
            box-shadow: 0 0 12px rgba(248, 113, 113, 0.25); 
        }
        
        /* Status Badges */
        .status-badge {
            font-size: 0.72rem; 
            padding: 5px 12px; 
            border-radius: 20px; 
            text-transform: uppercase; 
            font-weight: 800;
            letter-spacing: 0.8px;
        }
        .badge-visible { 
            background: rgba(16, 185, 129, 0.15); 
            color: #34d399; 
            border: 1px solid rgba(16, 185, 129, 0.3); 
        }
        .badge-hidden { 
            background: rgba(148, 163, 184, 0.1); 
            color: var(--text-muted); 
            border: 1px solid rgba(148, 163, 184, 0.2); 
        }

        /* Responsive UI Optimizations */
        @media (max-width: 1150px) {
            .admin-navbar { position: static; transform: none; width: 100%; margin: 10px 0; flex-direction: column; gap: 12px; padding: 15px; border-radius: 10px; }
            .admin-wrapper { margin-top: 20px; }
        }

        @media (max-width: 900px) {
            .f-card { flex-direction: column; text-align: center; padding: 22px; }
            .f-actions { width: 100%; min-width: 100%; }
            .f-user { justify-content: center; }
            .f-msg { text-align: left; }
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
            <a href="manage_orders.php">Orders</a>
            <a href="admin_about.php">About</a>
            <a href="manage_feedback.php" class="active">Feedback</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <div class="admin-wrapper">
        <div class="page-header">
            <h1>Guest <span>Feedbacks Hub</span></h1>
            <a href="admin_home.php" class="admin-btn">Dashboard</a>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <div class="alert-notify">
                <?php 
                    if($_GET['msg'] == 'deleted') echo "🗑️ Feedback deleted successfully from database.";
                    if($_GET['msg'] == 'updated') echo "✅ පෙනුම (Visibility Status) සාර්ථකව යාවත්කාලීන විය.";
                ?>
            </div>
        <?php endif; ?>

        <div class="feedback-card-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="f-card">
                        <div class="f-media">
                            <?php if ($row['file_path']): ?>
                                <?php if ($row['file_type'] == 'image'): ?>
                                    <img src="uploads/feedback/<?php echo $row['file_path']; ?>" alt="Customer Image">
                                <?php else: ?>
                                    <video muted><source src="uploads/feedback/<?php echo $row['file_path']; ?>" type="video/mp4"></video>
                                    <div style="font-size:10px; color: var(--text-muted); text-align:center; margin-top: 6px; font-weight: 600;">VIDEO MEDIA</div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div style="width:125px; height:125px; background:var(--primary-bg); display:flex; align-items:center; justify-content:center; border-radius:12px; color: var(--text-muted); border: 1px dashed var(--border-color); font-size: 0.85rem; font-weight: 500;">No Media</div>
                            <?php endif; ?>
                        </div>

                        <div class="f-info">
                            <div class="f-user">
                                <?php echo htmlspecialchars($row['full_name']); ?>
                                <span class="status-badge <?php echo $row['is_visible'] == 1 ? 'badge-visible' : 'badge-hidden'; ?>">
                                    <?php echo $row['is_visible'] == 1 ? 'Public' : 'Hidden'; ?>
                                </span>
                            </div>
                            <div class="f-stars"><?php echo str_repeat("★", $row['rating']) . str_repeat("☆", 5-$row['rating']); ?></div>
                            <div class="f-msg">"<?php echo htmlspecialchars($row['message']); ?>"</div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 12px; font-weight: 600; letter-spacing: 0.3px;">SUBMITTED ON: <?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></div>
                        </div>

                        <div class="f-actions">
                            <?php if ($row['is_visible'] == 0): ?>
                                <a href="manage_feedback.php?toggle_id=<?php echo $row['id']; ?>&current=0" class="btn-action btn-show">Add to Public View</a>
                            <?php else: ?>
                                <a href="manage_feedback.php?toggle_id=<?php echo $row['id']; ?>&current=1" class="btn-action btn-hide">Hide from Public</a>
                            <?php endif; ?>

                            <a href="manage_feedback.php?delete_id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('මෙම Feedback එක සදහටම මකා දැමීමට ඔබට සහතිකද?')">Delete Feedback</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center; padding:60px 20px; background: var(--card-bg); border-radius:16px; color: var(--text-muted); border: 1px solid var(--border-color); font-weight: 500; font-size: 1rem;">තවමත් කිසිදු Feedback එකක් ලැබී නැත.</div>
            <?php endif; ?>
        </div>
    </div>

</body>
<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>
</html>