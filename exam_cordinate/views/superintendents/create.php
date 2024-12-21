<?php
require '../../../config/db.php';

// Fetch courses from the database for dropdown
$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];
    $course_ids = $_POST['course_ids']; // This will be an array of selected course IDs

    // Fetch assigned course codes from superintendent_courses
    $existing_courses_query = "SELECT course_id FROM superintendent_courses";
    $existing_courses = $pdo->query($existing_courses_query)->fetchAll(PDO::FETCH_COLUMN);

    // Check for conflicts
    $conflicting_courses = array_intersect($course_ids, $existing_courses);

    if (!empty($conflicting_courses)) {

        // Display conflict message
        echo "<div class='alert alert-danger'><strong>Course code already selected</strong><ul>";
        echo "</ul></div>";
    } else {
        // Insert the superintendent's main details
        $stmt = $pdo->prepare("INSERT INTO superintendents (name, designation, department) VALUES (?, ?, ?)");
        $stmt->execute([$name, $designation, $department]);

        // Get the ID of the newly inserted superintendent
        $superintendent_id = $pdo->lastInsertId();

        // Insert the courses associated with the superintendent
        if (!empty($course_ids)) {
            $stmt = $pdo->prepare("INSERT INTO superintendent_courses (superintendent_id, course_id) VALUES (?, ?)");
            foreach ($course_ids as $course_id) {
                $stmt->execute([$superintendent_id, $course_id]);
            }
        }

        // Redirect to the index page
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Superintendent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<?php include('../layout/header.php'); ?>

<div class="container mt-5">
    <h1 class="mb-4">Add Superintendent</h1>
    <form method="POST" class="card p-4 shadow">
        <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Designation:</label>
            <input type="text" name="designation" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Department:</label>
            <input type="text" name="department" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Courses:</label>
            <select name="course_ids[]" class="form-control" multiple required>
                <?php foreach ($courses as $course): ?>
                <option value="<?= $course['id'] ?>"><?= $course['course_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Save</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
