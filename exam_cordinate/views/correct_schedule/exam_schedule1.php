<?php

// Set error reporting to suppress notices and warnings
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 1);

// Database connection
include_once('../../../config/db.php');

// Fetch courses, enrollments, and exam hall data
$stmt_courses = $pdo->prepare("SELECT id, course_code FROM courses");
$stmt_courses->execute();
$courses = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

$stmt_enrollments = $pdo->prepare("SELECT course_id, COUNT(*) as student_count FROM enrollments GROUP BY course_id");
$stmt_enrollments->execute();
$enrollments = $stmt_enrollments->fetchAll(PDO::FETCH_ASSOC);

// Fetch exam halls without the superintendent_id reference
$stmt_halls = $pdo->prepare("SELECT hall_number, seating_capacity FROM exam_halls ORDER BY seating_capacity ASC");
$stmt_halls->execute();
$halls = $stmt_halls->fetchAll(PDO::FETCH_ASSOC);

// Fetch superintendents and their assigned courses (update the query)
$stmt_superintendents = $pdo->prepare("
    SELECT sp.id, sp.name 
    FROM superintendents sp 
    LEFT JOIN superintendent_courses sc ON sc.superintendent_id = sp.id
");
$stmt_superintendents->execute();
$superintendents = $stmt_superintendents->fetchAll(PDO::FETCH_ASSOC);

// Map courses to student counts
$course_students = [];
foreach ($enrollments as $enrollment) {
    $course_students[$enrollment['course_id']] = $enrollment['student_count'];
}

// Helper function to find available hall
function findAvailableHall($students_needed, $halls) {
    foreach ($halls as $hall) {
        if ($hall['seating_capacity'] >= $students_needed) {
            return $hall;
        }
    }
    return null;  // No feasible hall
}

// Function to find eligible superintendents for a course
function findEligibleSuperintendents($course_id, $superintendents) {
    $eligible_superintendents = [];
    foreach ($superintendents as $superintendent) {
        $assigned_courses = getAssignedCourses($superintendent['id']);
        if (!in_array($course_id, $assigned_courses)) {
            $eligible_superintendents[] = $superintendent;
        }
    }
    return $eligible_superintendents;
}

// Function to get the courses assigned to a superintendent (query the database)
function getAssignedCourses($superintendent_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT course_id FROM superintendent_courses WHERE superintendent_id = :superintendent_id");
    $stmt->bindParam(':superintendent_id', $superintendent_id, PDO::PARAM_INT);
    $stmt->execute();
    $assigned_courses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $assigned_courses;
}

// Function to fairly distribute superintendent duties
function distributeSuperintendentDuties($exam_schedule, $superintendents) {
    // Initialize a count of exams assigned to each superintendent
    $superintendent_assignments = array_fill_keys(array_column($superintendents, 'id'), 0);

    // Distribute duties fairly across days and slots
    foreach ($exam_schedule as $day => $slots) {
        foreach ($slots as $slot => $exam) {
            if ($exam) {
                // Find the superintendent with the least number of assigned exams
                $least_assigned_superintendent = array_keys($superintendent_assignments, min($superintendent_assignments))[0];
                // Assign the exam to this superintendent
                $exam_schedule[$day][$slot]['superintendent'] = $superintendents[$least_assigned_superintendent]['name'];
                // Update the assignment count for that superintendent
                $superintendent_assignments[$least_assigned_superintendent]++;
            }
        }
    }
    return $exam_schedule;
}

// Define exam days and slots
$exam_days = [
    'Day 1 (Monday)', 'Day 2 (Tuesday)', 'Day 3 (Wednesday)', 
    'Day 4 (Thursday)', 'Day 5 (Friday)', 'Day 6 (Saturday)', 
    'Day 7 (Monday)', 'Day 8 (Tuesday)'
];

$slots_per_day = 5;
$exam_slots = [];
foreach ($exam_days as $day) {
    for ($slot = 1; $slot <= $slots_per_day; $slot++) {
        $exam_slots[$day][$slot] = null;  // Null means no exam in this slot
    }
}

// Schedule exams
foreach ($courses as $course) {
    $course_id = $course['id'];
    $students_needed = $course_students[$course_id] ?? 0;
    $hall = findAvailableHall($students_needed, $halls);
    
    if ($hall) {
        // Assign the course to a day and slot
        $day = $exam_days[array_rand($exam_days)];  // Randomly select a day for now
        $slot = rand(1, $slots_per_day);  // Randomly select a slot for now

        // Get eligible superintendents for the course
        $eligible_superintendents = findEligibleSuperintendents($course_id, $superintendents);

        // Assign a superintendent
        if ($eligible_superintendents) {
            $superintendent = $eligible_superintendents[array_rand($eligible_superintendents)];
        } else {
            $superintendent = ['name' => 'No eligible superintendent'];  // Fallback case
        }

        // Assign the exam slot
        $exam_slots[$day][$slot] = [
            'course_code' => $course['course_code'],
            'hall' => $hall['hall_number'],
            'superintendent' => $superintendent['name'] // Store and display the superintendent's name
        ];
    }
}

// Fairly distribute superintendent duties
$exam_slots = distributeSuperintendentDuties($exam_slots, $superintendents);

// Display the schedule
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Schedule</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include('../layout/header.php');?> 

<div class="container mt-5">
    <h2 class="mb-4">Exam Schedule</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Time Slot</th>
                <?php foreach ($exam_days as $day): ?>
                    <th><?php echo htmlspecialchars($day ?? ''); ?></th> <!-- Check if day is null before passing to htmlspecialchars -->
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            // Time slots (8:00-9:30, 10:00-11:30, etc.)
            $time_slots = ['8:00 - 9:30', '10:00 - 11:30', '12:00 - 1:30', '2:00 - 3:30', '4:00 - 5:30'];
            
            // Display the schedule
            foreach ($time_slots as $index => $time_slot):
                if ($index + 1 > $slots_per_day) {
                    break; // Prevent accessing out of bounds
                }
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($time_slot); ?></td>
                    <?php
                    foreach ($exam_days as $day):
                        $exam = $exam_slots[$day][$index + 1] ?? null;
                        if ($exam):
                            echo "<td>";
                            echo "Course: " . htmlspecialchars($exam['course_code'] ?? '') . "<br>"; // Check for null values before passing to htmlspecialchars
                            echo "Hall: " . htmlspecialchars($exam['hall'] ?? '') . "<br>"; // Check for null values before passing to htmlspecialchars
                            echo "Superintendent: " . htmlspecialchars($exam['superintendent'] ?? '') . "<br>"; // Check for null values before passing to htmlspecialchars
                            echo "</td>";
                        else:
                            echo "<td>-</td>";
                        endif;
                    endforeach;
                    ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
