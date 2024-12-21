<?php
require 'C:\xampp\htdocs\e_v\config\database.php';

// Fetch courses from the database
$query = "SELECT * FROM courses";
$result = $mysqli->query($query);

if ($result) {
    $courses = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die('Query failed: ' . $mysqli->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eligible Superintendents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include('../layout/header.php'); ?>

    <div class="container mt-5">
        <h1 class="mb-4">Eligible Superintendents for Each Course</h1>

        <?php foreach ($courses as $course): ?>
            <?php
                $course_id = $course['id'];
                // Fetch eligible superintendents for the current course
                $query = "
                    SELECT s.id, s.name
                    FROM superintendents s
                    WHERE NOT EXISTS (
                        SELECT 1
                        FROM superintendent_courses sc
                        WHERE sc.superintendent_id = s.id AND sc.course_id = ?
                    )
                ";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('i', $course_id);
                $stmt->execute();
                $result = $stmt->get_result();

                $eligibleSuperintendents = $result->fetch_all(MYSQLI_ASSOC);
            ?>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title"><?= htmlspecialchars($course['course_name']) ?> - Eligible Superintendents</h5>
                </div>
                <div class="card-body">
                    <?php if (count($eligibleSuperintendents) > 0): ?>
                        <ul class="list-group">
                            <?php foreach ($eligibleSuperintendents as $superintendent): ?>
                                <li class="list-group-item"><?= htmlspecialchars($superintendent['name']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No eligible superintendents available for this course.</p>
                    <?php endif; ?>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
