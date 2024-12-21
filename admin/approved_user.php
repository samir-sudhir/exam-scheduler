<?php
require '../config/database.php';
session_start();

// Only admin can approve users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../sign_in.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Ensure ID is an integer
    $mysqli->query("UPDATE users SET approved = 1 WHERE id = $id");
}

header("Location: index.php");
exit();
?>