<?php 
include 'db_connection.php'; 

//  PHPMailer Namespace  Files Including
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists('Exception.php') && file_exists('PHPMailer.php') && file_exists('SMTP.php')) {
    require 'Exception.php';
    require 'PHPMailer.php';
    require 'SMTP.php';
}

//  AJAX magin en Email Request eka Handle krna ek
if (isset($_POST['action']) && $_POST['action'] === 'send_ajax_email') {
    header('Content-Type: application/json');
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $qr_token = $_POST['qr_token'];
    $qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_token);

    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'lasadaimesh@gmail.com';  
            $mail->Password   = 'replzijwagofybtz';    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8'; 

            //  [FIX] Localhost SSL Error maga arinna
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $mail->setFrom('lasadaimesh@gmail.com', 'Hotel Panama');
            $mail->addAddress($email, $name); 

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Registration Successful - Hotel Panama Login Details';
            
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f5; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e4e4e7;'>
                        <h2 style='color: #fbbf24; text-align: center; margin-top: 0;'>Welcome to Hotel Panama!</h2>
                        <p>Dear <strong>{$name}</strong>,</p>
                        <p>Your account has been registered successfully. You can find your digital access details below:</p>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <img src='{$qr_image_url}' alt='Guest QR Code' style='border: 1px solid #cbd5e1; padding: 10px; border-radius: 8px; background: #fff;'>
                            <p style='font-size: 0.9rem; color: #64748b; margin-top: 10px;'>Scan this QR code at the service counter or login portal.</p>
                        </div>

                        <div style='background: #f1f5f9; padding: 20px; border-radius: 6px; border-left: 4px solid #38bdf8;'>
                            <h4 style='margin: 0 0 10px 0; color: #0f172a;'>Manual Login Details:</h4>
                            <p style='margin: 5px 0;'><strong>Username:</strong> {$phone}</p>
                            <p style='margin: 5px 0;'><strong>Password:</strong> <code style='background: #cbd5e1; padding: 2px 6px; border-radius: 4px; font-weight: bold;'>{$qr_token}</code></p>
                        </div>
                        <br>
                        <p style='margin-bottom: 0;'>Best regards,<br><strong>Management Team<br>Hotel Panama</strong></p>
                    </div>
                </div>";

            $mail->send();
            echo json_encode(['status' => 'success', 'message' => '✉️ Email has been sent successfully to the guest!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => "❌ Email not sent. Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => '❌ PHPMailer files are missing!']);
    }
    exit;
}

$show_qr = false;
$email_sent_status = ""; 

if(isset($_POST['register'])){
    $name = $_POST['full_name'];
    $nic = $_POST['nic'];
    $phone = $_POST['phone']; 
    $email = $_POST['email'];
    $address = $_POST['address'];
    $room_no = $_POST['room_no'];
    $price_per_session = $_POST['price_per_session'];
    $check_in = $_POST['check_in_dt'];
    $check_out = $_POST['check_out_dt'];
    
    $qr_token = bin2hex(random_bytes(10)); 
    $password_hashed = password_hash($qr_token, PASSWORD_DEFAULT); 

    $name = $conn->real_escape_string($name);
    $nic = $conn->real_escape_string($nic);
    $phone = $conn->real_escape_string($phone);
    $email = $conn->real_escape_string($email);
    $address = $conn->real_escape_string($address);
    $room_no = $conn->real_escape_string($room_no);
    $check_in = $conn->real_escape_string($check_in);
    $check_out = $conn->real_escape_string($check_out);

    $sql = "INSERT INTO users (full_name, nic_number, phone_number, email, address, room_number, price_per_session, check_in_datetime, check_out_datetime, qr_token, password, status) 
            VALUES ('$name', '$nic', '$phone', '$email', '$address', '$room_no', '$price_per_session', '$check_in', '$check_out', '$qr_token', '$password_hashed', 1)";

    if ($conn->query($sql) === TRUE) {
        $show_qr = true;
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_token);
        $qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_token);
        
        // WhatsApp Phone Number Formatting 
        $clean_phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($clean_phone, 0, 1) === '0') {
            $formatted_phone = '94' . substr($clean_phone, 1);
        } else if (substr($clean_phone, 0, 2) !== '94') {
            $formatted_phone = '94' . $clean_phone;
        } else {
            $formatted_phone = $clean_phone;
        }

        $msg = "ආයුබෝවන් $name, Hotel Panama වෙත ඔබව සාදරයෙන් පිළිගනිමු! \n\n"
             . "ඔබේ QR සබැඳිය: $qr_url \n"
             . "සෘජුව ලොග් වීමට (Manual Login): \n"
             . "Username: $phone \n"
             . "Password: $qr_token \n\n"
             . "Thank you!";
             
        $whatsapp_final_url = "https://wa.me/" . $formatted_phone . "?text=" . urlencode($msg);

        // register karaddi email ekak yana eka
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'lasadaimesh@gmail.com';  
                $mail->Password   = 'replzijwagofybtz';    
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8'; 

                // [FIX] Localhost SSL Error magaharima
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                $mail->setFrom('lasadaimesh@gmail.com', 'Hotel Panama');
                $mail->addAddress($email, $name); 

                $mail->isHTML(true);
                $mail->Subject = 'Registration Successful - Hotel Panama Login Details';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f5; color: #333;'>
                        <div style='max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; border: 1px solid #e4e4e7;'>
                            <h2 style='color: #fbbf24; text-align: center; margin-top: 0;'>Welcome to Hotel Panama!</h2>
                            <p>Dear <strong>{$name}</strong>,</p>
                            <p>Your account has been registered successfully. Access details:</p>
                            <div style='text-align: center; margin: 30px 0;'>
                                <img src='{$qr_image_url}' alt='Guest QR Code' style='border: 1px solid #cbd5e1; padding: 10px; border-radius: 8px; background: #fff;'>
                            </div>
                            <div style='background: #f1f5f9; padding: 20px; border-radius: 6px; border-left: 4px solid #38bdf8;'>
                                <p style='margin: 5px 0;'><strong>Username:</strong> {$phone}</p>
                                <p style='margin: 5px 0;'><strong>Password:</strong> <code>{$qr_token}</code></p>
                            </div>
                        </div>
                    </div>";

                $mail->send();
                $email_sent_status = "<div class='success-banner' id='status-alert' style='background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid #10b981; margin-bottom: 20px;'>✉️ Email has been sent automatically to the guest!</div>";
            } catch (Exception $e) {
                $email_sent_status = "<div class='success-banner' id='status-alert' style='background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid #f87171; margin-bottom: 20px;'>❌ Email not sent. Error: {$mail->ErrorInfo}</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - QR & Login Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
            --input-bg: #090d16;
            --nav-bg: rgba(19, 28, 46, 0.7);
        }

        body { 
            background: var(--primary-bg); 
            color: var(--text-main);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

        /* Active State Style (New Guest is active here) */
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

        .reg-container {
            max-width: 850px;
            margin: 120px auto 40px;
            padding: 0 20px;
            box-sizing: border-box;
        }
        .reg-card {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border-color);
        }
        .reg-title {
            margin-top: 0;
            margin-bottom: 30px;
            font-size: 1.8rem;
            color: var(--text-main);
            font-weight: 700;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            letter-spacing: 0.5px;
        }
        .reg-title span { color: var(--accent-gold); }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 22px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full-width { grid-column: span 2; }
        .form-grid-three { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; grid-column: span 2; }
        label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .custom-input, .custom-textarea { width: 100%; padding: 13px 16px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.95rem; box-sizing: border-box; transition: 0.3s ease; background: var(--input-bg); color: var(--text-main); outline: none; }
        .custom-input:focus, .custom-textarea:focus { border-color: var(--accent-gold); background: var(--input-bg); box-shadow: 0 0 8px rgba(251, 191, 36, 0.15); }
        .custom-textarea { resize: vertical; height: 90px; }
        .submit-btn { background: transparent; color: var(--accent-gold); border: 1px solid var(--accent-gold); padding: 15px; width: 100%; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: 0.3s ease; margin-top: 30px; text-transform: uppercase; letter-spacing: 0.5px; }
        .submit-btn:hover { background: var(--accent-gold); color: var(--primary-bg); box-shadow: 0 0 15px rgba(251, 191, 36, 0.3); }
        .success-banner { padding: 15px; border-radius: 10px; font-size: 1.1rem; font-weight: 600; text-align: center; margin-bottom: 25px; border: 1px solid rgba(16, 185, 129, 0.3); }
        .success-banner-main { background: rgba(16, 185, 129, 0.1); color: var(--success-green); }
        .qr-display-box { margin: 30px 0; text-align: center; }
        .qr-img { border: 1px solid var(--border-color); padding: 15px; border-radius: 12px; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.2); display: inline-block; }
        .login-details-box { background: #090d16; border: 1px solid var(--border-color); border-left: 4px solid var(--accent-blue); padding: 20px 30px; border-radius: 10px; margin: 20px auto; text-align: left; display: inline-block; min-width: 320px; box-sizing: border-box; }
        .login-details-box h4 { margin: 0 0 12px 0; color: var(--accent-blue); border-bottom: 1px solid var(--border-color); padding-bottom: 8px; font-size: 1rem; text-transform: uppercase; }
        .login-details-box p { margin: 8px 0; color: #cbd5e1; font-size: 0.95rem; }
        .flex-buttons-row { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; margin-top: 30px; }
        .action-ui-btn { padding: 13px 22px; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: 600; border: none; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; box-sizing: border-box; }
        .btn-ui-dark { background: rgba(248, 250, 252, 0.05); color: var(--text-main); border: 1px solid var(--border-color); }
        .btn-ui-dark:hover { background: rgba(248, 250, 252, 0.15); }
        .btn-ui-success { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .btn-ui-success:hover { background: var(--success-green); color: white; }
        .btn-ui-info { background: rgba(56, 189, 248, 0.1); color: var(--accent-blue); border: 1px solid rgba(56, 189, 248, 0.3); }
        .btn-ui-info:hover { background: var(--accent-blue); color: #090d16; }
        .btn-ui-outline { background: transparent; border: 1px solid #475569; color: var(--text-muted); }
        .btn-ui-outline:hover { border-color: var(--text-main); color: var(--text-main); }

        @media (max-width: 1100px) {
            .admin-navbar { position: static; transform: none; width: 100%; margin: 10px 0; flex-direction: column; gap: 12px; padding: 15px; border-radius: 10px; }
            .reg-container { margin-top: 20px; }
        }

        @media (max-width: 768px) {
            .form-grid, .form-grid-three { grid-template-columns: 1fr; gap: 15px; }
            .form-group.full-width, .form-grid-three { grid-column: span 1; }
            .login-details-box { min-width: 100%; }
        }
    </style>
</head>
<body>

    <!-- 🚀 Modified Floating Navbar Component -->
    <header class="admin-navbar">
        <div class="logo">PANAMA <span>CONTROL</span></div>
        <nav class="admin-nav-links">
            <a href="admin_home.php">Dashboard</a>
            <a href="register_customer.php" class="active">New Guest</a>
            <a href="customer_details.php">Guest List</a>
            <a href="kitchen_orders.php">Service</a>
            <a href="manage_items.php">Menu</a>
            <a href="manage_orders.php">Orders</a>
            <a href="admin_about.php">About</a>
            <a href="manage_feedback.php">Feedback</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <div class="reg-container">
        <div class="reg-card">
            
            <?php if(!$show_qr): ?>
                <h3 class="reg-title">Guest <span>Registration</span></h3>
                
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="custom-input" required placeholder="Guest full name">
                        </div>
                        
                        <div class="form-group">
                            <label>NIC Number</label>
                            <input type="text" name="nic" class="custom-input" required placeholder="National ID number">
                        </div>
                        
                        <div class="form-group">
                            <label>WhatsApp Number</label>
                            <input type="text" name="phone" class="custom-input" required placeholder="e.g. 0771234567">
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="custom-input" required placeholder="Guest email address">
                        </div>
                        
                        <div class="form-group file-width" style="grid-column: span 2;">
                            <label>Home Address</label>
                            <textarea name="address" class="custom-textarea" required placeholder="Permanent address details..."></textarea>
                        </div>
                        
                        <div class="form-grid-three">
                            <div class="form-group">
                                <label>Room No</label>
                                <input type="text" name="room_no" class="custom-input" required placeholder="Room ID">
                            </div>
                            
                            <div class="form-group">
                                <label>Price/Session (Rs.)</label>
                                <input type="number" name="price_per_session" class="custom-input" required placeholder="Amount">
                            </div>
                            
                            <div class="form-group">
                                <label>Check-in Date/Time</label>
                                <input type="datetime-local" name="check_in_dt" class="custom-input" required>
                            </div>
                        </div>
                        
                        <div class="form-group" style="grid-column: span 2; display: flex; justify-content: flex-end;">
                            <div style="width: 50%; min-width: 250px;" class="form-group">
                                <label>Check-out Date/Time</label>
                                <input type="datetime-local" name="check_out_dt" class="custom-input" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="register" class="submit-btn">Generate QR & Register Guest</button>
                </form>
            <?php else: ?>
                <div style="text-align: center;">
                    <div class="success-banner success-main-banner">✅ Registration Successful!</div>
                    
                    <!-- Alert Box for Email Status -->
                    <div id="ajax-email-status">
                        <?php echo $email_sent_status; ?>
                    </div>

                    <h3 style="color: var(--text-main); margin-bottom: 5px; font-size: 1.5rem;">Guest: <?php echo htmlspecialchars($name); ?></h3>
                    <p style="color: var(--text-muted); margin-top: 0; font-size: 0.95rem;">Registration successfully.</p>
                    
                    <div class="qr-display-box">
                        <img src="<?php echo $qr_image_url; ?>" class="qr-img" alt="Guest QR Code">
                        <p style="color: var(--accent-gold); font-weight: 600; margin-top: 15px; font-size: 0.95rem; letter-spacing: 0.5px;">Scan for Instant QR Code Login</p>
                    </div>

                    <div class="login-details-box">
                        <h4>Manual Login Details:</h4>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($phone); ?></p>
                        <p><strong>Password:</strong> <code style="font-size:1rem; background: var(--primary-bg); padding:3px 8px; border-radius:4px; border:1px solid var(--border-color); color: var(--accent-gold); font-weight: bold;"><?php echo $qr_token; ?></code></p>
                    </div>

                    <div class="flex-buttons-row">
                        <a href="<?php echo $qr_image_url; ?>" download="QR_<?php echo urlencode($name); ?>.png" class="action-ui-btn btn-ui-dark">Download QR 📥</a>
                        
                        <a href="<?php echo $whatsapp_final_url; ?>" target="_blank" class="action-ui-btn btn-ui-success">Send via WhatsApp 💬</a>
                        
                        <!-- AJAX Automatic Email Button -->
                        <button type="button" onclick="sendAutomaticEmail()" id="emailBtn" class="action-ui-btn btn-ui-info">Send via Email ✉️</button>
                    </div>

                    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                        <a href="admin_home.php" class="action-ui-btn btn-ui-outline" style="padding: 10px 40px;">Back to Admin Home</a>
                    </div>
                </div>

                <!-- JavaScript for Background AJAX Email Sending -->
                <script>
                function sendAutomaticEmail() {
                    const btn = document.getElementById('emailBtn');
                    const statusDiv = document.getElementById('ajax-email-status');
                    
                    btn.disabled = true;
                    btn.innerText = "Sending Email... ⏳";
                    
                    let formData = new FormData();
                    formData.append('action', 'send_ajax_email');
                    formData.append('name', `<?php echo addslashes($name); ?>`);
                    formData.append('email', `<?php echo addslashes($email); ?>`);
                    formData.append('phone', `<?php echo addslashes($phone); ?>`);
                    formData.append('qr_token', `<?php echo addslashes($qr_token); ?>`);

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
                            statusDiv.innerHTML = `<div class='success-banner' style='background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid #10b981; margin-bottom: 20px;'>\${data.message}</div>`;
                        } else {
                            statusDiv.innerHTML = `<div class='success-banner' style='background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid #f87171; margin-bottom: 20px;'>\${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        statusDiv.innerHTML = `<div class='success-banner' style='background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid #f87171; margin-bottom: 20px;'>❌ Connection error occurred!</div>`;
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerText = "Send via Email ✉️";
                    });
                }
                </script>
            <?php endif; ?>
            
        </div>
    </div>

</body>
<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>
</html>