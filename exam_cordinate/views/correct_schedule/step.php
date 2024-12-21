<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Exam Scheduling Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Enhanced Exam Scheduling Form</h2>
        <form action="step.php" method="POST">
            <div class="row mb-3">
                <label for="min_days" class="col-md-4 col-form-label">Minimum Days Between Exams:</label>
                <div class="col-md-8">
                    <input type="number" id="min_days" name="min_days" class="form-control" required>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" name="generate_schedule" class="btn btn-primary">Generate Exam Schedule</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
include_once('../../../config/database.php');

// Define time slots and days of the week
$time_slots = [
    '8:00 - 9:30', '10:00 - 11:30', '12:00 - 1:30', '2:00 - 3:30', '4:00 - 5:30'
];
$days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Function to get overlapping courses
function getOverlappingCourses($mysqli) {
    $stmt_courses = $mysqli->prepare("SELECT id, course_code FROM courses");
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);

    $overlapping_courses = [];
    $course_students = [];

    foreach ($courses as $course) {
        $course_id = $course['id'];
        $stmt_students = $mysqli->prepare("SELECT student_id FROM enrollments WHERE course_id = ?");
        $stmt_students->bind_param('i', $course_id);
        $stmt_students->execute();
        $result_students = $stmt_students->get_result();
        $students = array_column($result_students->fetch_all(MYSQLI_ASSOC), 'student_id');
        $course_students[$course['course_code']] = $students;
        $stmt_students->close();
    }

    foreach ($course_students as $course_code => $students) {
        $overlapping_courses[$course_code] = [];
        foreach ($course_students as $other_course_code => $other_students) {
            if ($course_code !== $other_course_code) {
                if (array_intersect($students, $other_students)) {
                    $overlapping_courses[$course_code][] = $other_course_code;
                }
            }
        }
    }

    return $overlapping_courses;
}

// Function to get eligible superintendents
function getEligibleSuperintendents($mysqli) {
    $stmt_courses = $mysqli->prepare("SELECT id, course_code FROM courses");
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();
    $courses = $result_courses->fetch_all(MYSQLI_ASSOC);

    $eligible_superintendents = [];

    foreach ($courses as $course) {
        $course_id = $course['id'];
        $stmt_superintendents = $mysqli->prepare(
            "SELECT s.id, s.name FROM superintendents s 
             WHERE NOT EXISTS (
                SELECT 1 FROM superintendent_courses sc 
                WHERE sc.superintendent_id = s.id AND sc.course_id = ? 
             )"
        );
        $stmt_superintendents->bind_param("i", $course_id);
        $stmt_superintendents->execute();
        $result_superintendents = $stmt_superintendents->get_result();
        while ($row = $result_superintendents->fetch_assoc()) {
            $eligible_superintendents[$course['course_code']][] = $row['name'];
        }
        $stmt_superintendents->close();
    }

    return $eligible_superintendents;
}

// Function to assign feasible exam halls
function feasibleExamHall($mysqli) {
    // Fetch courses and enrollments
    $courses = $mysqli->query("SELECT c.id AS course_id, c.course_code, c.course_name, COUNT(e.student_id) AS enrollment_count
        FROM courses c LEFT JOIN enrollments e ON c.id = e.course_id
        GROUP BY c.id, c.course_code, c.course_name")->fetch_all(MYSQLI_ASSOC);

    // Fetch exam halls, sorted by seating capacity
    $exam_halls = $mysqli->query("SELECT id, building, floor, hall_number, seating_capacity 
        FROM exam_halls ORDER BY seating_capacity ASC")->fetch_all(MYSQLI_ASSOC);

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

        // Add the result for the current course (indexed by course code)
        $feasible_exam_halls[$course['course_code']] = [
            'course_name' => $course['course_name'],
            'enrollment_count' => $enrollment_count,
            'assigned_hall' => $selected_hall
        ];
    }

    return $feasible_exam_halls;
}

// Function to schedule exams
function scheduleExams($time_slots, $days_of_week, $min_days, $overlapping_courses) {
    $schedule = [];
    $last_exam_day = [];
    foreach ($overlapping_courses as $course_code => $overlap) {
        $last_exam_day[$course_code] = -$min_days;
    }

    $current_day = 0;
    $current_time_slot = 0;

    foreach ($overlapping_courses as $course_code => $overlap) {
        while (true) {
            $conflict = false;

            // Check for overlapping courses
            foreach ($overlap as $overlap_course) {
                if (isset($schedule[$days_of_week[$current_day % count($days_of_week)]][$time_slots[$current_time_slot]]) &&
                    $schedule[$days_of_week[$current_day % count($days_of_week)]][$time_slots[$current_time_slot]] === $overlap_course) {
                    $conflict = true;
                    break;
                }
            }

            // Check for minimum days between exams
            if ($conflict || ($current_day - $last_exam_day[$course_code]) < $min_days) {
                $current_time_slot++;
                if ($current_time_slot >= count($time_slots)) {
                    $current_time_slot = 0;
                    $current_day++;
                }
                continue;
            }

            // Assign course to the schedule
            $schedule[$days_of_week[$current_day % count($days_of_week)]][$time_slots[$current_time_slot]] = $course_code;
            $last_exam_day[$course_code] = $current_day;
            break;
        }
    }

    return $schedule;
}

// Function to assign superintendents to exams
function assignSuperintendents($schedule, $eligibleSuperintendents, $days_of_week, $time_slots) {
    $superintendent_assignments = [];
    $assigned_superintendents = []; // Track assigned superintendents per day and time slot

    foreach ($schedule as $day => $slots) {
        foreach ($slots as $time_slot => $course_code) {
            if (isset($eligibleSuperintendents[$course_code]) && !empty($eligibleSuperintendents[$course_code])) {
                // Loop through the eligible superintendents for the course
                foreach ($eligibleSuperintendents[$course_code] as $key => $superintendent) {
                    // Check if the superintendent is already assigned to another exam at the same time on the same day
                    $conflict = false;
                    foreach ($assigned_superintendents as $assigned_day => $assigned_slots) {
                        if (in_array($superintendent, $assigned_slots)) {
                            $conflict = true;
                            break;
                        }
                    }

                    if (!$conflict) {
                        // Assign the superintendent if no conflict is found
                        $superintendent_assignments[$course_code] = $superintendent;
                        // Mark this superintendent as assigned to the current day and time slot
                        $assigned_superintendents[$day][$time_slot][] = $superintendent;
                        // Remove the assigned superintendent from the eligible list
                        unset($eligibleSuperintendents[$course_code][$key]);
                        break; // Exit the loop after assigning a suitable superintendent
                    }
                }
            } else {
                $superintendent_assignments[$course_code] = 'No available superintendent';
            }
        }
    }

    return $superintendent_assignments;
}

// Form submission logic
if (isset($_POST['generate_schedule'])) {
    $min_days = isset($_POST['min_days']) ? (int)$_POST['min_days'] : 1;

    $overlapCourses = getOverlappingCourses($mysqli);
    $eligibleSuperintendents = getEligibleSuperintendents($mysqli);
    $feasibleHalls = feasibleExamHall($mysqli);

    if (!empty($overlapCourses)) {
        $schedule = scheduleExams($time_slots, $days_of_week, $min_days, $overlapCourses);
        $superintendent_assignments = assignSuperintendents($schedule, $eligibleSuperintendents, $days_of_week, $time_slots);

        // Display schedule in a single table with course code, assigned superintendent, and exam hall
        echo "<h4>Exam Schedule</h4>";
        echo "<table class='table table-bordered'><tr><th>Time Slot</th>";
        foreach ($days_of_week as $day) {
            echo "<th>$day</th>";
        }
        echo "</tr>";

        foreach ($time_slots as $time) {
            echo "<tr><td>$time</td>";
            foreach ($days_of_week as $day) {
                $course_code = isset($schedule[$day][$time]) ? $schedule[$day][$time] : 'N/A';
                $superintendent = isset($superintendent_assignments[$course_code]) ? $superintendent_assignments[$course_code] : 'N/A';
                
                $hall = 'N/A';
                if ($course_code !== 'N/A' && isset($feasibleHalls[$course_code])) {
                    $assigned_hall = $feasibleHalls[$course_code]['assigned_hall'];
                    $hall = $assigned_hall ? $assigned_hall['building'] . ' ' . $assigned_hall['floor'] . ' ' . $assigned_hall['hall_number'] : 'N/A';
                }

                echo "<td>Course: $course_code<br>Supervisor: $superintendent<br>Hall: $hall</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}
?>