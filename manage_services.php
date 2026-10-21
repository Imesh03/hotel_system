<?php 
include 'db_connection.php'; 

// සේවාව බිලට එකතු කිරීමේ ක්‍රියාවලිය
if(isset($_POST['add_service'])){
    $u_id = $_POST['user_id'];
    $i_id = $_POST['item_id'];
    $qty = $_POST['qty'];

    // සේවාවේ මිල ලබා ගැනීම
    $res = $conn->query("SELECT price FROM menu_items WHERE id=$i_id");
    if($res->num_rows > 0){
        $item = $res->fetch_assoc();
        $total = $item['price'] * $qty;

        // ඇණවුමක් ලෙස බිලට එකතු කිරීම (Manual type සහ Status Complete ලෙස)
        $sql = "INSERT INTO orders (user_id, item_id, quantity, total_price, order_type, status) 
                VALUES ('$u_id', '$i_id', '$qty', '$total', 'manual', 'complete')";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success mt-3'>Service charge added to bill!</div>";
        }
    }
}
?>
<html>
<head/>

<link rel="stylesheet" href="style.css">
<header class="admin-navbar">
    <div class="logo">🏨 LUXURY ADMIN</div>
    <nav class="admin-nav-links">
        a href="admin_home.php">Dashboard</a>
        <a href="register_customer.php">New Guest</a>
        <a href="customer_details.php">Guest List</a>
        <a href="kitchen_orders.php" style="color: #e74c3c;">Service</a>
		<a href="manage_items.php" >Manage Menu</a>
		<a href="manage_orders.php">Manage orders</a>
		<a href="admin_about.php" >Manage About</a>
		<a href="manage_feedback.php">Feedback</a>
        <a href="logout.php" >Logout</a>
    </nav>
</header>
</html>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Services - Admin Panel</title>
   <link rel="stylesheet" href="style.css">
</head>
<body class="container mt-5">
    <h2 class="mb-4">Add Services to Guest Bill</h2>

    <div class="card shadow p-4">
        <form action="manage_services.php" method="POST">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Select Guest (Room Number)</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Select Guest --</option>
                        <?php
                        $users = $conn->query("SELECT id, full_name, room_number FROM users WHERE status='active'");
                        while($row = $users->fetch_assoc()) {
                            echo "<option value='".$row['id']."'>Room ".$row['room_number']." - ".$row['full_name']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Select Service</label>
                    <select name="item_id" class="form-select" required>
                        <option value="">-- Select Service --</option>
                        <?php
                        // Category එක Service වන අයිතම පමණක් පෙන්වයි
                        $items = $conn->query("SELECT id, item_name, price FROM menu_items WHERE category='service'");
                        while($row = $items->fetch_assoc()) {
                            echo "<option value='".$row['id']."'>".$row['item_name']." (Rs. ".number_format($row['price'], 2).")</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="qty" value="1" min="1" class="form-control">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" name="add_service" class="btn btn-success w-100">Add to Bill</button>
                </div>
            </div>
        </form>
    </div>

    <div class="mt-5">
        <h4>Recent Manual Service Entries</h4>
        <table class="table table-striped border">
            <thead class="table-dark">
                <tr>
                    <th>Room</th>
                    <th>Guest Name</th>
                    <th>Service Name</th>
                    <th>Total Price</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent = $conn->query("SELECT u.room_number, u.full_name, m.item_name, o.total_price, o.order_date