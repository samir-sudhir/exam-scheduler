<?php
require '../../../config/db.php';

// Fetch courses and enrollments
$courses = $pdo->query("SELECT c.id AS course_id, c.course_name, COUNT(e.student_id) AS enrollment_count
                         FROM courses c
                         LEFT JOIN enrollments e ON c.id = e.course_id
                         GROUP BY c.id, c.course_name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch exam halls
$exam_halls = $pdo->query("SELECT id, building, floor, hall_number, seating_capacity FROM exam_halls")->fetchAll(PDO::FETCH_ASSOC);

$feasible_exam_halls = [];

foreach ($courses as $course) {
    $feasible_exam_halls[$course['course_name']] = [];

    // Fetch halls that can accommodate the course enrollments
    foreach ($exam_halls as $hall) {
        if ($hall['seating_capacity'] >= $course['enrollment_count']) {
            $feasible_exam_halls[$course['course_name']][] = [
                'id' => $hall['id'],
                'building' => $hall['building'],
                'floor' => $hall['floor'],
                'hall_number' => $hall['hall_number'],
                'seating_capacity' => $hall['seating_capacity']
            ];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feasible Exam Halls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<?php include('../layout/header.php'); ?>

<div class="container mt-5">
    <h1 class="mb-4">Feasible Exam Halls</h1>
    <?php foreach ($feasible_exam_halls as $course_name => $halls): ?>
        <h3><?= $course_name ?></h3>
        <?php if (!empty($halls)): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Building</th>
                        <th>Floor</th>
                        <th>Hall Number</th>
                        <th>Seating Capacity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($halls as $hall): ?>
                        <tr>
                            <td><?= $hall['building'] ?></td>
                            <td><?= $hall['floor'] ?></td>
                            <td><?= $hall['hall_number'] ?></td>
                            <td><?= $hall['seating_capacity'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No feasible exam halls found for this course.</p>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
</body>
</html>
