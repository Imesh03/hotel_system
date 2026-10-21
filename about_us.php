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

// 3. Status eka inactive (0) nam system eken logout karanava
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

// Database eken txt labagnima සඳහා ශ්‍රිතය (Object-Oriented ක්‍රමයට හෝ Procedural ක්‍රමයට ගැළපෙන සේ)
function getContent($conn, $key) {
    $safe_key = mysqli_real_escape_string($conn, $key);
    $res = mysqli_query($conn, "SELECT content FROM about_us_content WHERE section_key='$safe_key' LIMIT 1");
    $data = mysqli_fetch_assoc($res);
    return $data ? $data['content'] : "Information will be updated soon.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Panama Luxury Hotel</title>
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
            position: relative; /* Notification Dot එක position කිරීමට */
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
            background-color: #ef4444; /* Glowing Red */
            border-radius: 50%;
            box-shadow: 0 0 8px #ef4444, 0 0 15px #ef4444;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Container */
        .main-container {
            max-width: 1100px;
            margin: 50px auto;
            padding: 0 20px;
        }

        /* Compact Luxury Slideshow Window */
        .slideshow-window {
            position: relative;
            max-width: 800px; 
            height: 400px;    
            margin: 0 auto 50px;
            overflow: hidden;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
            background: rgba(255, 255, 255, 0.01);
        }

        .slide {
            display: none;
            width: 100%;
            height: 100%;
            animation: fadeEffect 1.2s ease-in-out;
        }

        .slide img, .slide video {
            width: 100%;
            height: 100%;
            object-fit: cover; 
        }

        @keyframes fadeEffect {
            from { opacity: 0; transform: scale(1.03); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Content Boxes Layout Grid */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }

        /* Glassmorphic Info Boxes */
        .info-box {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            padding: 40px 30px;
            border-radius: 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .info-box:hover {
            transform: translateY(-12px);
            background: rgba(255, 255, 255, 0.05);
            border-color: #38bdf8;
            box-shadow: 0 20px 40px rgba(56, 189, 248, 0.15);
        }

        .info-box h2 {
            color: #38bdf8;
            font-size: 1.4rem;
            margin-top: 0;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.2);
        }

        .info-box p {
            color: #94a3b8;
            line-height: 1.8;
            font-size: 0.98rem;
            margin: 0;
        }
    </style>
</head>
<body>

    <nav>
        <a href="welcome.php">Home</a>
        <a href="index.php">Order Food</a>
        <a href="get_service.php">Get Service</a>
        <a href="live_bill.php">Bill</a>
        <a href="about_us.php" style="color: #38bdf8;">About us</a>
        
        <!-- Notification dot logic -->
        <a href="messages.php" class="<?php echo $has_unread ? 'notification-dot' : ''; ?>">Messages</a>
        
        <a href="public_feedback.php">Reviews</a>
        <a href="logout.php">Logout</a>
    </nav>

    <div class="main-container">
        
        <div class="slideshow-window">
            <?php
            $media_query = mysqli_query($conn, "SELECT * FROM about_us_content WHERE section_key='media' ORDER BY id DESC");
            if(mysqli_num_rows($media_query) > 0) {
                while($row = mysqli_fetch_assoc($media_query)) {
                    echo "<div class='slide'>";
                    if($row['media_type'] == 'video') {
                        echo "<video src='uploads/about-us/{$row['content']}' autoplay muted loop></video>";
                    } else {
                        echo "<img src='uploads/about-us/{$row['content']}' alt='Hotel Gallery'>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<div class='slide' style='display:flex; background:#1e293b; align-items:center; justify-content:center; height: 100%;'>
                        <p style='color:#475569; font-weight: 500;'>No media uploaded yet.</p>
                      </div>";
            }
            ?>
        </div>

        <div class="content-grid">
            <div class="info-box">
                <h2>Our Story</h2>
                <p><?php echo getContent($conn, 'story'); ?></p>
            </div>

            <div class="info-box">
                <h2>Our Vision</h2>
                <p><?php echo getContent($conn, 'vision'); ?></p>
            </div>

            <div class="info-box">
                <h2>Why Choose Us</h2>
                <p><?php echo getContent($conn, 'why_us'); ?></p>
            </div>
        </div>

    </div>

    <footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
        <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
    </footer>

    <script>
        let slideIndex = 0;
        const slides = document.getElementsByClassName("slide");

        function showSlides() {
            if (slides.length === 0) return;

            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }
            
            slideIndex++;
            if (slideIndex > slides.length) { slideIndex = 1; }    
            
            if (slides[slideIndex - 1]) {
                slides[slideIndex - 1].style.display = "block";  
            }
            
            // photos 5s vlin maru vna eka
            setTimeout(showSlides, 5000); 
        }

        if(slides.length > 0) {
            showSlides();
        }
    </script>

</body>
</html>