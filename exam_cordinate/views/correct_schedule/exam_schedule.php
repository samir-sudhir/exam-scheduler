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


/**
 * getOverlappingCourses
 *
 * @param  mixed $mysqli
 * @return void
 */
function getOverlappingCourses($mysqli) {
    $stmt_courses = $mysqli->prepare("SELECT id, course_code FROM courses");
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);
    $overlapping_courses = [];

    foreach ($courses as $course) {
        $course_id = $course['id'];
        $course_code = $course['course_code'];

        $query_students = "SELECT student_id FROM enrollments WHERE course_id = ?";
        $stmt_students = $mysqli->prepare($query_students);
        $stmt_students->bind_param('i', $course_id);
        $stmt_students->execute();
        $result_students = $stmt_students->get_result();
        $students = array_column($result_students->fetch_all(MYSQLI_ASSOC), 'student_id');
        $stmt_students->close();

        $overlapping = [];

        foreach ($courses as $other_course) {
            if ($other_course['id'] === $course_id) continue;

            $query_other_students = "SELECT student_id FROM enrollments WHERE course_id = ?";
            $stmt_other_students = $mysqli->prepare($query_other_students);
            $stmt_other_students->bind_param('i', $other_course['id']);
            $stmt_other_students->execute();
            $result_other_students = $stmt_other_students->get_result();
            $other_students = array_column($result_other_students->fetch_all(MYSQLI_ASSOC), 'student_id');
            $stmt_other_students->close();

            $overlap = array_intersect($students, $other_students);
            if ($overlap) {
                $overlapping[] = $other_course['course_code'];
            }
        }

        $overlapping_courses[$course_code] = $overlapping ?: "No overlapping courses";
    }

    return $overlapping_courses;
}


/**
 * getNonOverlappingCourses
 *
 * @param  mixed $mysqli
 * @return void
 */
function getNonOverlappingCourses($mysqli) {
    $stmt_courses = $mysqli->prepare("SELECT id, course_code FROM courses");
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);
    $non_overlapping_courses = [];

    foreach ($courses as $course) {
        $course_id = $course['id'];
        $course_code = $course['course_code'];

        $query_students = "SELECT student_id FROM enrollments WHERE course_id = ?";
        $stmt_students = $mysqli->prepare($query_students);
        $stmt_students->bind_param('i', $course_id);
        $stmt_students->execute();
        $result_students = $stmt_students ->get_result();
        $students = array_column($result_students->fetch_all(MYSQLI_ASSOC), 'student_id');
        $stmt_students->close();

        $non_overlapping = [];

        foreach ($courses as $other_course) {
            if ($other_course['id'] === $course_id) continue;

            $query_other_students = "SELECT student_id FROM enrollments WHERE course_id = ?";
            $stmt_other_students = $mysqli->prepare($query_other_students);
            $stmt_other_students->bind_param('i', $other_course['id']);
            $stmt_other_students->execute();
            $result_other_students = $stmt_other_students->get_result();
            $other_students = array_column($result_other_students->fetch_all(MYSQLI_ASSOC), 'student_id');
            $stmt_other_students->close();

            $overlap = array_intersect($students, $other_students);
            if (!$overlap) {
                $non_overlapping[] = $other_course['course_code'];
            }
        }

        $non_overlapping_courses[$course_code] = $non_overlapping ?: "No non-overlapping courses";
    }

    return $non_overlapping_courses;
}


/**
 * getEligibleSuperintendents
 *
 * @param  mixed $mysqli
 * @return void
 */
function getEligibleSuperintendents($mysqli) {
    $stmt_courses = $mysqli->prepare("SELECT id, course_code FROM courses");
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);
    $eligible_superintendents = [];

    foreach ($courses as $course) {
        $course_id = $course['id'];
        $course_code = $course['course_code'];

        $stmt_superintendents = $mysqli->prepare("SELECT s.id, s.name
                    FROM superintendents s
                    WHERE NOT EXISTS (
                        SELECT 1
                        FROM superintendent_courses sc
                        WHERE sc.superintendent_id = s.id AND sc.course_id = ?
                    )");
        $stmt_superintendents->bind_param('i', $course_id);
        $stmt_superintendents->execute();
        $result_superintendent = $stmt_superintendents->get_result();

        while ($row = $result_superintendent->fetch_assoc()) {
            $superintendent_name = $row['name'];

            if (!isset($eligible_superintendents[$superintendent_name])) {
                $eligible_superintendents[$superintendent_name] = [];
            }

            $eligible_superintendents[$superintendent_name][] = $course_code;
        }
    }

    return $eligible_superintendents;
}

/**
 * distributeTasksFairly
 *
 * @param  mixed $superintendent_tasks
 * @return void
 */
function distributeTasksFairly($superintendent_tasks) {
    // Function to get common courses
    function getCommonCourses($array1, $array2) {
        return array_intersect($array1, $array2);
    }

    // Step 1: Loop through each superintendent and their courses
    foreach ($superintendent_tasks as $superintendent_name => $courses) {
        foreach ($courses as $course) {
            foreach ($superintendent_tasks as $other_superintendent => $other_courses) {
                // Ensure we don't compare the same superintendent with itself
                if ($superintendent_name !== $other_superintendent) {
                    // Find common courses
                    $common_courses = getCommonCourses($superintendent_tasks[$superintendent_name], $superintendent_tasks[$other_superintendent]);

                    // If a course is common, remove it from the current list
                    if (in_array($course, $common_courses)) {
                        $key = array_search($course, $superintendent_tasks[$superintendent_name]);
                        if ($key !== false) {
                            unset($superintendent_tasks[$superintendent_name][$key]);
                        }
                    }
                }
            }
        }
    }

    // Step 2: Fair distribution of tasks
    // Count tasks for each superintendent
    $task_counts = array_map('count', $superintendent_tasks);

    // Find the max and min task counts
    $max_tasks = max($task_counts);
    $min_tasks = min($task_counts);

    // While the difference between max and min tasks is greater than 2
    while ($max_tasks - $min_tasks > 2) {
        // Find the superintendent with max and min tasks
        $max_superintendent = array_search($max_tasks, $task_counts);
        $min_superintendent = array_search($min_tasks, $task_counts);

        // Move one task from maxSuperintendent to minSuperintendent
        $task_to_move = array_pop($superintendent_tasks[$max_superintendent]);
        array_push($superintendent_tasks[$min_superintendent], $task_to_move);

        // Recalculate task counts
        $task_counts = array_map('count', $superintendent_tasks);
        $max_tasks = max($task_counts);
        $min_tasks = min($task_counts);
    }

    return $superintendent_tasks;
}


/**
 * feasibleExamHall
 *
 * @param  mixed $mysqli
 * @return void
 */
function feasibleExamHall($mysqli) {
    $courses = $mysqli->query("
        SELECT c.id AS course_id, c.course_name, COUNT(e.student_id) AS enrollment_count
        FROM courses c
        LEFT JOIN enrollments e ON c.id = e.course_id
        GROUP BY c.id, c.course_name
    ")->fetch_all(MYSQLI_ASSOC);

    $exam_halls = $mysqli->query("
        SELECT id, building, floor, hall_number, seating_capacity 
        FROM exam_halls
        ORDER BY seating_capacity ASC
    ")->fetch_all(MYSQLI_ASSOC);

    $assigned_halls = [];
    $feasible_exam_halls = [];

    foreach ($courses as $course) {
        $enrollment_count = $course['enrollment_count'];
        $selected_hall = null;

        foreach ($exam_halls as $hall) {
            if ($hall['seating_capacity'] >= $enrollment_count && !in_array($hall['id'], $assigned_halls)) {
                $selected_hall = $hall;
                $assigned_halls[] = $hall['id'];
                break;
            }
        }

        $feasible_exam_halls[$course['course_name']] = [
            'course_name' => $course['course_name'],
            'enrollment_count' => $enrollment_count,
            'assigned_hall' => $selected_hall
        ];
    }

    return $feasible_exam_halls;
}


/**
 * generateTimeSlots
 *
 * @param  mixed $startTime
 * @param  mixed $endTime
 * @param  mixed $slotDuration
 * @param  mixed $breakTime
 * @return void
 */
// Function to generate time slots dynamically
function generateTimeSlots($startTime, $endTime, $slotDuration, $breakTime) {
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

// Call the function to generate time slots
$startTime = "8:00 AM"; // Example start time
$endTime = "5:00 PM"; // Example end time
$slotDuration = 90; // Example slot duration in minutes
$breakTime = 30; // Example break time in minutes
$timeSlots = generateTimeSlots($startTime, $endTime, $slotDuration, $breakTime);
/**
 * generateDates
 *
 * @param  mixed $startDate
 * @param  mixed $numberOfDays
 * @param  mixed $weekdays
 * @return void
 */
// Function to generate dates dynamically
function generateDates($startDate, $numberOfDays, $weekdays) {
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

// Call the function to generate dates


// Check for schedule availability
if (empty($schedule)) {
    echo "<p>No exam schedule available. Please check the input data.</p>";
    exit; // Stop further execution if no data is available
}

// Proceed to display the schedule
foreach ($schedule as $date => $exams) {
    echo "<h3>Schedule for $date</h3>";
    foreach ($exams as $exam) {
        echo "<p>Time: {$exam['time_slot']}, Course: {$exam['course_name']}, Superintendent: {$exam['superintendent']}, Hall: {$exam['exam_hall']}</p>";
    }
}


/**
 * generateExamSlots
 *
 * @param  mixed $weekdays
 * @param  mixed $slotsPerDay
 * @param  mixed $minDays
 * @return void
 */
function generateExamSlots($weekdays, $slotsPerDay, $minDays) {
    $examSlots = [];
    $dayCount = count($weekdays);
    $totalDays = 30; // Total days to generate slots for
    $lastScheduledDays = [];

    foreach ($weekdays as $day) {
        $examSlots[$day] = [];
    }

    for ($day = 0; $day < $totalDays; $day++) {
        $dayName = $weekdays[$day % $dayCount];

        if (isset($lastScheduledDays[$dayName]) && ($day - $lastScheduledDays[$dayName]) < $minDays) {
            continue; // Skip this day if it violates the minimum days rule
        }

        for ($slot = 1; $slot <= $slotsPerDay; $slot++) {
            $examSlots[$dayName][] = "Slot $slot";
        }

        $lastScheduledDays[$dayName] = $day;
    }

    return $examSlots;
}

/**  
 * generateExamSchedule  
 *  
 * @param  mixed $mysqli  
 * @param  mixed $dates  
 * @param  mixed $timeSlots  
 * @param  mixed $minDays  
 * @param  mixed $overlapCourses  
 * @param  mixed $nonOverlapCourses  
 * @param  mixed $eligibleSuperintendents  
 * @param  mixed $feasibleHalls  
 * @param  mixed $taskDistribution  
 * @return array  
 */  
function generateExamSchedule($mysqli, $dates, $timeSlots, $minDays, $overlapCourses, $nonOverlapCourses, $eligibleSuperintendents, $feasibleHalls, $taskDistribution) {  
    $schedule = [];   
    $courseAssignments = [];  
    $superintendentTasks = [];  

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
                if (isset($courseAssignments[$course])) {  
                    continue;  
                }  

                $canSchedule = true;  
                foreach ($courseAssignments as $assignedCourse => $assignedDate) {  
                    if (in_array($assignedCourse, $overlaps) && abs(strtotime($date) - strtotime($assignedDate)) < ($minDays * 86400)) {  
                        $canSchedule = false; // Cannot schedule due to minDays constraint  
                        break;  
                    }  
                }  

                if ($canSchedule) {  
                    // Find an eligible superintendent for the course  
                    $eligibleSuperintendent = findEligibleSuperintendent($course, $eligibleSuperintendents);  

                    if ($eligibleSuperintendent === null) {  
                        error_log("No eligible superintendent found for course $course");  
                        continue;  
                    }  

                    // Find a feasible exam hall for the course  
                    $feasibleExamHall = findFeasibleExamHall($enrollmentCounts[$course], $feasibleHalls);  

                    if ($feasibleExamHall === null) {  
                        error_log("No feasible exam hall found for course $course");  
                        continue;  
                    }  

                    // Assign the course to the schedule  
                    assignCourseToSchedule($schedule, $date, $time, $course, $eligibleSuperintendent, $feasibleExamHall);  

                    // Track assignments  
                    $courseAssignments[$course] = $date;  
                    $superintendentTasks[$eligibleSuperintendent][] = $course; // Track tasks for fair distribution  
                }  
            }  

            // Schedule non-overlapping courses (without minDays check)  
            foreach ($nonOverlapCourses as $course => $overlaps) {  
                if (isset($courseAssignments[$course])) {  
                    continue;  
                }  

                // Find an eligible superintendent for the course  
                $eligibleSuperintendent = findEligibleSuperintendent($course, $eligibleSuperintendents);  

                if ($eligibleSuperintendent === null) {  
                    error_log("No eligible superintendent found for course $course");  
                    continue;  
                }  

                // Find a feasible exam hall for the course  
                $feasibleExamHall = findFeasibleExamHall($enrollmentCounts[$course], $feasibleHalls);  

                if ($feasibleExamHall === null) {  
                    error_log("No feasible exam hall found for course $course");  
                    continue;  
                }  

                // Assign the course to the schedule  
                assignCourseToSchedule($schedule, $date, $time, $course, $eligibleSuperintendent, $feasibleExamHall);  

                // Track assignments  
                $courseAssignments[$course] = $date;  
                $superintendentTasks[$eligibleSuperintendent][] = $course; // Track tasks for fair distribution  
            }  
        }  
    }  

    // Distribute tasks fairly among superintendents  
    redistributeTasks($superintendentTasks, $taskDistribution);  

    return $schedule;   
}  

/**  
 * findEligibleSuperintendent  
 *  
 * @param  string $course  
 * @param  array $eligibleSuperintendents  
 * @return string|null  
 */  
 function findEligibleSuperintendent($course, $eligibleSuperintendents) {  
    foreach ($eligibleSuperintendents as $superintendent => $courses) {  
        if (in_array($course, $courses)) {  
            return $superintendent;  
        }  
    }  
    return null;  
}  

/**  
 * findFeasibleExamHall  
 *  
 * @param  int $enrollmentCount  
 * @param  array $feasibleHalls  
 * @return array|null  
 */  
 function findFeasibleExamHall($enrollmentCount, $feasibleHalls) {  
    foreach ($feasibleHalls as $hall) {  
        if ($hall['seating_capacity'] >= $enrollmentCount) {  
            return $hall;  
        }  
    }  
    return null;  
}  

/**  
 * assignCourseToSchedule  
 *  
 * @param  array $schedule  
 * @param  string $date  
 * @param  string $time  
 * @param  string $course  
 * @param  string $superintendent  
 * @param  array $feasibleExamHall  
 * @return void  
 */  
 function assignCourseToSchedule(&$schedule, $date, $time, $course, $superintendent, $feasibleExamHall) {  
    $schedule[$date][] = [  
        'time_slot' => $time,  
        'course_name' => $course,  
        'superintendent' => $superintendent,  
        'exam_hall' => $feasibleExamHall['building'] . ' ' . $feasibleExamHall['hall_number']  
    ];  
}  

/**  
 * redistributeTasks  
 *  
 * @param  array $superintendentTasks  
 * @param  array $taskDistribution  
 * @return void  
 */  
 function redistributeTasks($superintendentTasks, $taskDistribution) {  
    // Implement task redistribution logic based on the provided $taskDistribution  
    // This could involve moving tasks from superintendents with more tasks to those with fewer tasks  
    // to achieve a more balanced workload.  
}
// Call the function to generate the exam schedule
$schedule = generateExamSchedule($mysqli, $dates, $timeSlots, $minDays, $overlapCourses, $nonOverlapCourses, $eligibleSuperintendents, $feasibleHalls, $taskDistribution);



// Check for schedule availability  
if (empty($schedule)) {  
    echo "<p>No exam schedule available. Please check the input data.</p>";  
    exit; // Stop further execution if no data is available  
}  

// Proceed to display the schedule  
foreach ($schedule as $date => $exams) {  
    echo "<h3>Schedule for $date</h3>";  
    foreach ($exams as $exam) {  
        echo "<p>Time: {$exam['time_slot']}, Course: {$exam['course_name']}, Superintendent: {$exam['superintendent']}, Hall: {$exam['exam_hall']}</p>";  
    }  
}
// Call the functions to generate the exam schedule

$startDate = "2024-12-04"; // Example start date
$numberOfDays = 30; // Example number of days
$weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']; // Allowed weekdays
$dates = generateDates($startDate, $numberOfDays, $weekdays);
$timeSlots = generateTimeSlots($startTime, $endTime, $slotDuration, $breakTime);
// $schedule = generateExamSchedule($mysqli, $dates, $timeSlots, $minDays, $overlapCourses, $nonOverlapCourses, $eligibleSuperintendents, $feasibleHalls, $taskDistribution);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        table th, table td {
            min-width: 120px;
            word-wrap: break-word;
            vertical-align: middle;
        }
        .table th, .table td {
            padding: 15px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class='card mx-auto w-75 shadow'>
            <div class='card-header bg-primary text-white'>
                <h2 class='text-center'>Schedule</h2>
            </div>
            <div class='card-body'>
                <div class='table-responsive'>
                    <table class='table table-bordered table-striped table-hover text-center'>
                        <thead class='table-dark'>
                            <tr>
                                <th>Time</th>
                                <?php foreach ($dates as $day): ?>
                                    <th><?php echo "{$day['label']}<br>({$day['day_of_week']})"; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($timeSlots as $time): ?>
                                <tr>
                                    <td><strong><?php echo $time; ?></strong></td>
                                    <?php foreach ($dates as $dateInfo): ?>
                                        <td>
                                            <?php
                                            $date = $dateInfo['date'];
                                            $slotDetails = array_filter($schedule[$date], function ($slot) use ($time) {
                                                return $slot['time_slot'] === $time;
                                            });
                                            if (!empty($slotDetails)) {
                                                $slotDetail = array_shift($slotDetails);
                                                echo "{$slotDetail['course_name']} - ({$slotDetail['superintendent']} - {$slotDetail['exam_hall']})";
                                            } else {
                                                echo ""; // Empty cell if no slot is scheduled
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
