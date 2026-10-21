<link rel="stylesheet" href="style.css">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow py-3">
    <div class="container-fluid px-5">
        <a class="navbar-brand fw-bold fs-3" href="admin_home.php">
            <i class="fa-solid fa-hotel text-warning me-2"></i>LUXURY HOTEL <span class="text-warning">ADMIN</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-2">
                <li class="nav-item">
                    <a class="nav-link px-3 active" href="admin_home.php"><i class="fa-solid fa-chart-line me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="register_customer.php"><i class="fa-solid fa-user-plus me-1"></i> New Registration</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="customer_details.php"><i class="fa-solid fa-users-gear me-1"></i> Guest Management</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="kitchen_orders.php"><i class="fa-solid fa-utensils me-1"></i> Kitchen & Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="manage_reviews.php"><i class="fa-solid fa-star me-1"></i> Reviews</a>
                </li>
                <li class="nav-item ms-lg-3">
                    <a class="btn btn-danger px-4 rounded-pill" href="logout.php"><i class="fa-solid fa-right-from-bracket me-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<html>
<head/>
<link rel="stylesheet" href="style.css">
<header class="admin-navbar">
    <div class="logo">🏨 LUXURY ADMIN</div>
    <nav class="admin-nav-links">
        <a href="admin_home.php">Dashboard</a>
        <a href="register_customer.php">New Guest</a>
        <a href="customer_details.php">Guest List</a>
        <a href="kitchen_orders.php">Orders</a>
        <a href="logout.php" style="color: #e74c3c;">Logout</a>
    </nav>
</header>
</html>