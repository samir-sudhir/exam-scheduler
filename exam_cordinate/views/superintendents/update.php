<?php
require 'C:\xampp\htdocs\e_v\config\database.php';

// Fetch courses from the database for dropdown
$query = "SELECT * FROM courses";
$result = $mysqli->query($query);

if ($result) {
    $courses = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die('Query failed: ' . $mysqli->error);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    // Get the current details for the superintendent
    $id = $_GET['id'];
    $stmt = $mysqli->prepare("SELECT * FROM superintendents WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $superintendent = $result->fetch_assoc();

    if (!$superintendent) {
        // If no superintendent found, redirect to the list page
        header('Location: index.php');
        exit;
    }

    // Get the current courses assigned to the superintendent
    $stmt = $mysqli->prepare("SELECT course_id FROM superintendent_courses WHERE superintendent_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignedCourses = $result->fetch_all(MYSQLI_ASSOC);  // Fetch as associative array
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update the superintendent details
    $id = $_POST['id'];
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];
    $course_ids = $_POST['course_ids']; // This will be an array of selected course IDs

    // Update the superintendent's main details
    $stmt = $mysqli->prepare("UPDATE superintendents SET name = ?, designation = ? , department = ? WHERE id = ?");
    $stmt->bind_param('sssi', $name, $designation, $department, $id);
    $stmt->execute();

    // Delete the previous courses assigned to the superintendent
    $stmt = $mysqli->prepare("DELETE FROM superintendent_courses WHERE superintendent_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Insert the new courses assigned to the superintendent
    if (!empty($course_ids)) {
        $stmt = $mysqli->prepare("INSERT INTO superintendent_courses (superintendent_id, course_id) VALUES (?, ?)");
        foreach ($course_ids as $course_id) {
            $stmt->bind_param('ii', $id, $course_id);
            $stmt->execute(); 
        }
    }

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Superintendent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<?php include('../layout/header.php'); ?>

<div class="container mt-5">
    <h1 class="mb-4">Update Superintendent</h1>
    <form method="POST" class="card p-4 shadow">
        <input type="hidden" name="id" value="<?= htmlspecialchars($superintendent['id']) ?>">
        
        <div class="mb-3">
            <label class="form-label">Name:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($superintendent['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Designation:</label>
            <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($superintendent['designation']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Department:</label>
            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($superintendent['department']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Courses:</label>
            <select name="course_ids[]" class="form-control" multiple required>
                <?php foreach ($courses as $course): ?>
                <option value="<?= $course['id'] ?>" <?= in_array($course['id'], array_column($assignedCourses, 'course_id')) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course['course_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
