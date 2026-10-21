<?php
include 'db_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer 
require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

// 1. Deactivate accounts & Send Checkout Email via PHPMailer
if (isset($_POST['deactivate_user'])) {
    $u_id = intval($_POST['u_id']);
    
    // Deactivate karanna pera customerge visthara ganna eka
    $user_info_query = "SELECT full_name, email FROM users WHERE id = $u_id LIMIT 1";
    $user_info_result = $conn->query($user_info_query);
    
    if ($user_info_result && $user_info_result->num_rows > 0) {
        $user_data = $user_info_result->fetch_assoc();
        $customer_name = $user_data['full_name'];
        $customer_email = $user_data['email']; 
        
        // Status = 0  update kirima (Deactivate)
        $sql = "UPDATE users SET status = 0 WHERE id = $u_id";
        if ($conn->query($sql)) {
            
            // email ekak customerta thiyenvnm vtrk PHPMailer kriya kirima
            if (!empty($customer_email)) {
                $mail = new PHPMailer(true);
                
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';             
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'lasadaimesh@gmail.com';       // 🟢 email
                    $mail->Password   = 'replzijwagofybtz';           // 🟢  Gmail App Password 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    
                    // Localhost එකේදී ඇතිවන SSL ගැටලු මඟහරවා ගැනීමට
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
                    $mail->Subject = 'Thank you for staying at Hotel Panama! - Checkout Successful';
                    
                    // HTML Email Body Luxury Theme 
                    $mail->Body = "
                    <html>
                    <head>
                        <title>Thank You for Your Stay</title>
                    </head>
                    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f8fafc; padding: 20px;'>
                        <div style='max-width: 600px; margin: 0 auto; padding: 30px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
                            <h2 style='color: #0f172a; text-align: center; font-size: 24px; border-bottom: 2px solid #fbbf24; padding-bottom: 15px;'>🏨 HOTEL PANAMA</h2>
                            <p style='font-size: 16px;'>Hello <strong>" . htmlspecialchars($customer_name) . "</strong>,</p>
                            <p>You have been successfully checked out from <strong>Hotel Panama</strong>, and your system account has been safely deactivated.</p>
                            
                            <div style='background-color: #fef3c7; border-left: 4px solid #fbbf24; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                                <p style='margin: 0; color: #92400e; font-weight: 500;'>
                                    We sincerely appreciate you choosing our hotel for your stay. We hope you had a wonderful and comfortable experience with our premium services.
                                </p>
                            </div>
                            
                            <p style='font-style: italic; color: #475569;'>Thank you once again, and we look forward to welcoming you back soon on your next visit!</p>
                            <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 25px 0;'>
                            <p style='font-size: 0.85rem; color: #64748b; text-align: center;'>Best Regards,<br>Management & Reception Team<br><strong>Hotel Panama Command Center</strong></p>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    // Plain text version
                    $mail->AltBody = "Hello " . $customer_name . ",\n\nYou have been successfully checked out from Hotel Panama. Your account has been deactivated.\n\nThank you for choosing us!\n\nBest Regards,\nHotel Panama Management Team";
                    
                    $mail->send();
                } catch (Exception $e) {
                    // ඊමේල් එක නොගියත් සිස්ටම් එක ක්‍රෑෂ් වීම වැළැක්වීම සඳහා
                }
            }
            
            header("Location: customer_details.php?msg=deactivated");
            exit();
        }
    }
}

// 🔍 Dynamic Search Filter
$search_query = "";
if (isset($_GET['search_keyword']) && !empty($_GET['search_keyword'])) {
    $keyword = $conn->real_escape_string(trim($_GET['search_keyword']));
    $search_query = " WHERE full_name LIKE '%$keyword%' OR nic_number LIKE '%$keyword%' OR phone_number LIKE '%$keyword%' ";
}

// 2. get searched Details
$query = "SELECT * FROM users $search_query ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Customer Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* 🎨 Custom Premium Luxury Dark Theme Variables */
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
            font-family: 'Segoe UI', sans-serif; 
            background-color: var(--primary-bg); 
            color: var(--text-main);
            margin: 0; 
            padding: 0; 
        }

        /* 🚀 NEW: Floating Glassmorphism Navigation Bar Styles */
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

        /* Active State Style (Guest List is active here) */
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
        
        .container { 
            width: 100%; 
            max-width: 1400px; 
            margin: 120px auto 40px;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .header { 
            background: var(--card-bg); 
            color: var(--text-main); 
            padding: 25px; 
            text-align: center; 
            border-radius: 16px; 
            margin-bottom: 25px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        .header h1 { margin: 0; font-size: 1.8rem; font-weight: 700; }
        .header p { margin: 5px 0 0; color: var(--text-muted); font-size: 0.95rem; }

        .search-panel {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
        }
        .search-form { display: flex; gap: 12px; width: 100%; max-width: 650px; align-items: center; }
        .search-input {
            flex: 1; padding: 12px 18px; background: #090d16; border: 1px solid var(--border-color);
            border-radius: 8px; font-size: 0.9rem; color: var(--text-main); outline: none; transition: 0.3s ease;
        }
        .search-input:focus { border-color: var(--accent-gold); box-shadow: 0 0 8px rgba(251, 191, 36, 0.2); }
        
        .btn-search { background: transparent; color: var(--accent-gold); border: 1px solid var(--accent-gold); padding: 11px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-search:hover { background: var(--accent-gold); color: var(--primary-bg); }
        .btn-reset { background: #243552; color: #cbd5e1; padding: 11px 20px; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: 600; }

        .table-wrapper { background: var(--card-bg); border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); border: 1px solid var(--border-color); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #090d16; color: var(--text-main); padding: 20px 18px; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid var(--border-color); font-weight: 700; }
        td { padding: 20px 18px; border-bottom: 1px solid rgba(36, 53, 82, 0.5); vertical-align: middle; }
        tbody tr:hover { background-color: var(--table-row-hover); }

        .label { font-size: 0.68rem; color: var(--accent-blue); font-weight: 700; text-transform: uppercase; display: inline-block; margin-bottom: 5px; letter-spacing: 0.8px; }
        .data-text { display: block; color: #cbd5e1; line-height: 1.5; }
        .info-group { margin-bottom: 14px; }
        
        .room-badge { display: inline-block; background: rgba(56, 189, 248, 0.12); color: var(--accent-blue); padding: 5px 10px; border-radius: 6px; border: 1px solid rgba(56, 189, 248, 0.25); font-weight: 700; font-size: 0.85rem; }
        .crypto-box { background: #090d16; padding: 14px; border-radius: 10px; border: 1px solid var(--border-color); border-left: 4px solid var(--accent-gold); }

        .btn { padding: 11px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 0.8rem; border: none; cursor: pointer; display: block; width: 100%; text-align: center; transition: 0.3s; box-sizing: border-box; }
        .btn-bill { background: rgba(251, 191, 36, 0.1); color: var(--accent-gold); border: 1px solid rgba(251, 191, 36, 0.4); margin-bottom: 10px; }
        .btn-bill:hover { background: var(--accent-gold); color: var(--primary-bg); }
        .btn-deactivate { background: rgba(248, 113, 113, 0.1); color: var(--danger-red); border: 1px solid rgba(248, 113, 113, 0.4); }
        .btn-deactivate:hover { background: var(--danger-red); color: white; }
        .btn-qr { background: rgba(16, 185, 129, 0.12); color: #34d399; font-size: 0.75rem; padding: 8px; margin-top: 10px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .btn-qr:hover { background: var(--success-green); color: white; }

        .inactive-badge { background: rgba(248, 113, 113, 0.04); color: var(--danger-red); padding: 14px; border-radius: 10px; border: 1px dashed rgba(248, 113, 113, 0.4); text-align: center; }
        .lock-icon { font-size: 1.3rem; display: block; }

        @media (max-width: 1100px) {
            .admin-navbar { position: static; transform: none; width: 100%; margin: 10px 0; flex-direction: column; gap: 12px; padding: 15px; border-radius: 10px; }
            .container { margin-top: 20px; }
        }

        @media (max-width: 1024px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }
            tr { border-bottom: 2px solid var(--border-color); padding: 15px 0; }
            td { border: none; padding: 10px 20px; }
            .search-form { flex-direction: column; align-items: center; }
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
        <a href="customer_details.php" class="active">Guest List</a>
        <a href="kitchen_orders.php">Service</a>
        <a href="manage_items.php">Menu</a>
        <a href="manage_orders.php">Orders</a>
        <a href="admin_about.php">About</a>
        <a href="manage_feedback.php">Feedback</a>
        <a href="admin_logout.php" class="logout-btn">Logout</a>
    </nav>
</header>

<div class="container">
    <div class="header">
        <h1>Hotel Administration - Customer Details 👥</h1>
        <p></p>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deactivated'): ?>
        <div style="background-color: rgba(16, 185, 129, 0.15); border: 1px solid var(--success-green); color: var(--success-green); padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600;">
           The Customer account was successfully deactivated! ✅
        </div>
    <?php endif; ?>

    <div class="search-panel">
        <form method="GET" class="search-form">
            <input type="text" name="search_keyword" placeholder="Name, NIC... " value="<?php echo isset($_GET['search_keyword']) ? htmlspecialchars($_GET['search_keyword']) : ''; ?>" class="search-input">
            <button type="submit" class="btn-search">Search 🔍</button>
            <?php if(!empty($_GET['search_keyword'])): ?>
                <a href="customer_details.php" class="btn-reset">Reset 🔄</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th width="24%">Customer & Address</th>
                    <th width="16%">NIC & Room</th>
                    <th width="18%">Contact & Stay</th>
                    <th width="16%">Login Credentials</th>
                    <th width="11%">QR Code</th>
                    <th width="15%">Financial & Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="info-group">
                                <span class="label">Full Name</span>
                                <strong class="data-text" style="color: var(--text-main); font-size: 1.05rem;"><?php echo htmlspecialchars($row['full_name']); ?></strong>
                            </div>
                            <div class="info-group">
                                <span class="label">Address</span>
                                <small class="data-text" style="color: var(--text-muted); font-size: 0.85rem;"><?php echo htmlspecialchars($row['address']); ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="info-group">
                                <span class="label">NIC Number</span>
                                <span class="data-text" style="font-weight: 600; color: #e2e8f0;"><?php echo htmlspecialchars($row['nic_number']); ?></span>
                            </div>
                            <div class="info-group">
                                <span class="label">Room Allocation</span><br>
                                <span class="room-badge">Room <?php echo htmlspecialchars($row['room_number']); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="info-group">
                                <span class="label">Phone (Username)</span>
                                <span class="data-text" style="color: var(--accent-blue); font-weight: 600;"><?php echo htmlspecialchars($row['phone_number']); ?></span>
                            </div>
                            <div class="info-group">
                                 <span class="label">E-mail</span>
                                <span class="data-text" style="color: #cbd5e1; font-weight: 500; font-size: 0.85rem;"><?php echo htmlspecialchars($row['email']); ?></span>
                            </div>
                            <div class="info-group">
                                <span class="label">Check-In Date</span>
                                <small class="data-text" style="color: var(--text-muted);"><?php echo htmlspecialchars($row['check_in_datetime']); ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="crypto-box">
                                <span class="label" style="color: var(--text-muted);">Password</span>
                                <code style="font-weight:700; color: var(--accent-gold); font-size: 1rem; display: block; margin-top: 4px; word-break: break-all;">
                                    <?php echo htmlspecialchars($row['login_password'] ?: $row['qr_token']); ?>
                                </code>
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <img src="<?php echo htmlspecialchars($row['password']); ?>" width="65" style="border:1px solid var(--border-color); padding:4px; background: #fff; border-radius: 8px;"><br>
                            <a href="<?php echo htmlspecialchars($row['password']); ?>" download class="btn btn-qr">Download QR</a>
                        </td>
                        <td>
                            <a href="view_full_bill.php?id=<?php echo $row['id']; ?>" target="_blank" class="btn btn-bill">View Master Bill 📄</a>
                            <div style="margin-top: 10px;">
                            <?php 
                            if ($row['status'] == 0) {
                                echo '
                                <div class="inactive-badge">
                                    <span class="lock-icon">🔒</span>
                                    <span style="font-size:0.75rem; font-weight:bold;">DEACTIVATED</span>
                                </div>';
                            } else {
                                echo '
                                <form method="POST" onsubmit="return confirm(\'This customer Log out? .\');">
                                    <input type="hidden" name="u_id" value="'.$row['id'].'">
                                    <button type="submit" name="deactivate_user" class="btn btn-deactivate">Deactivate Now</button>
                                </form>';
                            }
                            ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 50px; color: var(--text-muted);">Infor.</td>
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