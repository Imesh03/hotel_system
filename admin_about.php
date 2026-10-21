<?php
$conn = mysqli_connect("localhost", "root", "", "hotel_db");

// 1. auto check and create folder
$target_dir = "uploads/about-us/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// data Insert/Update
if(isset($_POST['save_text'])) {
    $section = $_POST['section'];
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    
    $check = mysqli_query($conn, "SELECT id FROM about_us_content WHERE section_key='$section'");
    if(mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE about_us_content SET content='$content' WHERE section_key='$section'");
    } else {
        mysqli_query($conn, "INSERT INTO about_us_content (section_key, content) VALUES ('$section', '$content')");
    }
    header("Location: admin_about.php");
    exit();
}

// Media Uploading
if(isset($_POST['upload_media'])) {
    $file_name = time() . "_" . basename($_FILES["media"]["name"]);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if(move_uploaded_file($_FILES["media"]["tmp_name"], $target_file)) {
        $media_category = (in_array($file_type, ['mp4', 'webm', 'ogg'])) ? 'video' : 'image';
        mysqli_query($conn, "INSERT INTO about_us_content (section_key, content, media_type) VALUES ('media', '$file_name', '$media_category')");
    }
    header("Location: admin_about.php");
    exit();
}

// data Delete
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // File eka folder ekenut delete krnva
    $file_res = mysqli_query($conn, "SELECT content FROM about_us_content WHERE id=$id AND section_key='media'");
    if($f = mysqli_fetch_assoc($file_res)) {
        unlink($target_dir . $f['content']);
    }
    mysqli_query($conn, "DELETE FROM about_us_content WHERE id=$id");
    header("Location: admin_about.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium About-Us Admin — Luxury Theme</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* 🎨 Premium Luxury Dark Theme Variables (Matching Command Center) */
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

        /* 🚀 Floating Glassmorphism Navigation Bar */
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

        /* Active State Style (Manage About) */
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

        .main-container {
            max-width: 1300px;
            margin: 120px auto 40px; 
            padding: 0 25px;
            box-sizing: border-box;
        }

        /* Luxury Header Title Container */
        .header-panel {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }

        .header-panel h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: 0.5px;
        }

        .header-panel h1 span {
            color: var(--accent-gold);
        }

        .header-panel p {
            margin: 8px 0 0 0; 
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Admin Grid Setup */
        .admin-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); 
            gap: 25px; 
        }
        
        /* Glassmorphism Luxury Windows */
        .window { 
            background: var(--card-bg); 
            border: 1px solid var(--border-color);
            border-radius: 16px; 
            padding: 25px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); 
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .window:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            border-color: #3b5278;
        }
        
        .window h2 { 
            color: var(--text-main); 
            font-size: 1.25rem; 
            margin-top: 0; 
            margin-bottom: 20px;
            border-bottom: 1px dashed var(--border-color); 
            padding-bottom: 14px; 
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .window h2 span {
            color: var(--accent-gold);
            font-weight: 700;
        }
        
        /* Inputs & Textareas */
        textarea { 
            width: 100%; 
            height: 120px; 
            background: var(--input-bg); 
            border: 1px solid var(--border-color); 
            color: var(--text-main); 
            border-radius: 8px; 
            padding: 12px; 
            box-sizing: border-box; 
            margin-bottom: 20px; 
            font-size: 0.95rem;
            line-height: 1.6;
            outline: none;
            resize: vertical;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        textarea::placeholder {
            color: #475569;
        }

        textarea:focus { 
            border-color: var(--accent-gold);
            box-shadow: 0 0 8px rgba(251, 191, 36, 0.15);
        }

        /* Custom File Input Styling */
        input[type="file"] {
            width: 100%;
            padding: 12px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-muted);
            margin-bottom: 20px;
            font-size: 0.9rem;
            box-sizing: border-box;
            outline: none;
            cursor: pointer;
        }

        input[type="file"]::-webkit-file-upload-button {
            background: rgba(248, 250, 252, 0.05);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 6px 12px;
            cursor: pointer;
            margin-right: 10px;
            transition: 0.2s;
            font-weight: 600;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background: rgba(248, 250, 252, 0.15);
        }

        /* Buttons Styling */
        button { 
            width: 100%; 
            padding: 13px; 
            border-radius: 8px; 
            border: none; 
            cursor: pointer; 
            font-weight: 700; 
            font-size: 0.9rem;
            transition: 0.2s ease; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-update { 
            background: transparent; 
            color: var(--accent-gold); 
            border: 1px solid var(--accent-gold);
        }

        .btn-update:hover { 
            background: var(--accent-gold); 
            color: #090d16;
            box-shadow: 0 0 12px rgba(251, 191, 36, 0.25);
        }
        
        .btn-media { 
            background: transparent; 
            color: #34d399; 
            border: 1px solid rgba(16, 185, 129, 0.6);
        }

        .btn-media:hover { 
            background: var(--success-green); 
            color: white;
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.25);
        }

        /* Media Items Table/List View */
        .item-list { 
            margin-top: 20px; 
            max-height: 170px; 
            overflow-y: auto; 
            font-size: 0.88rem; 
            border-top: 1px dashed var(--border-color);
            padding-top: 15px;
        }

        /* Custom scrollbar for list */
        .item-list::-webkit-scrollbar {
            width: 6px;
        }

        .item-list::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }
        
        .item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            background: var(--input-bg); 
            padding: 10px 14px; 
            margin-bottom: 8px; 
            border-radius: 6px; 
            border: 1px solid var(--border-color);
        }

        .item span {
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 65%;
            font-weight: 500;
        }

        .delete-link { 
            color: var(--danger-red); 
            text-decoration: none; 
            font-weight: 700; 
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid rgba(248, 113, 113, 0.2);
            transition: 0.2s;
        }

        .delete-link:hover { 
            background: var(--danger-red);
            color: white;
            box-shadow: 0 0 8px rgba(248, 113, 113, 0.2);
        }

        /* Responsive Navbar Optimization */
        @media (max-width: 1150px) {
            .admin-navbar { position: static; transform: none; width: 100%; margin: 10px 0; flex-direction: column; gap: 12px; padding: 15px; border-radius: 10px; }
            .main-container { margin-top: 20px; }
        }

        @media (max-width: 768px) {
            .admin-grid {
                grid-template-columns: 1fr;
            }
            .header-panel {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- 🚀 Floating Glassmorphism Navbar Component -->
    <header class="admin-navbar">
        <div class="logo">PANAMA <span>CONTROL</span></div>
        <nav class="admin-nav-links">
            <a href="admin_home.php">Dashboard</a>
            <a href="register_customer.php">New Guest</a>
            <a href="customer_details.php">Guest List</a>
            <a href="kitchen_orders.php">Service</a>
            <a href="manage_items.php">Menu</a>
            <a href="manage_orders.php">Orders</a>
            <a href="admin_about.php" class="active">About</a>
            <a href="manage_feedback.php">Feedback</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <div class="main-container">

        <div class="header-panel">
            <h1>About-Us <span>Creative Management</span></h1>
            <p>Customize and manage information on the hotel profile web page</p>
        </div>

        <div class="admin-grid">

            <!-- Our Story Window -->
            <div class="window">
                <h2>📖 <span>Our Story</span></h2>
                <form method="POST">
                    <input type="hidden" name="section" value="story">
                    <?php 
                    $q = mysqli_query($conn, "SELECT content FROM about_us_content WHERE section_key='story'");
                    $res = mysqli_fetch_assoc($q);
                    ?>
                    <textarea name="content" placeholder="Describe the magnificent journey of the hotel..."><?php echo htmlspecialchars($res['content'] ?? ''); ?></textarea>
                    <button type="submit" name="save_text" class="btn-update">Update Story</button>
                </form>
            </div>

            <!-- Our Vision Window -->
            <div class="window">
                <h2>👁️ <span>Our Vision</span></h2>
                <form method="POST">
                    <input type="hidden" name="section" value="vision">
                    <?php 
                    $q = mysqli_query($conn, "SELECT content FROM about_us_content WHERE section_key='vision'");
                    $res = mysqli_fetch_assoc($q);
                    ?>
                    <textarea name="content" placeholder="What is your ultimate luxury goal?"><?php echo htmlspecialchars($res['content'] ?? ''); ?></textarea>
                    <button type="submit" name="save_text" class="btn-update">Update Vision</button>
                </form>
            </div>

            <!-- Why Choose Us Window -->
            <div class="window">
                <h2>🌟 <span>Why Choose Us</span></h2>
                <form method="POST">
                    <input type="hidden" name="section" value="why_us">
                    <?php 
                    $q = mysqli_query($conn, "SELECT content FROM about_us_content WHERE section_key='why_us'");
                    $res = mysqli_fetch_assoc($q);
                    ?>
                    <textarea name="content" placeholder="List unique premium features..."><?php echo htmlspecialchars($res['content'] ?? ''); ?></textarea>
                    <button type="submit" name="save_text" class="btn-update">Update Features</button>
                </form>
            </div>

            <!-- Media Upload Window -->
            <div class="window">
                <div>
                    <h2>🖼️ <span>Media Gallery</span></h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="file" name="media" required>
                        <button type="submit" name="upload_media" class="btn-media">Upload New Media</button>
                    </form>
                </div>
                
                <div class="item-list">
                    <?php
                    $media_res = mysqli_query($conn, "SELECT * FROM about_us_content WHERE section_key='media' ORDER BY id DESC");
                    if(mysqli_num_rows($media_res) == 0) {
                        echo "<div style='color: var(--text-muted); text-align:center; padding: 15px; font-weight: 500;'>No media uploaded yet.</div>";
                    }
                    while($m = mysqli_fetch_assoc($media_res)) {
                        echo "<div class='item'>
                                <span title='".htmlspecialchars($m['content'])."'>".htmlspecialchars($m['content'])."</span>
                                <a href='admin_about.php?delete={$m['id']}' class='delete-link' onclick='return confirm(\"Are you sure you want to delete this media?\")'>Delete</a>
                              </div>";
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

</body>
<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>
</html>