<?php
require 'C:\xampp\htdocs\e_v\config\database.php';
// Get the list of superintendents with their corresponding courses
$query = "
    SELECT s.id, s.name, s.designation, s.department, GROUP_CONCAT(c.course_name ORDER BY c.course_name ASC) AS courses
    FROM superintendents s
    LEFT JOIN superintendent_courses sc ON s.id = sc.superintendent_id
    LEFT JOIN courses c ON sc.course_id = c.id
    GROUP BY s.id
";

$result = $mysqli->query($query);

if ($result) {
    $superintendents = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die('Query failed: ' . $mysqli->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superintendents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<?php include('../layout/header.php'); ?>

    <div class="container mt-5">
        <h1 class="mb-4">Superintendents</h1>
        <a href="../superintendents/eligible_superintendents.php" class="btn btn-secondary mb-3">Eligible Superintendents</a>
        <a href="create.php" class="btn btn-primary mb-3">Add New Superintendent</a>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Department</th>
                    <th>Assigned Courses</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($superintendents as $superintendent): ?>
                <tr>
                    <td><?= htmlspecialchars($superintendent['id']) ?></td>
                    <td><?= htmlspecialchars($superintendent['name']) ?></td>
                    <td><?= htmlspecialchars($superintendent['designation']) ?></td>
                    <td><?= htmlspecialchars($superintendent['department']) ?></td>
                    <td><?= htmlspecialchars($superintendent['courses']) ?></td> <!-- Displaying comma-separated course names -->
                    <td>
                        <a href="update.php?id=<?= $superintendent['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="delete.php?id=<?= $superintendent['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
