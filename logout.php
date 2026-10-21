<?php
// 1. දැනට පවතින Session එක ආරම්භ කිරීම හෝ හඳුනා ගැනීම
session_start();

// 2. සියලුම Session Variables හිස් කිරීම
$_SESSION = array();

// 3. බ්‍රවුසරයේ පවතින Session Cookie එක මකා දැමීම (වඩාත් ආරක්ෂිත ක්‍රමවේදයකි)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. සර්වර් එකෙන් Session එක සම්පූර්ණයෙන්ම විනාශ කිරීම
session_destroy();

// 5. පරිශීලකයා සාර්ථකව ඉවත් වූ බව පෙන්වමින් Login පිටුවට රැගෙන යාම
header("Location: login.php?msg=logout_success");
exit();
?>