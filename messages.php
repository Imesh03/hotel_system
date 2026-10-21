<?php
session_start();
include 'db_connection.php';

// checking customer login 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// check customer status
$status_check = $conn->query("SELECT status FROM users WHERE id = '$user_id'")->fetch_assoc();

// 3. Status eka inactive nam system eken logout karanava
if ($status_check['status'] == 0) {
    session_destroy();
    header("Location: login.php?msg=account_deactivated");
    exit();
}
include 'db_connection.php';

// 1. මුලින්ම පාරිභෝගිකයා ලොග් වී ඇත්දැයි බලන්න
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. දැන් පාරිභෝගිකයාගේ status එක database එකෙන් පරීක්ෂා කරන්න
$status_check = $conn->query("SELECT status FROM users WHERE id = '$user_id'")->fetch_assoc();

// 3. Status එක inactive නම් වහාම ලොග් අවුට් කරන්න
if ($status_check && $status_check['status'] == 'inactive') {
    session_destroy();
    header("Location: login.php?msg=account_deactivated");
    exit();
}

// කියවා නැති පණිවිඩ සංඛ්‍යාව ගණනය කිරීම
$unread_query = $conn->query("SELECT COUNT(*) as unread FROM messages WHERE user_id = $user_id AND is_read = 0");
$unread_data = $unread_query->fetch_assoc();
$unread_count = $unread_data['unread'];

// පිටුවට පැමිණි පසු සියලුම පණිවිඩ "කියවූ" ලෙස ලකුණු කිරීම
if ($unread_count > 0) {
    $conn->query("UPDATE messages SET is_read = 1 WHERE user_id = $user_id");
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> My Messages</title>
    <style>
        /* Consistent Dark Luxury Theme */
        body {
            margin: 0;
            padding: 0;
            background: #0f172a;
            background-image: radial-gradient(circle at top right, #1e293b, #0f172a);
            color: #f1f5f9;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            overflow-x: hidden;
        }

        /* Consistent Navigation Bar */
        nav {
            display: flex;
            justify-content: center;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(15px);
            padding: 15px;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        nav a {
            color: #94a3b8;
            text-decoration: none;
            padding: 10px 20px;
            font-weight: 600;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 13px;
        }

        nav a:hover {
            color: #38bdf8;
            text-shadow: 0 0 8px rgba(56, 189, 248, 0.5);
        }

        /* Sub Sticky Header for Title & Badge */
        .top-nav {
            position: sticky;
            top: 65px;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(10px);
            max-width: 700px;
            margin: 30px auto 0 auto;
            padding: 15px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            z-index: 999;
        }

        .page-title {
            color: #38bdf8;
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Unread Notifications Badge */
        .badge {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 5px 14px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.2);
        }

        /* Messages Content Container */
        .content-area {
            max-width: 700px;
            margin: 25px auto;
            padding: 0 20px 60px 20px;
        }
		/* Glassmorphic Message Card */
        .message-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            margin-bottom: 16px;
            padding: 22px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border-left: 4px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .message-card:hover {
            transform: translateY(-4px);
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(56, 189, 248, 0.3);
        }

        /* Highlight Style for Unread Messages */
        .unread-card {
            border-left-color: #38bdf8;
            background: rgba(56, 189, 248, 0.03);
            box-shadow: 0 10px 30px rgba(56, 189, 248, 0.08);
        }

        .message-text {
            color: #e2e8f0;
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .message-time {
            font-size: 0.78rem;
            color: #64748b;
            text-align: right;
            font-weight: 500;
        }

        /* Empty State Layout */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
        }

        /* Elegant Webkit Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 10px;
            border: 2px solid #0f172a;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #38bdf8;
        }
    </style>
</head>
<body>

    <nav>
        <a href="welcome.php">Home</a>
        <a href="index.php">Order Food</a>
        <a href="get_service.php">Get Service</a>
        <a href="live_bill.php">Bill</a>
        <a href="about_us.php">About us</a>
        <a href="messages.php" style="color: #38bdf8;">Messages</a>
        
        <a href="public_feedback.php">Reviews</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="top-nav">
        <h1 class="page-title">Notifications 📭</h1>
        <div>
            <?php if($unread_count > 0): ?>
                <span class="badge"><?php echo $unread_count; ?> New</span>
            <?php else: ?>
                <span style="color: #64748b; font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">All Read</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="content-area">
        <?php
        $msg_sql = "SELECT * FROM messages WHERE user_id = $user_id ORDER BY id DESC";
        $msg_res = $conn->query($msg_sql);

        if ($msg_res && $msg_res->num_rows > 0):
            while($msg = $msg_res->fetch_assoc()):
                // නව පණිවිඩයක් නම් ඊට අදාළ CSS Class එක එකතු කරයි
                $card_class = ($msg['is_read'] == 0) ? 'unread-card' : '';
        ?>
            <div class="message-card <?php echo $card_class; ?>">
                <div class="message-text">
                    <?php echo htmlspecialchars($msg['message']); ?>
                </div>
                <div class="message-time">
                    ⏱️ <?php echo date('M d, Y | h:i A', strtotime($msg['created_at'])); ?>
                </div>
            </div>
        <?php 
            endwhile;
        else:
        ?>
            <div class="empty-state">
                <p style="font-size: 3.5rem; margin: 0 0 15px 0;">📭</p>
                <p style="font-size: 1.1rem; margin: 0;">ඔබට තවමත් පණිවිඩ කිසිවක් ලැබී නැත.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>
</html>