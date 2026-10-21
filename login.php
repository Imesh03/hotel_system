<?php
session_start();
include 'db_connection.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // පෝරමයෙන් ලබාගන්නා දත්ත (Security සඳහා escape කරයි)
    $input_user = mysqli_real_escape_string($conn, $_POST['username']);
    $input_pass = mysqli_real_escape_string($conn, $_POST['password']);

    // 1. ප්‍රථමයෙන් 'users' table එකේ පරීක්ෂා කිරීම (Phone Number සහ NIC Number එකට අනුව)
    // මෙහිදී ඔබ පැවසූ පරිදි column නම් phone_number සහ nic_number ලෙස භාවිතා කර ඇත.
    $sql = "SELECT * FROM users WHERE phone_number = '$input_user' AND qr_token = '$input_pass' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Session එකේ දත්ත ගබඩා කිරීම
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['full_name'] = $row['full_name']; // හෝ ඔබේ table එකේ ඇති නම column එක
        $_SESSION['role'] = $row['role']; 

        // පරිශීලකයාගේ Role එක අනුව අදාළ පිටුවට යොමු කිරීම
        if ($row['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($row['role'] == 'staff') {
            header("Location: staff_dashboard.php");
        } else {
            // Customer කෙනෙක් නම් Welcome page එකට
            header("Location: welcome.php");
        }
        exit();
    } else {
        // දත්ත වැරදි නම් පෙන්වන පණිවිඩය
        $error = "ඇතුළත් කළ username  හෝ password  වැරදියි!";
    }
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotel System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Consistent Dark Luxury Theme Background */
        body {
            margin: 0;
            padding: 0;
            background: #0f172a;
            background-image: radial-gradient(circle at center, #1e293b, #0f172a);
            color: #f1f5f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        /* Glassmorphic Centered Login Card */
        .login-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.07);
            padding: 45px 40px;
            border-radius: 28px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6);
            width: 100%;
            max-width: 380px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-2px);
        }

        /* Luxury Header Title */
        .brand-title {
            color: #38bdf8;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            text-shadow: 0 0 12px rgba(56, 189, 248, 0.2);
        }

        .brand-subtitle {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0 0 35px 0;
            font-weight: 500;
        }

        /* Form Controls */
        .form-group { 
            margin-bottom: 24px; 
            text-align: left;
        }

        .form-group label { 
            display: block; 
            margin-bottom: 9px; 
            font-weight: 600; 
            color: #94a3b8; 
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Password Wrapper for Eye Button placement */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
		.form-group input { 
            width: 100%; 
            padding: 14px 16px; 
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08); 
            border-radius: 14px; 
            box-sizing: border-box; 
            color: #f1f5f9;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }

        /* Extra padding for password right side to prevent text overlapping with icon */
        .password-wrapper input {
            padding-right: 46px;
        }

        .form-group input:focus {
            border-color: #38bdf8;
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.15);
        }

        /* Creative Toggle Eye Styling */
        .toggle-password {
            position: absolute;
            right: 16px;
            cursor: pointer;
            color: #64748b;
            font-size: 1.1rem;
            user-select: none;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: #38bdf8;
        }

        /* Error Alert Status Box */
        .error-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.4;
            text-align: center;
        }

        /* Action Button */
        .btn-login {
            width: 100%;
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 14px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #38bdf8;
            color: #0f172a;
            box-shadow: 0 0 20px rgba(56, 189, 248, 0.4);
        }

        /* Subtle Bottom Credits */
        .credits {
            margin-top: 30px;
            font-size: 0.75rem;
            color: #475569;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2 class="brand-title">HOTEL PANAMA</h2>
    <p class="brand-subtitle">Hotel Management System</p>

    <?php if($error != ""): ?>
        <div class="error-box">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Username" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="passwordInput" placeholder="Password" required>
                <span class="toggle-password" id="togglePasswordBtn">👁️</span>
            </div>
        </div>

        <button type="submit" class="btn-login">
            Log In
        </button>
    </form>
    
    <div class="credits">
        &copy; 2026 Powered by Hotel Panama Luxury Systems.
    </div>
</div>

<script>
    const passwordInput = document.getElementById('passwordInput');
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');

    // Password එක පෙන්වීම සහ සැඟවීම සිදු කරන සරල JavaScript ශ්‍රිතය
    togglePasswordBtn.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '🔒';
    });
</script>

</body>
</html>