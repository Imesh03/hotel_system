<?php
// Database Connection
$conn = mysqli_connect("localhost", "root", "", "hotel_db");

// 1. Upload logic
if(isset($_POST['upload'])) {
    $type = $_POST['type'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    
    if($type == 'text') {
        $content = mysqli_real_escape_string($conn, $_POST['content_text']);
    } else {
        // පින්තූර සහ වීඩියෝ uploads/welcome ෆෝල්ඩරයට යැවීම
        $target_dir = "uploads/welcome/";
        $file_name = time() . "_" . basename($_FILES["file"]["name"]); 
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $content = $target_file;
        }
    }
    
    $sql = "INSERT INTO hotel_content (type, content_path, title, is_active) VALUES ('$type', '$content', '$title', 1)";
    mysqli_query($conn, $sql);
    header("Location: admin.php"); // Refresh page
}

// 2. Action logic (Delete/Hide)
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM hotel_content WHERE id=$id");
    header("Location: admin.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hotel Admin - Creative Panel</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .admin-header { text-align: center; color: #333; margin-bottom: 30px; }
        
        .windows-container { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        
        /* Windows Style Cards */
        .window { 
            background: white; border-radius: 12px; width: 350px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden;
            border-top: 5px solid #007bff;
        }
        .window-header { background: #f8f9fa; padding: 10px 15px; font-weight: bold; border-bottom: 1px solid #ddd; }
        .window-body { padding: 20px; }
        
        input, select, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #218838; }

        /* Data Table Style */
        .data-table { width: 100%; margin-top: 20px; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .btn-delete { color: red; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<h1 class="admin-header">Hotel Content Manager</h1>

<div class="windows-container">
    <div class="window">
        <div class="window-header">🖼️ Add Media (Photo/Video)</div>
        <div class="window-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Media Title" required>
                <select name="type">
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
                <input type="file" name="file" required>
                <button type="submit" name="upload">Upload to Welcome Folder</button>
            </form>
        </div>
    </div>

    <div class="window" style="border-top-color: #ffc107;">
        <div class="window-header">📝 Add Text / Special Offers</div>
        <div class="window-body">
            <form method="POST">
                <input type="hidden" name="type" value="text">
                <input type="text" name="title" placeholder="Heading/Title" required>
                <textarea name="content_text" rows="4" placeholder="Write description here..." required></textarea>
                <button type="submit" name="upload" style="background:#ffc107; color:black;">Publish Text</button>
            </form>
        </div>
    </div>
</div>
<h2 style="text-align: center; margin-top: 40px;">Current Active Content</h2>
<table class="data-table">
    <thead style="background: #333; color: white;">
        <tr>
            <th>Type</th>
            <th>Title</th>
            <th>Preview</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $res = mysqli_query($conn, "SELECT * FROM hotel_content");
        while($row = mysqli_fetch_assoc($res)) {
            $preview = ($row['type'] == 'text') ? "Text Content" : "<img src='{$row['content_path']}' width='50'>";
            echo "<tr>
                <td>" . ucfirst($row['type']) . "</td>
                <td>{$row['title']}</td>
                <td>$preview</td>
                <td><a href='admin.php?delete={$row['id']}' class='btn-delete'>Delete</a></td>
            </tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>
