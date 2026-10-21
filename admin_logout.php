<?php
// 1. Session එක ආරම්භ කිරීම
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Admin ට අදාළ Session අගයන් (Variables) පමණක් හිස් කිරීම
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_user']);

// 3. Admin ට අදාළ දත්ත විනාශ කිරීමෙන් පසු, කෙලින්ම 'admin_login.php' පිටුවට හරවා යැවීම
header("Location: admin_login.php");
exit;
?>