<?php
include 'db_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔐 දැනටමත් Login වී ඇත්නම් කෙලින්ම Dashboard එකට යැවීම
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_home.php");
    exit;
}

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Prepared Statements භාවිතය
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if ($password === $admin['password'] || password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_user'] = $admin['username'];
                
                header("Location: admin_home.php");
                exit;
            } else {
                $error_msg = "ඇතුළත් කළ මුරපදය (Password) වැරදියි!";
            }
        } else {
            $error_msg = "එවැනි පරිශීලක නාමයක් (Username) සොයාගත නොහැක!";
        }
        $stmt->close();
    } else {
        $error_msg = "කරුණාකර සියලුම තීරුවල තොරතුරු ඇතුළත් කරන්න!";
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Authentication | Hotel Panama</title>
    <style>
        /* 🎨 Premium Luxury Dark Theme - Pure Offline CSS */
        :root {
            --primary-bg: #0f172a;      
            --card-bg: #1e293b;         
            --border-color: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-blue: #38bdf8;
            --accent-gold: #fbbf24;
            --danger-red: #f87171;
        }

        body {
            background: var(--primary-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-main);
        }

        .login-box-container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            padding: 45px 35px;
            border-radius: 16px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
            text-align: center;
            position: relative;
        }

        .login-box-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-gold));
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .hotel-brand-logo {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--accent-gold);
            margin-bottom: 6px;
            letter-spacing: 1.5px;
        }

        .system-subtitle {
            color: var(--text-muted);
            font-size: 0.88rem;
            margin-bottom: 35px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper-block {
            text-align: left;
            margin-bottom: 22px;
        }

        .input-wrapper-block label {
            display: block;
            color: var(--text-muted);
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
		.password-field-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .custom-form-input {
            width: 100%;
            background: var(--primary-bg);
            border: 1px solid var(--border-color);
            padding: 13px 16px;
            border-radius: 8px;
            color: var(--text-main);
            font-size: 0.95rem;
            outline: none;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .password-field-container input {
            padding-right: 50px;
        }

        .custom-form-input:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
        }

        /* 👁️ Pure CSS Eye Icon Button (No external font needed) */
        .toggle-password-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            outline: none;
        }

        /* ඇහේ හැඩය සෑදීම */
        .eye-icon {
            position: relative;
            width: 18px;
            height: 11px;
            border: 2px solid var(--text-muted);
            border-radius: 60px / 40px;
            transition: all 0.2s;
        }

        /* ඇසේ මැද කණිනිකාව (Pupil) */
        .eye-icon::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 5px;
            height: 5px;
            background: var(--text-muted);
            border-radius: 50%;
        }

        /* මුරපදය සැඟවී ඇති විට ඇස මැදින් කැපෙන ඉර (Slash) */
        .eye-icon.slashed::before {
            content: '';
            position: absolute;
            top: -3px;
            left: 6px;
            width: 2px;
            height: 14px;
            background: var(--text-muted);
            transform: rotate(45deg);
        }

        .toggle-password-btn:hover .eye-icon {
            border-color: var(--accent-blue);
        }
        .toggle-password-btn:hover .eye-icon::after,
        .toggle-password-btn:hover .eye-icon.slashed::before {
            background: var(--accent-blue);
        }

        .secure-submit-btn {
            width: 100%;
            background: var(--accent-blue);
            color: #0f172a;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .secure-submit-btn:hover {
            background: #7dd3fc;
            transform: translateY(-1px);
        }

        .forgot-link-ui {
            display: block;
            margin-top: 20px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
        }

        .forgot-link-ui:hover {
            color: var(--accent-gold);
            text-decoration: underline;
        }

        .luxury-error-alert {
            background: rgba(248, 113, 113, 0.08);
            border: 1px solid rgba(248, 113, 113, 0.2);
            color: var(--danger-red);
            padding: 14px;
            border-radius: 8px;
            font-size: 0.88rem;
            margin-bottom: 25px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="login-box-container">
        <div class="hotel-brand-logo">🏨 HOTEL PANAMA</div>
        <div class="system-subtitle">Commander Control Authentication</div>

        <?php if (!empty($error_msg)): ?>
            <div class="luxury-error-alert">
                🛑 <?php echo htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>
		<form action="admin_login.php" method="POST">
            <div class="input-wrapper-block">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="custom-form-input" placeholder="Enter admin username" required autocomplete="off">
            </div>

            <div class="input-wrapper-block">
                <label for="password">Password</label>
                <div class="password-field-container">
                    <input type="password" id="password" name="password" class="custom-form-input" placeholder="Enter admin password" required>
                    <button type="button" class="toggle-password-btn" id="togglePasswordBtn" aria-label="Toggle Password Visibility">
                        <div class="eye-icon slashed" id="eyeIcon"></div>
                    </button>
                </div>
            </div>

            <button type="submit" class="secure-submit-btn">Secure Login</button>
        </form>

        <a href="admin_forget.php" class="forgot-link-ui">Forgot Password?</a>
    </div>

    <script>
        const togglePasswordBtn = document.querySelector('#togglePasswordBtn');
        const passwordInput = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePasswordBtn.addEventListener('click', function () {
            // Input Type එක මාරු කිරීම
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            
            // Pure CSS Eye Icon එකේ 'slashed' class එක එක් කිරීම හෝ ඉවත් කිරීම
            if (isPassword) {
                eyeIcon.classList.remove('slashed');
            } else {
                eyeIcon.classList.add('slashed');
            }
        });
    </script>

</body>
</html>