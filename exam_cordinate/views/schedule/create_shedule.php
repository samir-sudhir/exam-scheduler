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

// Fetch number of students per course from enrollments table
$enrollmentsQuery = "SELECT course_code, COUNT(*) AS num_students FROM enrollments GROUP BY course_code";
$enrollmentsResult = $conn->query($enrollmentsQuery);
$enrollments = [];
while ($row = $enrollmentsResult->fetch_assoc()) {
    $enrollments[$row['course_code']] = $row['num_students'];
}

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

// Function to check if a hall has enough capacity for a given course
function hasSufficientCapacity($courseCode, $hallId, $enrollments, $examHalls) {
    $numStudents = $enrollments[$courseCode];
    foreach ($examHalls as $hall) {
        if ($hall['id'] == $hallId) {
            return $numStudents <= $hall['seating_capacity'];
        }
    }
    return false;
}

// Function to generate the exam schedule with constraints and optimal allocation
function generateExamSchedule($courses, $examHalls, $timeSlots, $superintendents, $weekdays, $enrollments) {
    global $conn; // Use the global connection variable

    $schedule = [];
    $timeSlotIndex = 0; // Start with the first time slot
    $superintendentAssignments = [];
    $superintendentExams = []; // Track the exams assigned to each superintendent for minimum gap enforcement

    foreach ($courses as $course) {
        $courseCode = $course['course_code'];  // Assuming course_code is available
        $weekday = $weekdays[$timeSlotIndex % count($weekdays)]; // Round-robin weekday assignment

        // Assign time slot in a round-robin manner
        $slot = $timeSlots[$timeSlotIndex % count($timeSlots)];

        // Assign hall in a round-robin manner
        $hall = $examHalls[$timeSlotIndex % count($examHalls)];

        // Ensure hall has sufficient capacity
        if (!hasSufficientCapacity($courseCode, $hall['id'], $enrollments, $examHalls)) {
            $hall = $examHalls[($timeSlotIndex + 1) % count($examHalls)]; // Use the next available hall
        }

        // Check for superintendent assignment conflicts (ensuring a gap between exams for the same superintendent)
        $assignedSuperintendent = null;
        foreach ($superintendents as $superintendent) {
            $superintendentCoursesQuery = "SELECT * FROM superintendent_courses WHERE superintendent_id = " . $superintendent['id'];
            $assignedCoursesResult = $conn->query($superintendentCoursesQuery);
            $assignedCourses = $assignedCoursesResult->fetch_all(MYSQLI_ASSOC);

            $courseAssigned = false;
            foreach ($assignedCourses as $assignedCourse) {
                if ($assignedCourse['course_code'] == $courseCode) {
                    $courseAssigned = true;
                    break;
                }
            }

            // Check for gap between exams for the same superintendent
            $lastExamDate = isset($superintendentExams[$superintendent['id']]) ? $superintendentExams[$superintendent['id']] : null;
            $currentExamDate = strtotime($weekday); // Convert weekday to date for comparison
            $gapValid = ($lastExamDate === null) || ($currentExamDate - $lastExamDate >= 86400); // 86400 seconds = 1 day

            if (!$courseAssigned && !in_array($superintendent['id'], $superintendentAssignments) && $gapValid) {
                $assignedSuperintendent = $superintendent['name'];
                $superintendentAssignments[] = $superintendent['id'];
                $superintendentExams[$superintendent['id']] = $currentExamDate; // Record this exam date
                break;
            }
        }

        if ($assignedSuperintendent === null) {
            // Handle case where no suitable superintendent is found
            $assignedSuperintendent = "No available superintendent";
        }

        // Add to the schedule
        $schedule[] = [
            'course_code' => $courseCode,
            'weekday' => $weekday,
            'slot_start' => $slot[0],  // Slot start time
            'slot_end' => $slot[1],    // Slot end time
            'exam_hall' => $hall['building'],  // Hall name
            'superintendent' => $assignedSuperintendent  // Superintendent name
        ];

        // Move to the next time slot (circular)
        $timeSlotIndex = ($timeSlotIndex + 1) % count($timeSlots);
    }

    return $schedule;
}

// Generate the schedule
$schedule = generateExamSchedule($courses, $examHalls, $timeSlots, $superintendents, $weekdays, $enrollments);

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

// Display the exam schedule
displaySchedule($schedule);
?>
