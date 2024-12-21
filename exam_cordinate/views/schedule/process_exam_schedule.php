<?php
// Establish connection to the MySQL database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "a_4";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the courses, exam_halls, and superintendents tables
$coursesQuery = "SELECT * FROM courses";
$coursesResult = $conn->query($coursesQuery);
$courses = $coursesResult->fetch_all(MYSQLI_ASSOC);

$examHallsQuery = "SELECT * FROM exam_halls";
$examHallsResult = $conn->query($examHallsQuery);
$examHalls = $examHallsResult->fetch_all(MYSQLI_ASSOC);

$superintendentsQuery = "SELECT * FROM superintendents";
$superintendentsResult = $conn->query($superintendentsQuery);
$superintendents = $superintendentsResult->fetch_all(MYSQLI_ASSOC);

// Accept dynamic user inputs for time slots
$weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']; // Modify as needed
$startTime = "9:00 AM"; // Starting time for the first slot
$endTime = "5:00 PM"; // Ending time for the last slot
$slotDuration = 60; // Slot duration in minutes
$breakTime = 15; // Break time in minutes after each slot

// Function to generate time slots dynamically
function generateTimeSlots($startTime, $endTime, $slotDuration, $breakTime) {
    $timeSlots = [];
    $currentTime = strtotime($startTime);
    $endTime = strtotime($endTime);

    // Generate slots for each day
    while ($currentTime < $endTime) {
        $startSlot = date("g:i A", $currentTime);
        $endSlot = date("g:i A", $currentTime + $slotDuration * 60); // Add slot duration

        // Add time slot to array
        $timeSlots[] = [$startSlot, $endSlot];

        // Move current time forward by the slot duration plus break time
        $currentTime = $currentTime + ($slotDuration + $breakTime) * 60;
    }

    return $timeSlots;
}

    // Generate dynamic time slots based on user input
        $timeSlots = generateTimeSlots($startTime, $endTime, $slotDuration, $breakTime);
    // Function to generate the exam schedule
function generateExamSchedule($courses, $examHalls, $timeSlots, $superintendents, $weekdays) {
    $schedule = [];
    $timeSlotIndex = 0; // Start with the first time slot

    foreach ($courses as $course) {
        $courseCode = $course['course_code'];  // Assuming course_code is available
        $weekday = $weekdays[$timeSlotIndex % count($weekdays)]; // Round-robin weekday assignment

        // Assign time slot in a round-robin manner
        $slot = $timeSlots[$timeSlotIndex % count($timeSlots)];

        // Assign hall in a round-robin manner
        $hall = $examHalls[$timeSlotIndex % count($examHalls)];

        // Assign a random superintendent
        $superintendent = $superintendents[array_rand($superintendents)];

        // Add to the schedule
        $schedule[] = [
            'course_code' => $courseCode,
            'weekday'     => $weekday,
            'slot_start'  => $slot[0],  // Slot start time
            'slot_end'    => $slot[1],    // Slot end time
            'exam_hall'   => $hall['building'],  // Hall name
            'superintendent' => $superintendent['name']  // Superintendent name
        ];

        // Move to the next time slot (circular)
        $timeSlotIndex = ($timeSlotIndex + 1) % count($timeSlots);
    }

    return $schedule;
}

// Generate the schedule
$schedule = generateExamSchedule($courses, $examHalls, $timeSlots, $superintendents, $weekdays);

// Function to display the schedule in a Bootstrap table
function displaySchedule($schedule) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Exam Schedule</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <h2 class="mb-4">Exam Schedule</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Weekday</th>
                        <th>Slot Start Time</th>
                        <th>Slot End Time</th>
                        <th>Exam Hall</th>
                        <th>Superintendent</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($schedule as $entry) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($entry['course_code']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['weekday']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['slot_start']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['slot_end']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['exam_hall']) . '</td>';
        echo '<td>' . htmlspecialchars($entry['superintendent']) . '</td>';
        echo '</tr>';
    }

    echo '  </tbody>
            </table>
        </div>
    </body>
    </html>';
}

// Display the schedule
displaySchedule($schedule);

// Close the connection
$conn->close();
?>
