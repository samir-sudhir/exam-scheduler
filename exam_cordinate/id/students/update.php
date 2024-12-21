<?php
include_once('../../../config/database.php');

$id = $_GET['id'] ?? null;

$course_code = '';
$course_name = '';

if ($id) {
    // Fetch course details
    $select_course = "SELECT course_code, course_name FROM courses WHERE id = ?";
    $select_prepare = $connection->prepare($select_course);
    $select_prepare->bind_param('i', $id);
    $select_prepare->execute();
    $result = $select_prepare->get_result();
    $row = $result->fetch_assoc();

    $course_code = $row['course_code'] ?? '';
    $course_name = $row['course_name'] ?? '';
} else {
    echo "<div class='alert alert-danger'>ID Not Found</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $new_course_code = $_POST['new_course_code'];
    $new_course_name = $_POST['new_course_name'];
    $file = $_FILES['enrollments_file']['tmp_name'];

    if ($file && is_uploaded_file($file)) {
        $student_ids = file($file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        // Update course details
        $update_courses = "UPDATE courses SET course_code = ?, course_name = ? WHERE id = ?";
        $course_prepare = $connection->prepare($update_courses);
        $course_prepare->bind_param('ssi', $new_course_code, $new_course_name, $id);
        if ($course_prepare->execute()) {
            // Delete existing enrollments
            $delete_stmt = "DELETE FROM enrollments WHERE course_id = ?";
            $delete_prepare = $connection->prepare($delete_stmt);
            $delete_prepare->bind_param('i', $id);

            if ($delete_prepare->execute()) {
                // Insert new enrollments
                $insert_stmt = "INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)";
                $insert_prepare = $connection->prepare($insert_stmt);
                foreach ($student_ids as $student_id) {
                    $student_id = trim($student_id); // Sanitize student ID
                    $insert_prepare->bind_param('si', $student_id, $id);
                    $insert_prepare->execute();
                }
                echo "<div class='alert alert-success'>Course and enrollments updated successfully!</div>";
            } else {
                echo "<div class='alert alert-danger'>Failed to delete enrollments.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Failed to update course details.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No file uploaded or invalid file.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Course and Enrollments</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
<?php include('../layout/header.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">Update Course and Enrollments</h2>

    <div class="card mb-4">
        <div class="card-header">Update Course Details</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="new_course_code">New Course Code:</label>
                    <input type="text" class="form-control" id="new_course_code" name="new_course_code" value="<?php echo htmlspecialchars($course_code); ?>" accept=".txt" required>
                </div>
                <div class="form-group">
                    <label for="new_course_name">New Course Name:</label>
                    <input type="text" class="form-control" id="new_course_name" name="new_course_name" value="<?php echo htmlspecialchars($course_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="enrollments_file">Upload Enrollment File (.txt):</label>
                    <input type="file" class="form-control-file" id="enrollments_file" name="enrollments_file" accept=".txt" required>
                </div>
                <button type="submit" name="update" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
