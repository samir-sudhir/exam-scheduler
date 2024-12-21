<?php
// Database connection (assuming you have a connection file)
require '../../../config/database.php';

// Initialize variables to store the user input
$slotsPerDay = 0;
$dayGap = 0;
$schedule = [];
$slotTimes = [
    "Slot 1" => "08:00 - 09:30",
    "Slot 2" => "10:00 - 11:30",
    "Slot 3" => "12:00 - 13:30",
    "Slot 4" => "14:00 - 15:30",
    "Slot 5" => "16:00 - 17:30"
];

// Fetch courses from the database
$queryCourses = "SELECT c.id, c.course_code FROM courses c";
$resultCourses = mysqli_query($mysqli, $queryCourses);
$courses = [];
while ($row = mysqli_fetch_assoc($resultCourses)) {
    $courses[] = $row;
}

// Fetch halls and superintendents data
$queryHalls = "SELECT hall_number, seating_capacity FROM exam_halls";
$resultHalls = mysqli_query($mysqli, $queryHalls);
$halls = [];
while ($row = mysqli_fetch_assoc($resultHalls)) {
    $halls[] = $row;
}

$querySuperintendents = "SELECT sp.id, sp.name FROM superintendents sp";
$resultSuperintendents = mysqli_query($mysqli, $querySuperintendents);
$superintendents = [];
while ($row = mysqli_fetch_assoc($resultSuperintendents)) {
    $superintendents[] = $row;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get values from the form
    $slotsPerDay = $_POST['slotsPerDay'];
    $dayGap = $_POST['dayGap'];
    
    // Create the schedule logic based on the inputs
    $schedule = [];
    
    // Calculate the weekdays based on a fixed start date
    $currentDate = strtotime('next Monday');  // Use the next Monday as a fixed starting point
    $weekdays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    $scheduledDays = [];

    // Loop through and assign days based on the current date
    for ($i = 0; $i < 7; $i++) {
        $dayName = date('l', $currentDate); // Get the day name (e.g., Monday)
        $scheduledDays[] = $dayName;
        
        // Move to the next day, considering the day gap
        $currentDate = strtotime("+1 day", $currentDate);
    }

    // Adjust the days for the schedule according to the gap
    $examDays = [];
    $examDayIndex = 0; // To track the index of the exam days
    while ($examDayIndex < count($scheduledDays) && count($examDays) < ceil(count($courses) / $slotsPerDay)) {
        // If it's an exam day, add it to the examDays array
        $examDays[] = $scheduledDays[$examDayIndex];
        
        // Move to the next exam day, skipping the gap
        $examDayIndex += $dayGap + 1; 
    }

    // Fill the schedule with courses
    $slotNumber = 1;
    foreach ($weekdays as $day) {
        $schedule[$day] = [];
        // If the day is an exam day, assign courses
        if (in_array($day, $examDays)) {
            for ($slot = 1; $slot <= $slotsPerDay; $slot++) {
                // Assign a course to each slot (using the courses from the database)
                $courseIndex = array_rand($courses); // Randomly select a course for each slot
                $course = $courses[$courseIndex];  // Get the course details
                
                // Find available hall and assign superintendent
                $hall = $halls[array_rand($halls)];
                $superintendent = $superintendents[array_rand($superintendents)];

                $schedule[$day][] = [
                    'slot' => "Slot $slot",
                    'course' => $course['course_code'],
                    'hall' => $hall['hall_number'],
                    'superintendent' => $superintendent['name'],
                ]; 
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Exam Schedule</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('../layout/header.php'); ?>
    
    <div class="container mt-5">
        <h2 class="mb-4">Generate Exam Schedule</h2>
        
        <!-- Card to generate the exam schedule
        <div class="card text-center mt-3">
            <div class="card-body">
                <i class="fas fa-cogs card-icon" style="font-size: 40px;"></i>
                <a href="../correct_schedule/exam_schedule1.php">
                    <h5 class="card-title mt-3">Click to generate the Exam Schedule</h5>
                </a>
            </div>
        </div> -->
        
        <!-- Form to input details for schedule generation -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="slotsPerDay">Number of slots per day:</label>
                <input type="number" id="slotsPerDay" name="slotsPerDay" class="form-control" min="1" required>
            </div>
            <div class="form-group">
                <label for="dayGap">Day gap between exams:</label>
                <input type="number" id="dayGap" name="dayGap" class="form-control" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Generate schedule</button>
        </form>

        <?php if (!empty($schedule)): ?>
            <h3 class="mt-4">Generated Exam Schedule</h3>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Time Slot</th>
                        <?php
                        $currentDate = strtotime('next Monday');
                        for ($i = 0; $i < 7; $i++) {
                            echo "<th>" . date('l', $currentDate) . "</th>";
                            $currentDate = strtotime("+1 day", $currentDate); // Move to next day
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($slot = 1; $slot <= $slotsPerDay; $slot++): ?>
                        <tr>
                            <td><?php echo $slotTimes["Slot $slot"]; ?></td>
                            <?php foreach ($schedule as $day => $slots): ?>
                                <td>
                                    <?php 
                                    $slotInfo = $slots[$slot - 1] ?? null;
                                    if ($slotInfo) {
                                        echo "Course: " . $slotInfo['course'] . "<br>";
                                        echo "Hall: " . $slotInfo['hall'] . "<br>";
                                        echo "Superintendent: " . $slotInfo['superintendent'];
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
