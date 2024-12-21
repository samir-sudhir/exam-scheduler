<?php
    require '../../../config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Enrollments</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="bg-light">
<?php include('../layout/header.php'); ?>

<div class="container mt-5">
    <h2 class="mb-4">View Student Enrollments</h2>

    <!-- Section to view enrollments for a specific course -->
    <div class="card mb-4">
        <div class="card-header">Course and Enrollments</div>
        <div class="card-body">
            <?php
                $id = $_GET['id']; // Assuming 'id' is course_id
                $select_students = "SELECT student_id FROM enrollments WHERE course_id = ?";
                $stmt = $connection->prepare($select_students);

                if ($stmt) {
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        echo "<ul class='list-group'>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<li class='list-group-item'>Student ID: " . htmlspecialchars($row['student_id']) . "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>No students enrolled in this course.</p>";
                    }

                    $stmt->close();
                } else {
                    echo "<p>Unable to prepare the statement. Please try again.</p>";
                }
            ?>
            <a href="../students/enroll.php" class="btn btn-success mt-3">Back</a>
        </div>
    </div>
</div>

</body>
</html>

<?php
    $connection->close();
?>
