<?php
require_once 'db_connect.php';

unset($_SESSION['auth_user'], $_SESSION['auth_name'], $_SESSION['auth_role'], $_SESSION['login_at']);

$_SESSION['flash_message'] = "You have been logged out successfully.";
$_SESSION['flash_type']    = "success";

header("Location: login.php");
exit;
?>
