<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $worker_name  = trim($_POST['worker_name']);
    $service_type = trim($_POST['service_type']);

    $stmt = $pdo->prepare("INSERT INTO workers (worker_name, service_type) VALUES (?, ?)");
    $stmt->execute([$worker_name, $service_type]);
}

header("Location: job_entry.php");
exit();
?>