<?php
session_start();
include 'db_connection.php';

// 1. checking customer login 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. check customer status
$status_check = $conn->query("SELECT status FROM users WHERE id = '$user_id'")->fetch_assoc();

// 3. Status eka inactive nam system eken logout karanava
if ($status_check['status'] == 0) {
    session_destroy();
    header("Location: login.php?msg=account_deactivated");
    exit();
}

// --- 4. Unread messages check කිරීම (Notification Dot සඳහා) ---
$has_unread = false;
$unread_query = $conn->query("SELECT id FROM messages WHERE user_id = '$user_id' AND is_read = 0 LIMIT 1");
if ($unread_query && $unread_query->num_rows > 0) {
    $has_unread = true;
}

// Public ayata admin approve krpu eva vitry pennanna oni
$public_feedback = $conn->query("SELECT f.*, u.full_name FROM feedback f JOIN users u ON f.user_id = u.id WHERE f.is_visible = 1 ORDER BY f.created_at DESC");
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Reviews & Feedback | Panama Luxury</title>
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
            position: relative;
        }

        nav a:hover {
            color: #38bdf8;
            text-shadow: 0 0 8px rgba(56, 189, 248, 0.5);
        }

        /* Creative Notification Red Dot Style */
        .notification-dot::after {
            content: '';
            position: absolute;
            top: 6px;
            right: 8px;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            box-shadow: 0 0 8px #ef4444, 0 0 15px #ef4444;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Glassmorphic Feedback Submission Container */
        .feedback-form-box { 
            max-width: 650px; 
            margin: 0 auto 50px auto; 
            padding: 35px; 
            background: rgba(255, 255, 255, 0.02); 
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 24px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .section-title {
            color: #38bdf8;
            font-size: 1.5rem;
            margin-top: 0;
            margin-bottom: 25px;
            border-left: 4px solid #38bdf8;
            padding-left: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Form Inputs Style */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .custom-select, .custom-textarea {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
            padding: 12px;
            border-radius: 12px;
            font-size: 0.95rem;
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
        }

        .custom-select:focus, .custom-textarea:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.15);
        }

        .custom-textarea {
            height: 110px;
            resize: none;
            font-family: inherit;
        }

        /* File Upload Input styling */
        .file-input-wrapper {
            background: rgba(255, 255, 255, 0.01);
            border: 1px dashed rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
        }

        /* Action Buttons */
        .btn-submit {
            width: 100%;
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 14px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            background: #38bdf8;
            color: #0f172a;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.4);
        }

        /* Alert Notification */
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
        }

        /* Feedback Grid Layout */
        .feedback-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); 
            gap: 25px; 
            margin-top: 20px;
        }

        .feedback-card { 
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            padding: 28px; 
            border-radius: 24px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-top: 3px solid #38bdf8;
        }

        .feedback-card:hover { 
            transform: translateY(-8px); 
            background: rgba(255, 255, 255, 0.04);
            box-shadow: 0 15px 35px rgba(56, 189, 248, 0.15);
        }

        .customer-name { 
            font-weight: 600; 
            color: #f1f5f9; 
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px; 
        }

        .stars-row {
            color: #fbbf24; 
            margin-bottom: 14px; 
            font-size: 1.1rem;
            letter-spacing: 2px;
        }

        .feedback-card p {
            color: #94a3b8;
            line-height: 1.6;
            font-size: 0.95rem;
            margin: 0 0 15px 0;
            word-wrap: break-word;
        }

        /* Media Display Window Inside Cards */
        .media-box { 
            margin-top: 15px; 
            border-radius: 14px; 
            overflow: hidden; 
            border: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(0,0,0,0.2);
            max-height: 180px;
        }
        
        .media-box img, .media-box video { 
            width: 100%; 
            height: 100%;
            max-height: 180px;
            object-fit: cover; 
            display: block;
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
        
        <!-- Messages Link with Dynamic Red Dot -->
        <a href="messages.php" class="<?php echo $has_unread ? 'notification-dot' : ''; ?>">Messages</a>
        
        <a href="public_feedback.php" style="color: #38bdf8;">Reviews</a>
        <a href="logout.php">Logout</a>
    </nav>
  
<div class="container">
    <div class="feedback-form-box">
        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert-success">✅ Your feedback submitted successfully! It will be visible once approved.</div>
        <?php endif; ?>

        <h2 class="section-title">Share Your Experience</h2>
        
        <form action="submit_feedback.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Rating :</label>
                <select name="rating" class="custom-select" required>
                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                    <option value="4">⭐⭐⭐⭐ Good</option>
                    <option value="3">⭐⭐⭐ Average</option>
                    <option value="2">⭐⭐ Poor</option>
                    <option value="1">⭐ Very Bad</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Your Message :</label>
                <textarea name="message" class="custom-textarea" placeholder="ඔබේ අදහස හෝ අත්දැකීම මෙහි ලියන්න..." required></textarea>
            </div>
            
            <div class="form-group">
                <label>Upload Image or Video :</label>
                <div class="file-input-wrapper">
                    <input type="file" name="media" accept="image/*,video/*" style="color: #94a3b8; font-size: 0.85rem;">
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Submit Review</button>
        </form>
    </div>

    <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.05); margin-bottom: 40px;">

    <h2 class="section-title" style="margin-bottom: 30px;">Guest Reviews & Stories</h2>
    
    <div class="feedback-grid">
        <?php if($public_feedback && $public_feedback->num_rows > 0): ?>
            <?php while($row = $public_feedback->fetch_assoc()): ?>
                <div class="feedback-card">
                    <span class="customer-name">👤 <?php echo htmlspecialchars($row['full_name']); ?></span>
                    
                    <div class="stars-row">
                        <?php echo str_repeat("★", $row['rating']); ?>
                    </div>
                    
                    <p><?php echo htmlspecialchars($row['message']); ?></p>
                    
                    <?php if(!empty($row['file_path'])): ?>
                        <div class="media-box">
                            <?php if($row['file_type'] == 'image'): ?>
                                <img src="uploads/feedback/<?php echo $row['file_path']; ?>" alt="Guest feedback image">
                            <?php else: ?>
                                <video controls preload="metadata">
                                    <source src="uploads/feedback/<?php echo $row['file_path']; ?>" type="video/mp4">
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; color: #64748b; padding: 40px;">
                <p style="font-size: 2.5rem; margin: 0 0 10px 0;">✨</p>
                <p>තවමත් කිසිදු පාරිභෝගික සමාලෝචනයක් ලබා දී නොමැත.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>

</body>
</html>