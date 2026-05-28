<?php
session_start();
require_once 'includes/db.php';
if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }
$pdo->query("UPDATE notifications SET is_read = 1");
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
