<?php
session_start();
include_once('config/database.php');

// Check database connection
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// ... (All the functions: getOverlappingCourses, getNonOverlappingCourses, getEligibleSuperintendents, distributeTasksFairly, feasibleExamHall, generateTimeSlots, generateDates, generateExamSlots - same as in the previous improved version)

// Handle Form Input and set $minDays
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $minDays = isset($_POST['minDays']) ? (int)$_POST['minDays'] : 2;
    $_SESSION['minDays'] = $minDays;
} else if (isset($_SESSION['minDays'])) {
    $minDays = $_SESSION['minDays'];
}

$overlapCourses = getOverlappingCourses($mysqli);
$non_overlapping_courses = getNonOverlappingCourses($mysqli);
$eligibleSuperintendents = getEligibleSuperintendents($mysqli);
$feasibleHalls = feasibleExamHall($mysqli);
$taskDistribution = distributeTasksFairly($eligibleSuperintendents);

$startTime = "8:00 AM";
$endTime = "5:00 PM";
$slotDuration = 90;
$breakTime = 30;
$timeSlots = generateTimeSlots($startTime, $endTime, $slotDuration, $breakTime);

$startDate = "2024-12-04";
$numberOfDays = 30;
$weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$dates = generateDates($startDate, $numberOfDays, $weekdays);

$weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$slotsPerDay = 5;
$examSlots = generateExamSlots($weekdays, $slotsPerDay, $minDays);

function generateExamSchedule($mysqli, $dates, $timeSlots, $minDays, $overlapCourses, $eligibleSuperintendents, $feasibleHalls, $taskDistribution) {
    $schedule = [];
    $courseAssignments = [];
    $superintendentAssignments = [];

    foreach ($dates as $dateInfo) {
        $date = $dateInfo['date'];
        foreach ($timeSlots as $time) {
            foreach ($overlapCourses as $course => $overlaps) {
                if (isset($courseAssignments[$course])) continue;

                $canSchedule = true;
                foreach ($courseAssignments as $assignedCourse => $assignedDate) {
                    if (in_array($course, $overlaps) && abs(strtotime($date) - strtotime($assignedDate)) < ($minDays * 86400)) {
                        $canSchedule = false;
                        break;
                    }
                }

                if ($canSchedule) {
                    $eligibleSuperintendentId = null;
                    $eligibleSuperintendentName = null;
                    foreach ($taskDistribution as $superintendentId => $superintendentData) {
                        if (in_array($course, $superintendentData['courses'])) {
                            $eligibleSuperintendentId = $superintendentId;
                            $eligibleSuperintendentName = $superintendentData['superintendent_name'];
                            break;
                        }
                    }

                    if ($eligibleSuperintendentId === null) continue;

                    $feasibleExamHall = null;
                    $enrollmentCount = $mysqli->query("SELECT COUNT(student_id) AS count FROM enrollments WHERE course_id = (SELECT id FROM courses WHERE course_code = '$course')")->fetch_assoc()['count'];
                    $availableHalls = array_filter($feasibleHalls, function ($hall) use ($enrollmentCount) {
                        return $hall['assigned_hall']['seating_capacity'] >= $enrollmentCount;
                    });
                    usort($availableHalls, function ($a, $b) {
                        return $a['assigned_hall']['seating_capacity'] <=> $b['assigned_hall']['seating_capacity'];
                    });
                    if (!empty($availableHalls)) {
                        $feasibleExamHall = $availableHalls[0]['assigned_hall'];
                    }

                    if ($feasibleExamHall === null) continue;

                    $superintendentAlreadyAssigned = false;
                    foreach ($superintendentAssignments as $assignedSchedule) {
                        if ($assignedSchedule['superintendent'] == $eligibleSuperintendentId && $assignedSchedule['date'] == $date && $assignedSchedule['time_slot'] == $time) {
                            $superintendentAlreadyAssigned = true;
                            break;
                        }
                    }
                    if ($superintendentAlreadyAssigned) continue;

                    $schedule[$date][] = [
                        'time_slot' => $time,
                        'course_name' => $course,
                        'superintendent' => $eligibleSuperintendentName,
                        'exam_hall' => $feasibleExamHall['building'] . ' ' . $feasibleExamHall['hall_number']
                    ];

                    $courseAssignments[$course] = $date;
                    $superintendentAssignments[] = ['superintendent' => $eligibleSuperintendentId, 'date' => $date, 'time_slot' => $time];
                    break;
                }
            }
        }
    }

    return $schedule;
}

$schedule = generateExamSchedule($mysqli, $dates, $timeSlots, $minDays, $overlapCourses, $eligibleSuperintendents, $feasibleHalls, $taskDistribution);

// ... (HTML display code - same as before)

$mysqli->close();
?>
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