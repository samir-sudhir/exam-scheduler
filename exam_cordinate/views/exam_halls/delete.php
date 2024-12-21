<?php
require 'D:\newXampp\htdocs\exam_shedular_practice\e_v\config\db.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare and execute delete query
    $stmt = $pdo->prepare("DELETE FROM exam_halls WHERE id = ?");
    $stmt->execute([$id]);

    // Redirect back to the list of exam halls
    header('Location: index.php');
    exit;
}
?>
