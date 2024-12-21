<?php
session_start();
include_once('../../../config/database.php');

// Handle Form Input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slotsPerDay = isset($_POST['slotsPerDay']) ? (int)$_POST['slotsPerDay'] : 0;
    $minDays = isset($_POST['minDays']) ? (int)$_POST['minDays'] : 0;

    // Validate inputs
    if ($slotsPerDay < 1 || $slotsPerDay > 5) {
        die("Slots per day must be between 1 and 5.");
    }
    if ($minDays < 1 || $minDays > 30) {
        die("Minimum days between exams must be between 1 and 30.");
    }

    // Store inputs in session
    $_SESSION['slotsPerDay'] = $slotsPerDay;
    $_SESSION['minDays'] = $minDays;
}


// Function to get overlapping courses
    function getOverlappingCourses($mysqli)
{
    // Fetch all courses using mysqli
    $stmt_courses = $mysqli->prepare("SELECT id, course_code FROM courses");
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);

    // Initialize array to hold non-overlapping courses
    $overlapping_courses = [];

    // Find non-overlapping courses
    foreach ($courses as $course) {
        $course_id = $course['id'];
        $course_code = $course['course_code'];

        // Get students enrolled in this course
        $query_students = "SELECT student_id FROM enrollments WHERE course_id = ?";
        $stmt_students = $mysqli->prepare($query_students);
        $stmt_students->bind_param('i', $course_id);
        $stmt_students->execute();
        $result_students = $stmt_students->get_result();
        $students = array_column($result_students->fetch_all(MYSQLI_ASSOC), 'student_id');
        $stmt_students->close();

        $overlapping = [];

        // Check against all other courses
        foreach ($courses as $other_course) {
            if ($other_course['id'] === $course_id) continue;

            // Get students enrolled in the other course
            $query_other_students = "SELECT student_id FROM enrollments WHERE course_id = ?";
            $stmt_other_students = $mysqli->prepare($query_other_students);
            $stmt_other_students->bind_param('i', $other_course['id']);
            $stmt_other_students->execute();
            $result_other_students = $stmt_other_students->get_result();
            $other_students = array_column($result_other_students->fetch_all(MYSQLI_ASSOC), 'student_id');
            $stmt_other_students->close();

            // Check if there's any overlap
            $overlap = array_intersect($students, $other_students);

            // If there is no overlap, add the other course to the non-overlapping list
            if ($overlap) {
                $overlapping[] = $other_course['course_code'];
            }
        }

        // Store non-overlapping courses
        if ($overlapping) {
            $overlapping_courses[$course_code] = $overlapping;
        } else {
            $overlapping_courses[$course_code] = "No overlapping courses";
        }
    }

    return $overlapping_courses;
}

// Call the function to get overlapping courses
$overlapCourses = getOverlappingCourses($mysqli);

// // Properly print the result
// echo "Overlapping Course";
// echo "<pre>";
// print_r($overlapCourses); // Use print_r to show the array in a readable format
// echo "</pre>";

// Function to get non-overlapping courses
function getNonOverlappingCourses($mysqli)
{
    // Fetch all courses using mysqli
    $stmt_courses = $mysqli->prepare("SELECT id, course_code FROM courses");
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);

    // Initialize array to hold non-overlapping courses
    $non_overlapping_courses = [];

    // Find non-overlapping courses
    foreach ($courses as $course) {
        $course_id = $course['id'];
        $course_code = $course['course_code'];

        // Get students enrolled in this course
        $query_students = "SELECT student_id FROM enrollments WHERE course_id = ?";
        $stmt_students = $mysqli->prepare($query_students);
        $stmt_students->bind_param('i', $course_id);
        $stmt_students->execute();
        $result_students = $stmt_students->get_result();
        $students = array_column($result_students->fetch_all(MYSQLI_ASSOC), 'student_id');
        $stmt_students->close();

        $non_overlapping = [];

        // Check against all other courses
        foreach ($courses as $other_course) {
            if ($other_course['id'] === $course_id) continue;

            // Get students enrolled in the other course
            $query_other_students = "SELECT student_id FROM enrollments WHERE course_id = ?";
            $stmt_other_students = $mysqli->prepare($query_other_students);
            $stmt_other_students->bind_param('i', $other_course['id']);
            $stmt_other_students->execute();
            $result_other_students = $stmt_other_students->get_result();
            $other_students = array_column($result_other_students->fetch_all(MYSQLI_ASSOC), 'student_id');
            $stmt_other_students->close();

            // Check if there's any overlap
            $overlap = array_intersect($students, $other_students);

            // If there is no overlap, add the other course to the non-overlapping list
            if (empty($overlap)) {
                $non_overlapping[] = $other_course['course_code'];
            }
        }

        // Store non-overlapping courses
        if (!empty($non_overlapping)) {
            $non_overlapping_courses[$course_code] = $non_overlapping;
        } else {
            $non_overlapping_courses[$course_code] = "No non-overlapping courses";
        }
    }

    return $non_overlapping_courses;
}

// Call the function to get non-overlapping courses
$non_overlapping_courses = getNonOverlappingCourses($mysqli);
// // Properly print the result
// echo "NON Overlapping Course";
// echo "<pre>";
// print_r($non_overlapping_courses); // Use print_r to show the array in a readable format
// echo "</pre>";

function getEligibleSuperintendents($mysqli)
{
    // Fetch all courses
    $courses = $mysqli->query("SELECT * FROM courses")->fetch_all(MYSQLI_ASSOC);

    $eligibleSuperintendents = [];

    // Iterate over each course
    foreach ($courses as $course) {
        $course_id = $course['id'];
        $course_code = $course['course_code'];

        // Prepare the query to find superintendents who have not been assigned to this course
        $stmt = $mysqli->prepare("SELECT s.id, s.name
                    FROM superintendents s
                    WHERE NOT EXISTS (
                        SELECT 1
                        FROM superintendent_courses sc
                        WHERE sc.superintendent_id = s.id AND sc.course_id = ?
                    )");

        // Bind the parameter for the course_id
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result_superintendent = $stmt->get_result();

        // Fetch the results and add to eligibleSuperintendents
        while ($row = $result_superintendent->fetch_assoc()) {
            $superintendent_name = $row['name'];

            // Initialize the superintendent entry if not already set
            if (!isset($eligibleSuperintendents[$superintendent_name])) {
                $eligibleSuperintendents[$superintendent_name] = [];  // Initialize as an empty array
            }

            // Add the course to the superintendent's course list
            $eligibleSuperintendents[$superintendent_name][] = $course_code;
        }
    }

    return $eligibleSuperintendents;
}

// Call the function and print the result
$eligibleSuperintendents = getEligibleSuperintendents($mysqli);
// echo "Eligible Superintendents";
// echo "<pre>";
// print_r($eligibleSuperintendents); // Use print_r to show the array in a readable format
// echo "</pre>";


function distributeTasksFairly($superintendentTasks) {
    // Function to get common courses
    function getCommonCourses($array1, $array2) {
        return array_intersect($array1, $array2);
    }
    
    // Step 1: Loop through each superintendent and their courses
    foreach ($superintendentTasks as $superintendentName => $courses) {
        foreach ($courses as $course) {
            foreach ($superintendentTasks as $otherSuperintendent => $otherCourses) {
                // Ensure we don't compare the same superintendent with itself
                if ($superintendentName !== $otherSuperintendent) {
                    // Find common courses
                    $commonCourses = getCommonCourses($superintendentTasks[$superintendentName], $superintendentTasks[$otherSuperintendent]);
                    
                    // If a course is common, remove it from the current list
                    if (in_array($course, $commonCourses)) {
                        $key = array_search($course, $superintendentTasks[$superintendentName]);
                        if ($key !== false) {
                            unset($superintendentTasks[$superintendentName][$key]);
                        }
                    }
                }
            }
        }
    }
    
    // Step 2: Fair distribution of tasks
    // Count tasks for each superintendent
    $taskCounts = array_map('count', $superintendentTasks);
    
    // Find the max and min task counts
    $maxTasks = max($taskCounts);
    $minTasks = min($taskCounts);
    
    // While the difference between max and min tasks is greater than 2
    while ($maxTasks - $minTasks > 2) {
        // Find the superintendent with max and min tasks
        $maxSuperintendent = array_search($maxTasks, $taskCounts);
        $minSuperintendent = array_search($minTasks, $taskCounts);
        
        // Move one task from maxSuperintendent to minSuperintendent
        $taskToMove = array_pop($superintendentTasks[$maxSuperintendent]);
        array_push($superintendentTasks[$minSuperintendent], $taskToMove);
        
        // Recalculate task counts
        $taskCounts = array_map('count', $superintendentTasks);
        $maxTasks = max($taskCounts);
        $minTasks = min($taskCounts);
    }

    return $superintendentTasks;
}

$taskDistribution = distributeTasksFairly($eligibleSuperintendents);
// echo "<pre>";
// print_r($taskDistribution);
// echo "</pre>";

function feasibleExamHall($mysqli)
{
    // Fetch courses and enrollments
    $courses = $mysqli->query("
        SELECT c.id AS course_id, c.course_name, COUNT(e.student_id) AS enrollment_count
        FROM courses c
        LEFT JOIN enrollments e ON c.id = e.course_id
        GROUP BY c.id, c.course_name
    ")->fetch_all(MYSQLI_ASSOC);

    // Fetch exam halls, sorted by seating capacity
    $exam_halls = $mysqli->query("
        SELECT id, building, floor, hall_number, seating_capacity 
        FROM exam_halls
        ORDER BY seating_capacity ASC
    ")->fetch_all(MYSQLI_ASSOC);

    $assigned_halls = []; // To track assigned halls
    $feasible_exam_halls = [];

    foreach ($courses as $course) {
        $enrollment_count = $course['enrollment_count'];
        $selected_hall = null;

        // Find the first available hall with enough capacity
        foreach ($exam_halls as $hall) {
            if ($hall['seating_capacity'] >= $enrollment_count && !in_array($hall['id'], $assigned_halls)) {
                $selected_hall = $hall;
                $assigned_halls[] = $hall['id']; // Mark hall as assigned
                break; // Exit loop after finding a suitable hall
            }
        }

        // Add the result for the current course
        $feasible_exam_halls[$course['course_name']] = [
            // 'course_id' => $course['course_id'],
            'course_name' => $course['course_name'],
            'enrollment_count' => $enrollment_count,
            'assigned_hall' => $selected_hall
        ];
    }

    return $feasible_exam_halls;
}


// Now use the function to get feasible exam halls for courses
$feasibleHalls = feasibleExamHall($mysqli);
// echo "fessible hall";
// echo "<pre>";
// print_r($feasibleHalls); // Use print_r to show the array in a readable format
// echo "</pre>";

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

function generateExamSchedule($mysqli, $dates, $timeSlots, $minDays, $overlapCourses, $nonOverlapCourses, $eligibleSuperintendents, $feasibleHalls,$taskDistribution)
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
                } 
                
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


// Check if minDays is set before using it
$minDays = isset($_SESSION['minDays']) ? $_SESSION['minDays'] : 1; // Default to 1 if not set

// Generate the exam schedule
$schedule = generateExamSchedule($mysqli, $dates, $timeSlots, $minDays, $overlapCourses, $nonOverlapCourses, $eligibleSuperintendents, $feasibleHalls,$taskDistribution);


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

