<?php
include 'db_connection.php';

// URL එකෙන් පාරිභෝගිකයාගේ ID එක ලබා ගැනීම (උදා: view_full_bill.php?id=5)
if (!isset($_GET['id'])) {
    die("පාරිභෝගිකයාගේ ID එක සඳහන් කර නැත! (Please provide a User ID in the URL)");
}

$user_id = intval($_GET['id']);
$hotel_name = "Luxury Hotel & Resort"; 

// 1. අමතර ගාස්තු එකතු කිරීම (Add) 
if (isset($_POST['add_extra'])) {
    $desc = $conn->real_escape_string($_POST['extra_desc']);
    $amt = floatval($_POST['extra_amount']);
    
    $query = "INSERT INTO extra_orders (user_id, description, amount, order_date) VALUES ('$user_id', '$desc', $amt, NOW())";
    
    if ($conn->query($query)) {
        header("Location: view_full_bill.php?id=" . $user_id); 
        exit();
    }
}

//  2. අමතර ගාස්තු මකා දැමීම (Delete)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM extra_orders WHERE id = $delete_id AND user_id = '$user_id'");
    
    header("Location: view_full_bill.php?id=" . $user_id);
    exit();
}

// 3. පාරිභෝගික දත්ත ලබා ගැනීම
$user_query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
$user_res = $conn->query($user_query);
$user = $user_res->fetch_assoc();

if (!$user) {
    die("මෙම ID එකට අදාළ පාරිභෝගිකයෙකු හමු නොවීය.");
}

// 4. කාමර ගාස්තුව ගණනය කිරීම
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
    <title>Master Bill - <?php echo htmlspecialchars($user['full_name']); ?></title>
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

        /* Floating Glassmorphism Navigation Bar */
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
            max-width: 950px; 
            margin: 120px auto 40px; 
            padding: 0 20px; 
            box-sizing: border-box;
        }

        /* Master Bill Card */
        .bill-card { 
            background: var(--card-bg); 
            border-radius: 20px; 
            padding: 40px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.4); 
            border: 1px solid var(--border-color); 
        }

        /* Action Box (Add Extra Services) */
        .action-box { 
            background: rgba(9, 13, 22, 0.6); 
            padding: 22px; 
            border-radius: 14px; 
            border: 1px solid var(--border-color); 
            margin-bottom: 30px; 
        }

        .action-box h4 {
            margin: 0 0 12px 0; 
            color: var(--accent-gold);
            font-size: 1.05rem;
            letter-spacing: 0.5px;
        }

        .form-row { display: flex; gap: 12px; }
        
        .form-row input { 
            padding: 12px 16px; 
            background: var(--input-bg);
            border: 1px solid var(--border-color); 
            border-radius: 8px; 
            color: var(--text-main);
            font-size: 0.9rem;
            outline: none;
            flex: 1; 
            transition: 0.3s ease;
        }

        .form-row input:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.15);
        }

        .btn-add { 
            background: var(--accent-gold); 
            color: #090d16; 
            border: none; 
            padding: 12px 24px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 700; 
            font-size: 0.9rem;
            transition: 0.3s ease;
            text-transform: uppercase;
        }

        .btn-add:hover {
            box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
            transform: translateY(-1px);
        }

        /* Invoice Header */
        .invoice-header {
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            border-bottom: 1px solid var(--border-color); 
            padding-bottom: 20px; 
            margin-bottom: 30px;
        }

        .invoice-header h1 {
            margin: 0; 
            color: var(--text-main);
            font-size: 1.8rem;
            letter-spacing: 1px;
        }

        .invoice-header p {
            margin: 5px 0 0;
            color: var(--accent-gold);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .customer-meta {
            text-align: right;
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .customer-meta strong {
            color: var(--text-main);
        }

        /* Table Design */
        table { width: 100%; border-collapse: collapse; margin-bottom: 35px; }
		th { 
            text-align: left; 
            background: rgba(9, 13, 22, 0.5); 
            padding: 14px; 
            border-bottom: 2px solid var(--border-color); 
            color: var(--accent-blue); 
            font-size: 0.85rem; 
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        
        td { 
            padding: 16px 14px; 
            border-bottom: 1px solid var(--border-color); 
            color: var(--text-main);
            font-size: 0.95rem;
        }
        
        .price-col { text-align: right; font-weight: 700; color: var(--text-main); }
        
        .btn-delete { 
            color: var(--danger-red); 
            text-decoration: none; 
            border: 1px solid rgba(248, 113, 113, 0.4); 
            padding: 5px 12px; 
            border-radius: 6px; 
            font-size: 0.8rem; 
            font-weight: 600;
            transition: 0.2s ease;
        }

        .btn-delete:hover {
            background: var(--danger-red);
            color: #fff;
            box-shadow: 0 0 10px rgba(248, 113, 113, 0.3);
        }

        /* Total Banner */
        .total-banner { 
            background: rgba(9, 13, 22, 0.8); 
            border: 1px solid var(--border-color);
            color: var(--text-main); 
            padding: 24px 30px; 
            border-radius: 14px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }

        .total-banner h2 {
            margin: 0;
            font-size: 1.2rem;
            letter-spacing: 1px;
            color: var(--text-muted);
        }

        .total-amount { 
            font-size: 2.2rem; 
            color: var(--accent-gold); 
            font-weight: 800; 
            letter-spacing: 1px;
        }

        .btn-print { 
            background: var(--success-green); 
            color: white; 
            border: none; 
            padding: 14px 40px; 
            border-radius: 50px; 
            font-weight: 700; 
            cursor: pointer; 
            font-size: 1rem; 
            margin-top: 30px; 
            transition: 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        /* 🖨 Clean Print Styling */
        @media print {
            .no-print, .admin-navbar { display: none !important; }
            body { background-color: #fff !important; color: #000 !important; }
            .admin-wrapper { margin: 0; padding: 0; max-width: 100%; }
            .bill-card { box-shadow: none !important; border: none !important; width: 100% !important; padding: 0 !important; background: #fff !important; color: #000 !important; }
            .invoice-header h1, .invoice-header p, .customer-meta strong, td, .price-col { color: #000 !important; }
            th { background: #f1f1f1 !important; color: #000 !important; border-bottom: 2px solid #000 !important; }
            td { border-bottom: 1px solid #ccc !important; }
            .total-banner { background: #f8f9fa !important; border: 1px solid #000 !important; color: #000 !important; }
            .total-banner h2 { color: #000 !important; }
            .total-amount { color: #000 !important; }
        }

        /* Responsive UI */
        @media (max-width: 900px) {
            .admin-navbar { position: static; transform: none; width: 100%; margin: 10px 0; flex-direction: column; gap: 12px; padding: 15px; border-radius: 10px; }
            .admin-wrapper { margin-top: 20px; }
            .form-row { flex-direction: column; }
            .invoice-header { flex-direction: column; text-align: center; gap: 15px; }
            .customer-meta { text-align: center; }
        }
    </style>
</head>
<body>
<!--  Floating Glassmorphism Navbar Component -->
    <header class="admin-navbar no-print">
        <div class="logo">PANAMA <span>CONTROL</span></div>
        <nav class="admin-nav-links">
            <a href="admin_home.php">Dashboard</a>
            <a href="register_customer.php">New Guest</a>
            <a href="customer_details.php" class="active">Guest List</a>
            <a href="kitchen_orders.php">Service</a>
            <a href="manage_items.php">Menu</a>
            <a href="manage_orders.php">Orders</a>
            <a href="admin_about.php">About</a>
            <a href="manage_feedback.php">Feedback</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <div class="admin-wrapper">
        <div class="bill-card">
            
            <!-- Quick Add Extra Service Section -->
            <div class="action-box no-print">
                <h4>Billing Desk — Add Charges</h4>
                <form method="POST" class="form-row">
                    <input type="text" name="extra_desc" placeholder="Service (Description)" required>
                    <input type="number" step="0.01" name="extra_amount" placeholder="Fee (Rs.)" required>
                    <button type="submit" name="add_extra" class="btn-add">Add Item</button>
                </form>
            </div>

            <!-- Master Invoice Header -->
            <div class="invoice-header">
                <div>
                    <h1>Hotel Panama</h1>
					<p>Tel-011-1234567</p>
                    <p>Master Invoice</p>
                </div>
                <div class="customer-meta">
                    <div><strong>Customer:</strong> <?php echo htmlspecialchars($user['full_name']); ?></div>
					 <div><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></div>
                    <div><strong>Room number:</strong> <?php echo htmlspecialchars($user['room_number']); ?></div>
                </div>
            </div>

            <!-- Billing Details Table -->
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Date</th>
                        <th class="price-col">Amount</th>
                        <th class="no-print" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Room fee</strong></td>
                        <td><small><?php echo date('Y-m-d', strtotime($user['check_in_datetime'])); ?></small></td>
                        <td class="price-col">Rs. <?php echo number_format($room_total, 2); ?></td>
                        <td class="no-print" style="text-align:center;">-</td>
                    </tr>

                    <?php 
                    $food_sql = "SELECT orders.*, menu_items.item_name FROM orders JOIN menu_items ON orders.item_id = menu_items.id WHERE user_id = '$user_id'";
                    $food_res = $conn->query($food_sql);
                    while($f = $food_res->fetch_assoc()): 
                        $grand_total += $f['total_price'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($f['item_name']); ?></td>
                        <td><small><?php echo date('Y-m-d', strtotime($f['order_date'])); ?></small></td>
                        <td class="price-col">Rs. <?php echo number_format($f['total_price'], 2); ?></td>
                        <td class="no-print" style="text-align:center;">-</td>
                    </tr>
                    <?php endwhile; ?>

                    <?php 
                    $extra_res = $conn->query("SELECT * FROM extra_orders WHERE user_id = '$user_id'");
                    while($ex = $extra_res->fetch_assoc()): 
                        $grand_total += $ex['amount'];
						?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($ex['description']); ?></strong></td>
                        <td><small><?php echo date('Y-m-d', strtotime($ex['order_date'])); ?></small></td>
                        <td class="price-col">Rs. <?php echo number_format($ex['amount'], 2); ?></td>
                        <td class="no-print" style="text-align:center;">
                            <a href="?id=<?php echo $user_id; ?>&delete_id=<?php echo $ex['id']; ?>" class="btn-delete" onclick="return confirm('මෙය ඉවත් කරන්නද?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Grand Total Banner -->
            <div class="total-banner">
                <h2>GRAND TOTAL</h2>
                <div class="total-amount">Rs. <?php echo number_format($grand_total, 2); ?></div>
            </div>

            <!-- Print Button Area -->
            <div style="text-align:center;" class="no-print">
                <button onclick="window.print()" class="btn-print">Print / Download Bill 🖨</button>
            </div>

        </div>
    </div>

</body>
</html>
