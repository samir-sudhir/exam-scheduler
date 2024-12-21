<?php
require '../config/database.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../sign_in.php");
    exit();
}

// Fetch unapproved users
$unapproved_query = "SELECT id, username, role FROM users WHERE approved = 0";
$unapproved_result = $mysqli->query($unapproved_query);

// Fetch approved users
$approved_query = "SELECT id, username, role FROM users WHERE approved = 1";
$approved_result = $mysqli->query($approved_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <?php
        include('../admin/layout/header.php');
    ?>
    <div class="container mt-5">
        <h2>Unapproved Users</h2>
        <?php if ($unapproved_result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $unapproved_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= $user['username'] ?></td>
                            <td><?= $user['role'] ?></td>
                            <td>
                                <a href="../admin/approved_user.php?id=<?= $user['id'] ?>" class="btn btn-success">Approve</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-danger">There are no unapproved users.</p>
        <?php endif; ?>

        <h2 class="mt-5">Approved Users</h2>
        <?php if ($approved_result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $approved_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= $user['username'] ?></td>
                            <td><?= $user['role'] ?></td>
                            <td>
                                <a href="update_user.php?id=<?= $user['id'] ?>" class="btn btn-primary">Update</a>
                                <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-danger">There are no approved users.</p>
        <?php endif; ?>
    </div>
</body>
</html>
