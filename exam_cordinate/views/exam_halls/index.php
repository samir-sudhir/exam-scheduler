<?php
require 'C:\xampp\htdocs\e_v\config\database.php';  // Adjust path if necessary

// Query using MySQLi
$result = $mysqli->query("SELECT * FROM exam_halls");

if ($result) {
    $halls = $result->fetch_all(MYSQLI_ASSOC);  // Fetch the results as an associative array
} else {
    die('Query failed: ' . $mysqli->error);  // Error handling for failed query
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Halls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="../../views/scheduler/generate.php">Exam Scheduler </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav" style="justify-content: flex-end;">
                <ul class="navbar-nav">
                   
                    <li class="nav-item">
                        <a class="nav-link" href="../../../sign_out.php">Sign Out</a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Exam Halls</h1>
        <a href="../exam_halls/feasible_exam_halls.php" class="btn btn-secondary mb-3">Feasible Hall</a>
        <a href="create.php" class="btn btn-primary mb-3">Add New Exam Hall</a>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Building</th>
                    <th>Floor</th>
                    <th>Hall Number</th>
                    <th>Seating Capacity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($halls as $hall): ?>
                <tr>
                    <td><?= $hall['id'] ?></td>
                    <td><?= $hall['building'] ?></td>
                    <td><?= $hall['floor'] ?></td>
                    <td><?= $hall['hall_number'] ?></td>
                    <td><?= $hall['seating_capacity'] ?></td>
                    <td>
                        <a href="update.php?id=<?= $hall['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete.php?id=<?= $hall['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
