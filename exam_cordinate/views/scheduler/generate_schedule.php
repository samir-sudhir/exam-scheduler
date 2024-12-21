<?php
// Database connection (assuming you have a connection file)
require 'C:\xampp\htdocs\e_v\config\database.php';

// Get values from the form
$slotsPerDay = $_POST['slotsPerDay'];
$dayGap = $_POST['dayGap']; // The gap between exams for the same student

// Fetch all courses and their enrolled students
$queryCourses = "SELECT c.id, c.course_code, e.student_id 
                 FROM courses c 
                 JOIN enrollments e ON c.id = e.course_id 
                 ORDER BY c.id, e.student_id";
$resultCourses = mysqli_query($mysqli, $queryCourses);

$courses = [];
while ($row = mysqli_fetch_assoc($resultCourses)) {
    $courses[$row['id']]['course_code'] = $row['course_code'];
    $courses[$row['id']]['students'][] = $row['student_id'];
}

// Initialize schedule
$schedule = [];
$studentExamDays = [];
$daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

// Initialize the schedule structure
$dayCounter = 1;
while ($dayCounter <= count($courses)) {
    // Assign courses to each day/slot based on gap logic
    foreach ($courses as $course_id => $course) {
        foreach ($course['students'] as $student_id) {
            // Try to find a day and slot that satisfies the gap between exams
            $scheduled = false;
            for ($day = 0; !$scheduled && $day < count($daysOfWeek); $day++) {
                if (count($schedule[$day] ?? []) < $slotsPerDay) {
                    if (!isset($studentExamDays[$student_id]) || !in_array($day, $studentExamDays[$student_id])) {
                        $schedule[$day][] = [
                            'course_code' => $course['course_code'],
                            'student_id' => $student_id
                        ];
                        $studentExamDays[$student_id][] = $day;
                        $scheduled = true;
                    }
                }
            }
        }
    }
}

// Output the schedule as a table
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Schedule</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('../layout/header.php'); ?>

    <div class="container mt-4">
        <h2 class="mb-4">Exam Schedule</h2>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Slot/Day</th>
                    <?php 
                    // Loop through days of the week for table columns
                    foreach ($daysOfWeek as $day) {
                        echo "<th>$day</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Loop through each slot
                for ($slot = 1; $slot <= $slotsPerDay; $slot++) {
                    echo "<tr><td>Slot $slot</td>";

                    // For each day, list the courses scheduled
                    for ($day = 0; $day < count($daysOfWeek); $day++) {
                        echo "<td>";
                        if (isset($schedule[$day]) && isset($schedule[$day][$slot-1])) {
                            // Display courses scheduled for this slot on the current day
                            foreach ($schedule[$day] as $exam) {
                                echo $exam['course_code'] . "<br>";
                            }
                        }
                        echo "</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
