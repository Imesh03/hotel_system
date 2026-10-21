<?php 
include 'db_connection.php'; 

$message = ""; // Message 

if(isset($_POST['submit'])){
    $name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $price = $_POST['price'];
    $cat = $_POST['category'];
    $sub_cat = ($cat == 'food') ? $_POST['sub_category'] : 'none';
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    // Image Upload Logic
    $image_name = time() . '_' . $_FILES['item_image']['name']; 
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image_name);

    // uploads naththan auto hdenv
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($_FILES['item_image']['tmp_name'], $target_file)) {
        $sql = "INSERT INTO menu_items (item_name, price, image_path, category, sub_category, description, is_available) 
                VALUES ('$name', '$price', '$image_name', '$cat', '$sub_cat', '$desc', 1)";

        if ($conn->query($sql) === TRUE) {
            $message = "<div class='alert alert-success'>New item added successfully! ✅</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . " ❌</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Image upload failed! ❌</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Item - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Premium Luxury Dark Theme Variables */
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
            --input-focus: #1e293b;
            --nav-bg: rgba(19, 28, 46, 0.7);
        }

        body { 
            background: var(--primary-bg); 
            font-family: 'Segoe UI', Roboto, sans-serif; 
            color: var(--text-main);
            margin: 0; 
            padding: 0; 
        }

        /* 🚀 Floating Glassmorphism Navigation Bar Styles */
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

        /* Active State Style (Manage Menu) */
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

        /* Container Setup */
        .container { 
            width: 100%; 
            max-width: 850px; 
            margin: 120px auto 50px; 
            padding: 0 24px;
            box-sizing: border-box;
        }

        /* Header Card Style */
        .header { 
            background: var(--card-bg); 
            color: var(--text-main); 
            padding: 25px 30px; 
            border-radius: 16px; 
            margin-bottom: 25px; 
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header h1 { margin: 0; font-size: 1.6rem; font-weight: 700; }

        /* 📋 Form Card Design */
        .form-card {
            background: var(--card-bg);
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-col {
            flex: 1;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            color: var(--accent-blue);
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 9px;
            letter-spacing: 0.8px;
        }

        .form-control {
            width: 100%;
            padding: 13px 16px;
            background: #090d16;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            color: var(--text-main);
            outline: none;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.15);
            background: var(--input-focus);
        }

        textarea.form-control {
            resize: vertical;
            font-family: inherit;
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'><path fill='%2394a3b8' d='M0 0l5 5 5-5z'/></svg>");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 12px;
            padding-right: 40px;
        }

        /* 📂 Custom File Input Style */
        input[type="file"] {
            padding: 9px 12px;
        }
        input[type="file"]::file-selector-button {
            background: var(--border-color);
            color: #cbd5e1;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 12px;
            transition: 0.2s;
            font-weight: 600;
            font-size: 0.85rem;
        }
        input[type="file"]::file-selector-button:hover {
            background: var(--accent-blue);
            color: var(--primary-bg);
        }

        /* Button Layout Configuration */
        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            padding: 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            display: block;
            width: 100%;
            text-align: center;
            transition: 0.3s ease;
            box-sizing: border-box;
        }

        .btn-primary {
            background: transparent;
            color: var(--accent-gold);
            border: 1px solid var(--accent-gold);
        }

        .btn-primary:hover {
            background: var(--accent-gold);
            color: var(--primary-bg);
            box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
        }

        .btn-secondary {
            background: rgba(148, 163, 184, 0.08);
            color: var(--text-muted);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: #1e293b;
            color: var(--text-main);
            border-color: var(--text-muted);
        }

        /* 🔔 Alert Boxes Styling */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-green);
            color: #34d399;
            border-left: 5px solid var(--success-green);
        }

        .alert-danger {
            background-color: rgba(248, 113, 113, 0.1);
            border: 1px solid var(--danger-red);
            color: var(--danger-red);
            border-left: 5px solid var(--danger-red);
        }

        /* Responsive Design Rules */
        @media (max-width: 1100px) {
            .admin-navbar { position: static; transform: none; width: 100%; margin: 10px 0; flex-direction: column; gap: 12px; padding: 15px; border-radius: 10px; }
            .container { margin-top: 20px; }
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            .form-card {
                padding: 22px;
            }
            .header {
                text-align: center;
                justify-content: center;
            }
        }
    </style>
    <script>
        function checkSubCat() {
            var mainCat = document.getElementById("main_category").value;
            var subDiv = document.getElementById("sub_cat_div");
            if (mainCat === "food") {
                subDiv.style.display = "block";
            } else {
                subDiv.style.display = "none";
            }
        }
        
        window.onload = function() {
            checkSubCat();
        };
    </script>
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
            <a href="manage_items.php" class="active">Menu</a>
            <a href="manage_orders.php">Orders</a>
            <a href="admin_about.php">About</a>
            <a href="manage_feedback.php">Feedback</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

<div class="container">
    <div class="header">
        <h1>Add New Foods ✨</h1>
    </div>

    <!-- Alerts Display Section -->
    <?php if(!empty($message)) echo $message; ?>

    <div class="form-card">
        <form action="add_item.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" name="item_name" class="form-control" placeholder="Enter food or beverage name" required>
            </div>

            <div class="form-row">
                <div class="form-col form-group">
                    <label class="form-label">Price (Rs.)</label>
                    <input type="number" name="price" step="0.01" class="form-control" placeholder="0.00" required>
                </div>
                <div class="form-col form-group">
                    <label class="form-label">Item Image</label>
                    <input type="file" name="item_image" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col form-group">
                    <label class="form-label">Main Category</label>
                    <select name="category" id="main_category" class="form-control" onchange="checkSubCat()" required>
                        <option value="food">Food</option>
                        <option value="beverages">Beverages</option>
                        <option value="snacks">Snacks (Fast Food)</option>
                        <option value="service">Hotel Service</option>
                    </select>
                </div>
                <div class="form-col form-group" id="sub_cat_div">
                    <label class="form-label">Sub Category (For Food Only)</label>
                    <select name="sub_category" class="form-control">
                        <option value="breakfast">Breakfast</option>
                        <option value="lunch">Lunch</option>
                        <option value="dinner">Dinner</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Brief details about the item or service..."></textarea>
            </div>

            <div class="btn-container">
                <button type="submit" name="submit" class="btn btn-primary">Save Menu Item</button>
                <a href="manage_items.php" class="btn btn-secondary">Go to Manage Items</a>
            </div>
        </form>
    </div>
</div>

</body>
<footer style="background: rgba(15, 23, 42, 0.6); border-top: 1px solid rgba(255,255,255,0.05); color: #64748b; text-align: center; padding: 30px; margin-top: 50px;">
    <p>&copy; 2026 Powered by Hotel Panama Luxury Systems | All Rights Reserved.</p>
</footer>
</html>