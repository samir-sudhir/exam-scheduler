

function getEligibleSuperintendents($mysqli)
{
    // Fetch all courses
    $courses = $mysqli->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC);

    // Fetch all superintendents
    $superintendents = $mysqli->query("SELECT * FROM superintendents")->fetch_all(MYSQLI_ASSOC);

    $eligibleSuperintendents = [];
    $superintendentCourses = [];

    $stmt_courses = $mysqli->prepare("SELECT superintendent_id, course_id FROM superintendent_courses");
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    while ($row = $result_courses->fetch_assoc()) {
        $superintendentCourses[$row['superintendent_id']][] = $row['course_id'];
    }

    // Iterate through superintendents to find eligible ones
    foreach ($superintendents as $superintendent) {
        $superintendentId = $superintendent['id'];
        $superintendentName = $superintendent['name'];

        // Initialize the list of eligible courses for the current superintendent
        $eligibleSuperintendents[$superintendentName] = [];

        // Iterate over all courses to find eligibility
        foreach ($courses as $course) {
            $currentCourseId = $course['id'];
            // Skip if already assigned to the course
            if (isset($superintendentCourses[$superintendentId]) && !in_array($currentCourseId, $superintendentCourses[$superintendentId])) {
                // Add eligible course to the list
                $eligibleSuperintendents[$superintendentName][] = $course['course_code'];
            }
        }
    }

    return $eligibleSuperintendents;
}
// Function to fairly distribute tasks among superintendents
function distributeTasksFairly($eligibleSuperintendents)
{
    // Step 1: Initial task assignment (each course is assigned only once)
    $superintendentTasks = [];
    $assignedCourses = []; // To track which courses have already been assigned

    foreach ($eligibleSuperintendents as $superintendentName => $courses) {
        foreach ($courses as $course) {
            // Ensure each course is only assigned once
            if (!in_array($course, $assignedCourses)) {
                $superintendentTasks[$superintendentName][] = $course;
                $assignedCourses[] = $course; // Mark course as assigned
            }
        }
    }

    // Step 2: Count tasks for each superintendent
    $taskCounts = [];
    foreach ($superintendentTasks as $superintendentName => $tasks) {
        $taskCounts[$superintendentName] = count($tasks);
    }

    // Check if taskCounts is not empty before using max() and min()
    if (empty($taskCounts)) {
        echo("No tasks assigned to any superintendents.");
    }

    // Step 3: Balance task distribution
    $maxIterations = 100; // Prevent infinite loops
    $iterations = 0;

    while (true) {
        $maxSuperintendent = array_search(max($taskCounts), $taskCounts);
        $minSuperintendent = array_search(min($taskCounts), $taskCounts);

        // Stop if the distribution is balanced
        if ($taskCounts[$maxSuperintendent] - $taskCounts[$minSuperintendent] <= 2) {
            break;
        }

        // Move a task from the max-loaded superintendent to the min-loaded one
        if (!empty($superintendentTasks[$maxSuperintendent])) {
            $taskToMove = array_pop($superintendentTasks[$maxSuperintendent]);
            $superintendentTasks[$minSuperintendent][] = $taskToMove;

            // Update task counts
            $taskCounts[$maxSuperintendent]--;
            $taskCounts[$minSuperintendent]++;
        }

        // Increment iteration count
        $iterations++;
        if ($iterations > $maxIterations) {
            error_log("Max iterations reached while balancing tasks.");
            break; // Prevent infinite loop
        }
    }

    return $superintendentTasks;
}

// Call the function
$taskDistribution = distributeTasksFairly($eligibleSuperintendents);

function feasibleExamHall($mysqli)
{
    // Fetch courses and enrollments
    $courses = $mysqli->query("
            SELECT c.id AS course_id, c.course_name, COUNT(e.student_id) AS enrollment_count
            FROM courses c
            LEFT JOIN enrollments e ON c.id = e.course_id
            GROUP BY c.id, c.course_name
        ")->fetch_all(MYSQLI_ASSOC);

    // Fetch exam halls
    $exam_halls = $mysqli->query("
            SELECT id, building, floor, hall_number, seating_capacity 
            FROM exam_halls
            ORDER BY seating_capacity ASC
        ")->fetch_all(MYSQLI_ASSOC);

    $feasible_exam_halls = [];
    $assigned_halls = []; // To track assigned halls

    foreach ($courses as $course) {
        $filtered_halls = []; // Initialize filtered halls for this course

        // Filter halls that can accommodate the course enrollments
        foreach ($exam_halls as $hall) {
            if ($hall['seating_capacity'] >= $course['enrollment_count'] && !in_array($hall['id'], $assigned_halls)) {
                $filtered_halls[] = $hall; // Store the hall directly
            }
        }

        if (!empty($filtered_halls)) {
            $hallToAssign = array_shift($filtered_halls);
            $assigned_halls[] = $hallToAssign['id'];
            $feasible_exam_halls[$course['course_name']] = $hallToAssign;
        }
    }

    return $feasible_exam_halls;
}

// Now use the function to get feasible exam halls for courses
$feasibleHalls = feasibleExamHall($mysqli);

// Function to generate time slots dynamically
$startTime = "8:00 AM";
$endTime = "5:00 PM";
$slotDuration = 90;
$breakTime = 30;

function generateTimeSlots($startTime, $endTime, $slotDuration, $breakTime)
{
    $slots = [];
    $currentTime = strtotime($startTime);
    $endTime = strtotime($endTime);

    while ($currentTime + ($slotDuration * 60) <= $endTime) {
        $slotStart = date("g:i A", $currentTime);
        $currentTime += $slotDuration * 60;
        $slotEnd = date("g:i A", $currentTime);

        $slots[] = "$slotStart - $slotEnd";
        $currentTime += $breakTime * 60; // Add break time
    }

    // Add the last slot to match the exact end time
    if ($currentTime < $endTime) {
        $slotStart = date("g:i A", $currentTime);
        $slotEnd = date("g:i A", $endTime);
        $slots[] = "$slotStart - $slotEnd";
    }

    return $slots;
}

// Generate Time Slots
$timeSlots = generateTimeSlots($startTime, $endTime, $slotDuration, $breakTime);

// Function to generate dates dynamically
$startDate = "2024-12-04";
$numberOfDays = 30;
$weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

function generateDates($startDate, $numberOfDays, $weekdays)
{
    $dates = [];
    $currentDate = strtotime($startDate); // Starting point (could be any date, e.g., exam start date)

    while (count($dates) < $numberOfDays) {
        $dayOfWeek = date("l", $currentDate); // Get the day name (e.g., Monday)

        // Check if the current day is within the allowed weekdays
        if (in_array($dayOfWeek, $weekdays)) {
            $dates[] = [
                'label' => "Day " . (count($dates) + 1), // Day Label (Day 1, Day 2, etc.)
                'date' => date("Y-m-d", $currentDate),   // Date in Y-m-d format (e.g., 2024-12-01)
                'day_of_week' => $dayOfWeek              // Actual Day Name (e.g., Monday)
            ];
        }

        // Move to the next day
        $currentDate = strtotime("+1 day", $currentDate);
    }

    return $dates;
}

// Generate Dates
$dates = generateDates($startDate, $numberOfDays, $weekdays);

$weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$slotsPerDay = 5;
// Assuming $_SESSION['minDays'] is already set somewhere in your application
if (isset($_SESSION['minDays'])) {
    $minDays = $_SESSION['minDays'];
} else {
    // If minDays is not set, you can fallback to a default value (e.g., 2)
    $minDays = 2;
}

function generateExamSlots($weekdays, $slotsPerDay, $minDays)
{
    $examSlots = [];
    $dayCount = count($weekdays);
    $totalDays = 30; // Total days to generate slots for, can be adjusted as needed
    $lastScheduledDays = []; // Track last scheduled day for each course slot

    // Initialize the exam slots for each weekday
    foreach ($weekdays as $day) {
        $examSlots[$day] = [];
    }

    // Generate slots while respecting the minimum days between exams
    for ($day = 0; $day < $totalDays; $day++) {
        $dayName = $weekdays[$day % $dayCount]; // Loop through the weekdays

        // Ensure minimum days between exams
        if (isset($lastScheduledDays[$dayName]) && ($day - $lastScheduledDays[$dayName]) < $minDays) {
            continue; // Skip this day if it violates the minimum days rule
        }

        // Add slots for the current day
        for ($slot = 1; $slot <= $slotsPerDay; $slot++) {
            $examSlots[$dayName][] = "Slot $slot";
        }

        // Update the last scheduled day for the current weekday
        $lastScheduledDays[$dayName] = $day;
    }

    return $examSlots;
}

// Generate Exam Slots
$examSlots = generateExamSlots($weekdays, $slotsPerDay, $minDays);

function generateExamSchedule($mysqli, $dates, $timeSlots, $minDays, $overlapCourses, $nonOverlapCourses, $eligibleSuperintendents, $feasibleHalls)
{
    $schedule = [];
    $courseAssignments = []; // To track assigned courses
    $superintendentTasks = []; // To track tasks assigned to each superintendent

    // Calculate enrollment counts for each course
    $enrollmentCounts = [];
    foreach (array_merge($overlapCourses, $nonOverlapCourses) as $course => $overlaps) {
        $stmt = $mysqli->prepare("SELECT COUNT(student_id) AS count FROM enrollments WHERE course_id = (SELECT id FROM courses WHERE course_code = ?)");
        $stmt->bind_param('s', $course);
        $stmt->execute();
        $result = $stmt->get_result();
        $enrollmentCounts[$course] = $result->fetch_assoc()['count'];
        $stmt->close();
    }

    // Loop through each date and time slot to assign courses
    foreach ($dates as $dateInfo) {
        $date = $dateInfo['date'];
        foreach ($timeSlots as $time) {
            // Schedule overlapping courses first with minDays check
            foreach ($overlapCourses as $course => $overlaps) {
                // Skip if the course is already assigned
                if (isset($courseAssignments[$course])) {
                    continue;
                }

                // Check if the course can be scheduled based on minDays
                $canSchedule = true;
                foreach ($courseAssignments as $assignedCourse => $assignedDate) {
                    if (in_array($assignedCourse, $overlaps) && abs(strtotime($date) - strtotime($assignedDate)) < ($minDays * 86400)) {
                        $canSchedule = false; // Cannot schedule due to minDays constraint
                        break;
                    }
                }

                if ($canSchedule) {
                    // Find an eligible superintendent for the course
                    $eligibleSuperintendent = null;
                    foreach ($eligibleSuperintendents as $superintendent => $courses) {
                        if (in_array($course, $courses)) {
                            $eligibleSuperintendent = $superintendent;
                            break;
                        }
                    }

                    if ($eligibleSuperintendent === null) {
                        error_log("No eligible superintendent found for course $course");
                        continue;
                    }

                    // Find a feasible exam hall for the course
                    $feasibleExamHall = null;
                    foreach ($feasibleHalls as $hall) {
                        $enrollmentCount = $enrollmentCounts[$course];
                        if ($hall['seating_capacity'] >= $enrollmentCount) {
                            $feasibleExamHall = $hall;
                            break;
                        }
                    }

                    if ($feasibleExamHall === null) {
                        error_log("No feasible exam hall found for course $course");
                        continue;
                    }

                    // Assign the course to the schedule
                    $schedule[$date][] = [
                        'time_slot' => $time,
                        'course_name' => $course,
                        'superintendent' => $eligibleSuperintendent,
                        'exam_hall' => $feasibleExamHall['building'] . ' ' . $feasibleExamHall['hall_number']
                    ];

                    // Track assignments
                    $courseAssignments[$course] = $date;
                    $superintendentTasks[$eligibleSuperintendent][] = $course; // Track tasks for fair distribution

                    // Debugging output
                    error_log("Assigned overlapping course: $course on date: $date at time: $time");
                } else {
                    error_log("Cannot schedule course: $course on date: $date at time: $time due to minDays constraint.");
                }
            }

            // Schedule non-overlapping courses (without minDays check)
            foreach ($nonOverlapCourses as $course => $overlaps) {
                // Skip if the course is already assigned
                if (isset($courseAssignments[$course])) {
                    continue;
                }

                // Find an eligible superintendent for the course
                $eligibleSuperintendent = null;
                foreach ($eligibleSuperintendents as $superintendent => $courses) {
                    if (in_array($course, $courses)) {
                        $eligibleSuperintendent = $superintendent;
                        break;
                    }
                }

                if ($eligibleSuperintendent === null) {
                    error_log("No eligible superintendent found for course $course");
                    continue;
                }

                // Find a feasible exam hall for the course
                $feasibleExamHall = null;
                foreach ($feasibleHalls as $hall) {
                    $enrollmentCount = $enrollmentCounts[$course];
                    if ($hall['seating_capacity'] >= $enrollmentCount) {
                        $feasibleExamHall = $hall;
                        break;
                    }
                }

                if ($feasibleExamHall === null) {
                    error_log("No feasible exam hall found for course $course");
                    continue;
                }

                // Assign the course to the schedule
                $schedule[$date][] = [
                    'time_slot' => $time,
                    'course_name' => $course,
                    'superintendent' => $eligibleSuperintendent,
                    'exam_hall' => $feasibleExamHall['building'] . ' ' . $feasibleExamHall['hall_number']
                ];

                // Track assignments
                $courseAssignments[$course] = $date;
                $superintendentTasks[$eligibleSuperintendent][] = $course; // Track tasks for fair distribution

                // Debugging output
                error_log("Assigned non-overlapping course: $course on date: $date at time: $time");
            }
        }
    }

    // Final schedule output
    error_log("Final schedule: " . print_r($schedule, true));

    // Fair distribution of tasks among superintendents
    distributeTasksFairly($superintendentTasks);

    return $schedule;
}

// Check if minDays is set before using it
$minDays = isset($_SESSION['minDays']) ? $_SESSION['minDays'] : 1; // Default to 1 if not set

// Generate the exam schedule
$schedule = generateExamSchedule($mysqli, $dates, $timeSlots, $minDays, $overlapCourses, $nonOverlapCourses, $eligibleSuperintendents, $feasibleHalls);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        table th,
        table td {
            min-width: 120px;
            /* Minimum column width */
            word-wrap: break-word;
            /* Wrap content if too long */
            vertical-align: middle;
            /* Center align vertically */
        }

        .table th,
        .table td {
            padding: 15px;
            /* Add padding for better spacing */
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <?php
        // Display Schedule
        echo "<div class='container my-5'>";
        echo "<div class='card mx-auto w-75 shadow'>";
        echo "<div class='card-header bg-primary text-white'>";
        echo "<h2 class='text-center'>Schedule</h2>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered table-striped table-hover text-center'>";
        echo "<thead class='table-dark'>";
        echo "<tr><th>Time</th>";
        foreach ($dates as $day) {
            echo "<th>{$day['label']}<br>({$day['day_of_week']})</th>";
        }
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        // Loop through each time slot
        foreach ($timeSlots as $time) {
            echo "<tr>";
            echo "<td><strong>$time</strong></td>"; // Time slot in the first column

            // Add course, superintendent, and exam hall under each day
            foreach ($dates as $dateInfo) {
                $date = $dateInfo['date'];

                // Check if the date exists in the schedule; if not, initialize it
                if (!isset($schedule[$date])) {
                    $schedule[$date] = []; // Initialize as an empty array if date is not set
                }

                // Ensure that $schedule[$date] is an array before filtering
                $slotDetails = array_filter($schedule[$date], function ($slot) use ($time) {
                    return $slot['time_slot'] === $time;
                });

                if (!empty($slotDetails)) {
                    $slotDetail = array_shift($slotDetails);
                    echo "<td>{$slotDetail['course_name']} - ({$slotDetail['superintendent']} - {$slotDetail['exam_hall']})</td>";
                } else {
                    echo "<td></td>"; // Empty cell if no slot is scheduled
                }
            }
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
