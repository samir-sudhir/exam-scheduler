<?php
// Database connection
include('../../../config/database.php');

// Fetch course details
$id = $_GET['id'] ?? null;

$course_code = '';
$course_name = '';

if ($id) {
    $select_courses = "SELECT course_code, course_name FROM courses WHERE id = ?";
    $prepare_course = $mysqli->prepare($select_courses);
    $prepare_course->bind_param('i', $id);
    $prepare_course->execute();
    $result = $prepare_course->get_result();
    $row = $result->fetch_assoc();

    $course_code = $row['course_code'] ?? ''; // Correct use of ?? operator
    $course_name = $row['course_name'] ?? '';
}

// Handle course update and enrollment updates
if (isset($_POST['update'])) {
    $new_course_code = $_POST['new_course_code'];
    $new_course_name = $_POST['new_course_name'];

    // Check if a file is uploaded
    if (isset($_FILES['enrollments_file']) && $_FILES['enrollments_file']['error'] == 0) {
        // Validate file extension
        $file_extension = pathinfo($_FILES['enrollments_file']['name'], PATHINFO_EXTENSION);
        if ($file_extension !== 'txt') {
            echo "<div class='alert alert-danger'>Only .txt files are allowed.</div>";
            exit;
        }

        $file_tmp = $_FILES['enrollments_file']['tmp_name'];
        $file_data = file($file_tmp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Update course details
        $update_course = "UPDATE courses SET course_code = ?, course_name = ? WHERE id = ?";
        $stmt = $mysqli->prepare($update_course);
        $stmt->bind_param('ssi', $new_course_code, $new_course_name, $id);
        $stmt->execute();

        // Clear current enrollments for the course
        $clear_enrollments = "DELETE FROM enrollments WHERE course_id = ?";
        $stmt = $mysqli->prepare($clear_enrollments);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        // Insert updated enrollments
        $insert_enrollment = "INSERT INTO enrollments (course_id, student_id) VALUES (?, ?)";
        $stmt = $mysqli->prepare($insert_enrollment);

        foreach ($file_data as $student_id) {
            $student_id = trim($student_id); // Remove whitespace
            $stmt->bind_param('is', $id, $student_id);
            $stmt->execute();
        }

        echo "<div class='alert alert-success'>Course and enrollments updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Please upload a valid file.</div>";
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
                    <input type="text" class="form-control" id="new_course_code" name="new_course_code" value="<?php echo htmlspecialchars($course_code); ?>" required>
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
