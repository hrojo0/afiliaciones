<?php 
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
session_destroy();
header('Location: login.php');
?>