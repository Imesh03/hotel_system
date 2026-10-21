<?php 
include 'db_connection.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Delete part
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    
    // Folder eken Image delete krna eka
    $res = $conn->query("SELECT image_path FROM menu_items WHERE id=$id");
    if($row = $res->fetch_assoc()){
        if(!empty($row['image_path']) && file_exists("uploads/".$row['image_path'])){
            unlink("uploads/".$row['image_path']);
        }
    }
    
    $conn->query("DELETE FROM menu_items WHERE id=$id");
    header("Location: manage_items.php?msg=deleted");
    exit();
}

// 2. Availability changing(In Stock/Out of Stock) 
if(isset($_GET['status'])){
    $id = intval($_GET['status']);
    $val = intval($_GET['val']);
    $conn->query("UPDATE menu_items SET is_available=$val WHERE id=$id");
    header("Location: manage_items.php");
    exit();
}

$items = $conn->query("SELECT * FROM menu_items ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items - Admin</title>
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

        /* Active State Style (Manage Menu / Items is active here) */
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

        /* Main Container Setup */
        .manage-container {
            max-width: 1400px; 
            margin: 120px auto 50px; 
            padding: 0 24px; 
            box-sizing: border-box;
        }

        /* Top Action Bar Setup */
        .action-bar {
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
            gap: 15px;
        }

        .action-bar h2 {
            margin: 0;
            color: var(--text-main);
            font-size: 1.6rem;
            font-weight: 700;
        }

        .add-new-btn {
            background: transparent;
            color: var(--accent-gold);
            border: 1px solid var(--accent-gold);
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s ease;
        }
        .add-new-btn:hover {
            background: var(--accent-gold);
            color: var(--primary-bg);
            box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
        }

        /* Premium Data Table */
        .table-responsive-wrapper {
            background: var(--card-bg); 
            border-radius: 16px; 
            padding: 25px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
            overflow-x: auto; 
            border: 1px solid var(--border-color);
        }

        .admin-table {
            width: 100%; 
            border-collapse: collapse; 
            text-align: left;
        }

        .admin-table th {
            background: #090d16; 
            padding: 18px 15px; 
            font-size: 0.85rem; 
            color: var(--text-main); 
            font-weight: 600; 
            text-transform: uppercase; 
            border-bottom: 2px solid var(--border-color); 
        }

        .admin-table td {
            padding: 16px 15px; 
            border-bottom: 1px solid rgba(36, 53, 82, 0.5); 
            font-size: 0.9rem; 
            color: #cbd5e1; 
            vertical-align: middle; 
        }

        tbody tr:hover { 
            background-color: var(--table-row-hover); 
        }

        /* Out of Stock Row Styling */
        .out-of-stock-row {
            background-color: rgba(248, 113, 113, 0.02);
        }
        .out-of-stock-row td {
            color: var(--text-muted);
        }

        /* Thumbnail Image Styling */
        .img-wrapper {
            width: 55px;
            height: 55px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            background: #090d16;
            border: 1px solid var(--border-color);
        }
        .img-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Custom Modern Badges */
        .status-badge-pill {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-right: 8px;
            letter-spacing: 0.5px;
        }
        .badge-in-stock { 
            background: rgba(16, 185, 129, 0.15); 
            color: #34d399; 
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .badge-out-stock { 
            background: rgba(248, 113, 113, 0.15); 
            color: var(--danger-red); 
            border: 1px solid rgba(248, 113, 113, 0.3);
        }

        .status-toggle-link {
            font-size: 0.82rem;
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }
        .status-toggle-link:hover { text-decoration: underline; color: #7dd3fc; }

        /* Action Buttons Grid */
        .btn-action-group {
            display: flex;
            gap: 8px;
        }

        .action-link-btn {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
        }
        .btn-action-edit {
            background: rgba(56, 189, 248, 0.1);
            color: var(--accent-blue);
            border: 1px solid rgba(56, 189, 248, 0.4);
        }
        .btn-action-edit:hover { background: var(--accent-blue); color: var(--primary-bg); box-shadow: 0 0 10px rgba(56, 189, 248, 0.2); }

        .btn-action-delete {
            background: rgba(248, 113, 113, 0.1);
            color: var(--danger-red);
            border: 1px solid rgba(248, 113, 113, 0.4);
        }
        .btn-action-delete:hover { background: var(--danger-red); color: #fff; box-shadow: 0 0 10px rgba(248, 113, 113, 0.2); }

        /* Success Message Alert */
        .success-toast {
            background: rgba(16, 185, 129, 0.1); color: #34d399; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px;
            border-left: 5px solid var(--success-green); border-top: 1px solid rgba(16, 185, 129, 0.2); border-right: 1px solid rgba(16, 185, 129, 0.2); border-bottom: 1px solid rgba(16, 185, 129, 0.2); font-weight: 500;
        }

        @media (max-width: 1100px) {
            .admin-navbar { position: static; transform: none; width: 100%; margin: 10px 0; flex-direction: column; gap: 12px; padding: 15px; border-radius: 10px; }
            .manage-container { margin-top: 20px; }
        }

        @media (max-width: 768px) {
            .action-bar { flex-direction: column; gap: 15px; text-align: center; }
            .admin-table th, .admin-table td { padding: 12px 10px; font-size: 0.85rem; }
            .btn-action-group { flex-direction: column; gap: 5px; }
            .action-link-btn { padding: 6px 10px; font-size: 0.8rem; text-align: center; justify-content: center; }
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
            <a href="manage_items.php" class="active">Menu</a>
            <a href="manage_orders.php">Orders</a>
            <a href="admin_about.php">About</a>
            <a href="manage_feedback.php">Feedback</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <div class="manage-container">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="success-toast">
                ✅ Action Completed Successfully!
            </div>
        <?php endif; ?>

        <div class="action-bar">
            <h2>Menu Management 🍔</h2>
            <a href="add_item.php" class="add-new-btn">+ Add New Foods</a>
        </div>

        <div class="table-responsive-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($items && $items->num_rows > 0): ?>
                        <?php while($row = $items->fetch_assoc()): ?>
                        <tr class="<?php echo $row['is_available'] ? '' : 'out-of-stock-row'; ?>">
                            <td>
                                <div class="img-wrapper">
                                    <?php if(!empty($row['image_path']) && file_exists("uploads/".$row['image_path'])): ?>
                                    <img src="uploads/<?php echo $row['image_path']; ?>" class="img-thumb" alt="Item Image">
                                    <?php else: ?>
                                        <div style="height:100%; display:flex; align-items:center; justify-content:center; color: var(--text-muted); font-size:10px;">No Img</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td>
                                <strong style="font-size: 1.05rem; color: var(--text-main);"><?php echo htmlspecialchars($row['item_name']); ?></strong><br>
                                <?php if(isset($row['sub_category']) && $row['sub_category'] != 'none' && !empty($row['sub_category'])): ?>
                                    <small style="color: var(--text-muted); font-size: 0.85rem;"><?php echo htmlspecialchars($row['sub_category']); ?></small>
                                <?php endif; ?>
                            </td>
                            
                            <td><span style="font-size: 0.95rem;"><?php echo ucfirst(htmlspecialchars($row['category'])); ?></span></td>
                            
                            <td style="font-weight: 700; color: var(--accent-gold);">Rs. <?php echo number_format($row['price'], 2); ?></td>
                            
                            <td>
                                <?php if($row['is_available']): ?>
                                    <span class="status-badge-pill badge-in-stock">In Stock</span>
                                    <a href="manage_items.php?status=<?php echo $row['id']; ?>&val=0" class="status-toggle-link">Mark Out</a>
                                <?php else: ?>
                                    <span class="status-badge-pill badge-out-stock">Out of Stock</span>
                                    <a href="manage_items.php?status=<?php echo $row['id']; ?>&val=1" class="status-toggle-link">Mark In</a>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <div class="btn-action-group">
                                    <a href="edit_item.php?id=<?php echo $row['id']; ?>" class="action-link-btn btn-action-edit">Edit</a>
                                    <a href="manage_items.php?delete=<?php echo $row['id']; ?>" class="action-link-btn btn-action-delete" onclick="return confirm('මෙම ආහාරය මෙනුවෙන් සදහටම මකා දැමීමට ඔබට සහතිකද?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 45px; color: var(--text-muted); font-weight: 500;">මෙනුවේ කිසිදු ආහාර අයිතමයක් ඇතුලත් කර නැත.</td>
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