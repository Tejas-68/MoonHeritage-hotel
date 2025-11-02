<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';

// Log the logout activity before destroying session
if (isLoggedIn()) {
    logActivity(getUserId(), 'logout', 'User logged out');
}

// Unset all session variables
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Delete remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to home page
redirect('index.php');
?>