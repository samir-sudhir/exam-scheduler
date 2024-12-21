<?php
    include('D:\newXampp\htdocs\exam_shedular_practice\e_v\config/database.php');

    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
        $course_code = $_POST['new_course_code'];
        $course_name = $_POST['new_course_name'];

        $stmt_course = "INSERT INTO courses (course_code, course_name) VALUES (?,?)";
        $stmt_prepare = $connection->prepare($stmt_course);
        $stmt_prepare->bind_param('ss',$course_code,$course_name);
        $stmt_prepare->execute();

        echo "<div class='alert alert-success'>New course $course_code added successfully.</div>";

    }

    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])){
        $course_id = $_POST['existing_course'];
        $file = $_FILES['enrollment_file']['tmp_name'];

        if($file) {
            $students = file($file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

            $enroll_query = "INSERT INTO enrollments (course_id, student_id) VALUE (?,?)";
            $enroll_prepare = $connection->prepare($enroll_query);
            foreach($students as $student) {
                $enroll_prepare->bind_param('is',$course_id,$student);
                if (!$enroll_prepare->execute()) {
                    $errors[] = "Error checking enrollment for student ID: $student_id.";
                    continue;
                }
            }

        }  else {
            echo "<div class='alert alert-danger'>No file uploaded. Please upload a valid file.</div>";
        }
    }


    // Handle course deletion
    // Delete course and enrollments
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        $course_id = $_POST['course_code'];  // Using 'course_code' which actually holds the 'id'

        // Delete enrollments associated with the course
        $delete_enrollments = "DELETE FROM enrollments WHERE course_id = ?";
        $stmt = $connection->prepare($delete_enrollments);
        $stmt->bind_param('i', $course_id);
        if ($stmt->execute()) {
            // If enrollments are deleted successfully, proceed to delete the course
            $delete_course = "DELETE FROM courses WHERE id = ?";
            $stmt_course = $connection->prepare($delete_course);
            $stmt_course->bind_param('i', $course_id);
            
            if ($stmt_course->execute()) {
                // Success message
                echo "<div class='alert alert-success'>Course and its enrollments have been deleted successfully.</div>";
            } else {
                // Handle error in deleting course
                echo "<div class='alert alert-danger'>Error deleting the course.</div>";
            }
        } else {
            // Handle error in deleting enrollments
            echo "<div class='alert alert-danger'>Error deleting enrollments for the course.</div>";
        }
    }

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body>
    <?php
    include_once('../layout/header.php');
    ?>

    <div class="container mt-5">
        <h2 class="mb-4">Manage Student Enrollments</h2>

        <!-- Option to Add New Course -->
        <div class="card mb-4">
            <div class="card-header">Add New Course</div>
            <div class="card-body">
                <form action="enroll.php" method="POST">
                    <div class="form-group">
                        <label for="new_course_code">New Course Code:</label>
                        <input type="text" class="form-control" id="new_course_code" name="new_course_code" placeholder="Enter new course code" required>
                    </div>
                    <div class="form-group">
                        <label for="new_course_code">New Course Name:</label>
                        <input type="text" class="form-control" id="new_course_name" name="new_course_name" placeholder="Enter new course name" required>
                    </div>
                    <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
                </form>
            </div>
        </div>

        <!-- Option to Upload Enrollment for Existing Course -->
        <div class="card mb-4">
            <div class="card-header">Upload Enrollment to Existing Course</div>
            <div class="card-body">
                <form action="enroll.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="existing_course">Select Course:</label>
                        <select class="form-control" id="existing_course" name="existing_course" required>
                            <option value="">Select a course</option>
                            <?php
                            // Fetch existing courses from the database
                            $courses_query = "SELECT * FROM courses";
                            $courses_result = $connection->query($courses_query);
                            while ($course = $courses_result->fetch_assoc()) {
                                echo "<option value='" . $course['id'] . "'>" . $course['course_code'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="enrollment_file">Upload Enrollment File (.txt):</label>
                        <input type="file" class="form-control-file" id="enrollment_file" name="enrollment_file" accept=".txt"  required>
                    </div>
                    <button type="submit" name="upload" class="btn btn-primary">Upload Enrollment</button>
                </form>
            </div>
        </div>

        <!-- Form to delete a course and its enrollments -->
        <div class="card mb-4">
            <div class="card-header">Delete Course and Enrollments</div>
            <div class="card-body">
                <form action="enroll.php" method="POST">
                    <div class="form-group">
                        <label for="course_code_delete">Select Course:</label>
                        <select class="form-control" id="course_code_delete" name="course_code" style="width: 100%;" required>
                            <option value="">Select a course</option>
                            <?php
                            // Fetch existing courses from the database
                            $courses_query = "SELECT * FROM courses";
                            $courses_result = $connection->query($courses_query);
                            while ($course = $courses_result->fetch_assoc()) {
                                echo "<option value='" . $course['id'] . "'>" . $course['course_code'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="delete" class="btn btn-danger">Delete Course</button>
                </form>
            </div>
        </div>

        <!-- Form to delete a course and its enrollments -->
        <div class="card mb-4">
            <div class="card-header">View Course and Enrollments</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Actin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $select_course = "SELECT * FROM courses";
                        $execute_courses = $connection->query($select_course);

                        if ($execute_courses->num_rows > 0) {
                            while ($row = $execute_courses->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['course_code'] . "</td>";
                                echo "<td>" . $row['course_name'] . "</td>";
                                echo "<td><a href='../students/view_student.php?id=" . $row['id'] . "' class='btn btn-success mx-2'>View Student</a><a href='../students/update.php?id=" . $row['id'] . "' class='btn btn-warning mx-2'>Update</a></td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</body>

</html>