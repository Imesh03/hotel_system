<?php
include 'db_connection.php';
session_start();

$step = 1; // 1: Username පරීක්ෂාව, 2: ආරක්ෂක ප්‍රශ්නය පරීක්ෂාව
$username = "";
$question = "";
$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['check_user'])) {
        $username = trim($_POST['username']);
        $stmt = $conn->prepare("SELECT username, security_question FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $admin = $res->fetch_assoc();
            $_SESSION['reset_user'] = $admin['username'];
            $question = $admin['security_question'];
            $step = 2; // ඊළඟ පියවරට
        } else {
            $error_msg = "එවැනි පරිශීලක නාමයක් සොයාගත නොහැක!";
        }
        $stmt->close();
    } elseif (isset($_POST['reset_password'])) {
        $username = $_SESSION['reset_user'];
        $answer = trim($_POST['security_answer']);
        $new_password = trim($_POST['new_password']);

        $stmt = $conn->prepare("SELECT security_answer FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && $answer === $admin['security_answer']) {
            // නව මුරපදය Hash කර සුරැකීම
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $hashed_password, $username);
            
            if ($update_stmt->execute()) {
                $success_msg = "මුරපදය සාර්ථකව වෙනස් කරන ලදී! දැන් ලොග් විය හැක.";
                unset($_SESSION['reset_user']);
                $step = 3; // අවසන් පියවර
            } else {
                $error_msg = "යම් දෝෂයක් සිදු විය. නැවත උත්සාහ කරන්න.";
                $step = 2;
            }
            $update_stmt->close();
        } else {
            $error_msg = "ආරක්ෂිත පිළිතුර (Security Answer) වැරදියි!";
            $step = 2;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | Hotel Panama</title>
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --border: #334155; --gold: #fbbf24; --blue: #38bdf8; --text: #f8fafc; }
        body { background: var(--bg); color: var(--text); font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin:0;}
        .forget-container { background: var(--card); border: 1px solid var(--border); padding: 40px; border-radius: 16px; width: 100%; max-width: 400px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); text-align: center; }
        .logo { font-size: 1.5rem; font-weight: bold; color: var(--gold); margin-bottom: 20px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; color: #94a3b8; font-size: 0.85rem; margin-bottom: 8px; font-weight: bold; }
        input { width: 100%; background: var(--bg); border: 1px solid var(--border); padding: 12px; border-radius: 8px; color: #fff; box-sizing: border-box; outline: none; }
        .btn { width: 100%; background: var(--blue); color: #0f172a; border: none; padding: 13px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.88rem; text-align: left; }
        .danger { background: rgba(248, 113, 113, 0.1); border: 1px solid #f87171; color: #f87171; }
        .success { background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; }
        .link { display: block; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 0.9rem; }
		</style>
</head>
<body>
    <div class="forget-container">
        <div class="logo">🔐 Account Recovery</div>
        
        <?php if($error_msg): ?><div class="alert danger">🛑 <?php echo $error_msg; ?></div><?php endif; ?>
        <?php if($success_msg): ?><div class="alert success">✅ <?php echo $success_msg; ?></div><?php endif; ?>

        <?php if ($step === 1): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username එක ඇතුළත් කරන්න</label>
                    <input type="text" name="username" placeholder="Enter admin username" required autocomplete="off">
                </div>
                <button type="submit" name="check_user" class="btn">Next Step</button>
            </form>
        <?php elseif ($step === 2): ?>
            <form method="POST">
                <p style="color: var(--gold); font-size:0.95rem; text-align:left;">Question: <?php echo htmlspecialchars($question); ?></p>
                <div class="form-group">
                    <label>ආරක්ෂිත පිළිතුර (Security Answer)</label>
                    <input type="text" name="security_answer" placeholder="Enter your answer" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>නව මුරපදය (New Password)</label>
                    <input type="password" name="new_password" placeholder="Enter new password" required>
                </div>
                <button type="submit" name="reset_password" class="btn">Reset Password</button>
            </form>
        <?php elseif ($step === 3): ?>
            <a href="admin_login.php" class="btn" style="text-decoration: none; display: inline-block;">Go to Login</a>
        <?php endif; ?>

        <?php if($step !== 3): ?>
            <a href="admin_login.php" class="link">Cancel and Back</a>
        <?php endif; ?>
    </div>
</body>
</html>