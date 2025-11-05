<?php
define('MOONHERITAGE_ACCESS', true);
require_once 'config.php';


if (isLoggedIn()) {
    logActivity(getUserId(), 'logout', 'User logged out');
}


$_SESSION = array();


if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}


if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}


session_destroy();


redirect('index.php');
?>