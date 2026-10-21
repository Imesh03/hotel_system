<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$message = "";
$error = "";
$admin_id = $_SESSION['admin_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $new_password = trim($_POST['password']);
    $sec_question = trim($_POST['security_question']);
    $sec_answer = trim($_POST['security_answer']);

    if (!empty($new_username) && !empty($sec_question) && !empty($sec_answer)) {
        
        if (!empty($new_password)) {
            // පාස්වර්ඩ් එකත් වෙනස් කරන්නේ නම් (Hashed Password)
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE admins SET username = ?, password = ?, security_question = ?, security_answer = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $new_username, $hashed_password, $sec_question, $sec_answer, $admin_id);
        } else {
            // පාස්වර්ඩ් එක වෙනස් නොකරන්නේ නම්
            $stmt = $conn->prepare("UPDATE admins SET username = ?, security_question = ?, security_answer = ? WHERE id = ?");
            $stmt->bind_param("sssi", $new_username, $sec_question, $sec_answer, $admin_id);
        }

        if ($stmt->execute()) {
            $_SESSION['admin_user'] = $new_username; // Session එක Update කිරීම
            $message = "පාලක දත්ත සාර්ථකව යාවත්කාලීන කරන ලදී!";
        } else {
            $error = "යාවත්කාලීන කිරීම අසාර්ථකයි. නැවත උත්සාහ කරන්න.";
        }
        $stmt->close();
    } else {
        $error = "කරුණාකර අනිවාර්ය තොරතුරු ඇතුළත් කරන්න.";
    }
}

// දැනට තිබෙන දත්ත ලබා ගැනීම
$stmt = $conn->prepare("SELECT username, security_question, security_answer FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | Admin Control</title>
    <style>
        :root { --bg: #0f172a; --card: #1e293b; --border: #334155; --gold: #fbbf24; --blue: #38bdf8; --text: #f8fafc; }
        body { background: var(--bg); color: var(--text); font-family: sans-serif; display: flex; justify-content: center; padding: 50px 20px; }
        .profile-container { background: var(--card); border: 1px solid var(--border); padding: 35px; border-radius: 12px; width: 100%; max-width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); }
        h2 { color: var(--gold); margin-top: 0; text-align: center; }
        .form-group { margin-bottom: 18px; }
        label { display: block; color: #94a3b8; font-size: 0.85rem; margin-bottom: 6px; font-weight: bold; }
        input { width: 100%; background: var(--bg); border: 1px solid var(--border); padding: 12px; border-radius: 6px; color: #fff; box-sizing: border-box; outline: none; }
        input:focus { border-color: var(--blue); }
        .btn { width: 100%; background: var(--blue); color: #0f172a; border: none; padding: 13px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }
        .success { background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; }
        .danger { background: rgba(248, 113, 113, 0.1); border: 1px solid #f87171; color: #f87171; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { color: var(--text); }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>🔧 Update Admin Settings</h2>
        
        <?php if($message): ?><div class="alert success">✅ <?php echo $message; ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert danger">🛑 <?php echo $error; ?></div><?php endif; ?>
		<form method="POST">
            <div class="form-group">
                <label>Admin Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($admin_data['username']); ?>" required>
            </div>
            <div class="form-group">
                <label>New Password (වෙනස් කිරීමට පමණක් ඇතුළත් කරන්න)</label>
                <input type="password" name="password" placeholder="Leave blank to keep current">
            </div>
            <div class="form-group">
                <label>Security Question (මුරපදය අමතක වූ විට ඇසීමට)</label>
                <input type="text" name="security_question" value="<?php echo htmlspecialchars($admin_data['security_question']); ?>" required>
            </div>
            <div class="form-group">
                <label>Security Answer (ආරක්ෂිත පිළිතුර)</label>
                <input type="text" name="security_answer" value="<?php echo htmlspecialchars($admin_data['security_answer']); ?>" required autocomplete="off">
            </div>
            <button type="submit" class="btn">Save Changes</button>
            <a href="admin_home.php" class="back-link">← Back to Dashboard</a>
        </form>
    </div>
</body>
</html>