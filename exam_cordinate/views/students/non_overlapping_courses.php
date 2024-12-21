<?php
// Database connection
include_once('../../../config/db.php');

// Fetch all courses and enrollments
$stmt_courses = $pdo->prepare("SELECT id, course_code FROM courses");
$stmt_courses->execute();
$courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

$stmt_enrollments = $pdo->prepare("SELECT course_id, student_id FROM enrollments");
$stmt_enrollments->execute();
$enrollments = $stmt_enrollments->fetchAll(PDO::FETCH_ASSOC);

// Map courses to students
$course_students = [];
foreach ($enrollments as $enrollment) {
    $course_students[$enrollment['course_id']][] = $enrollment['student_id'];
}

// Initialize array to hold non-overlapping courses
$non_overlapping_courses = [];

// Calculate non-overlapping courses
foreach ($courses as $course) {
    $course_id = $course['id'];
    $course_code = $course['course_code'];
    $current_students = $course_students[$course_id] ?? [];

    $non_overlapping = [];

    foreach ($courses as $other_course) {
        if ($other_course['id'] === $course_id) continue;

        $other_students = $course_students[$other_course['id']] ?? [];
        
        // Check if there is an intersection (overlap)
        $overlap = array_intersect($current_students, $other_students);

        // If no overlap, add to non-overlapping list
        if (empty($overlap)) {
            $non_overlapping[] = $other_course['course_code'];
        }
    }

    // If no non-overlapping courses, set 'None'
    $non_overlapping_courses[$course_code] = !empty($non_overlapping) ? implode(', ', $non_overlapping) : 'None';
}

// Display non-overlapping courses
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Non-Overlapping Courses</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include('../layout/header.php');?>

    <div class="container mt-5">
        <h2 class="mb-4">Non-Overlapping Courses</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>List of Non-Overlapping Courses</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($non_overlapping_courses as $course_code => $non_overlapping_list): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course_code); ?></td>
                        <td><?php echo htmlspecialchars($non_overlapping_list); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
