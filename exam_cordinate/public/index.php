<?php
// Database connection
include('../../e_v/config/database.php');


// Fetch the count of enrollments
$enrollment_query = "SELECT COUNT(*) AS total_enrollments FROM enrollments";
$enrollment_result = $mysqli->query($enrollment_query);
$enrollment_count = $enrollment_result->fetch_assoc()['total_enrollments'];

// Fetch the count of courses
$course_query = "SELECT COUNT(*) AS total_courses FROM courses";
$course_result = $mysqli->query($course_query);
$course_count = $course_result->fetch_assoc()['total_courses'];

$superintendent_query = "SELECT COUNT(*) AS total_superintendents FROM superintendents";
$superintendent_result = $mysqli->query($superintendent_query);
$superintendent_count = $superintendent_result->fetch_assoc()['total_superintendents'];

$exam_hall_query = "SELECT COUNT(*) AS total_exam_halls FROM exam_halls";
$exam_hall_result = $mysqli->query($exam_hall_query);
$exam_hall_count = $exam_hall_result->fetch_assoc()['total_exam_halls'];

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-icon {
            font-size: 3rem;
            color: #007bff;
        }
        .card-text {
            font-size: 2rem;
            font-weight: bold;
        }
        .dashboard-title {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
        <!-- Navigation Header -->
            
    <!-- Navigation Header -->
    <?php

    // Navigation menu
    $navItems = [
        ['label' => 'Enroll Students', 'link' => 'views/students/enroll.php'],
        ['label' => 'Non-Overlapping Courses', 'link' => 'views/courses/non_overlapping_courses.php'],
        ['label' => 'Generate Schedule', 'link' => '/views/scheduler/generate.php'],
        ['label' => 'Sign Out', 'link' => '../../e_v/sign_out.php']
    ];
    ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
        <a class="navbar-brand" href="\public\index.php">Exam Scheduler </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav" style="justify-content: flex-end;">
        <ul class="navbar-nav">
            <!--  -->
            <li class="nav-item">
                    <a class="nav-link" href="/views/exam_halls">Exam Halls Management</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/views/superintendents">Superintendents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="views/slots">Slots</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="views/students/enroll.php">Enroll Students</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="views/courses/non_overlapping_courses.php">Non-Overlapping Courses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="views/scheduler/generate.php">Generate Schedule</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../../e_v/sign_out.php">Sign Out</a>
                </li>
        </ul>
        </div>
        </div>
    </nav>
    <div class="container mt-5">
        <div class="row g-4">
            <!-- Enrollments Card -->
            <div class="col-md-6 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user-graduate card-icon"></i>
                        <h5 class="card-title mt-3">Total Enrollments</h5>
                        <p class="card-text"><?php echo $enrollment_count; ?></p>
                    </div>
                </div>
            </div>
            <!-- Courses Card -->
            <div class="col-md-6 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-book-open card-icon"></i>
                        <h5 class="card-title mt-3">Total Courses</h5>
                        <p class="card-text"><?php echo $course_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user card-icon"></i>
                        <h5 class="card-title mt-3">Superintendent</h5>
                        <p class="card-text"><?php echo $superintendent_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-building card-icon"></i>
                        <h5 class="card-title mt-3">Exam Halls</h5>
                        <p class="card-text"><?php echo $exam_hall_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-setting card-icon"></i>
                        <a href="../e_v/exam_cordinate/views/schedule/process_exam_schedule.php"><h5 class="card-title mt-3">Click to generate the Exam Schedule</h5></a>
                        <!-- <a href="http://localhost/e_v3/views/schedule/process_exam_schedule.php"><h5 class="card-title mt-3">Click to generate the Exam Schedule</h5></a> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- process_exam_schedule.php -->

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
