<?php
/**
 * noontech - Logout Action Script
 * Destroys credentials tracking and returns home.
 */
session_start();
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Start a fresh session to deliver logout toast message
session_start();
$_SESSION['login_success'] = "Successfully logged out. Hope to design with you again!";
header("Location: login.php");
exit;
?>