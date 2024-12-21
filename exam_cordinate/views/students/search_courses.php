<?php
require 'D:\newXampp\htdocs\exam_shedular_practice\e_v\config\database.php';

$search = $_GET['search'];

$query = "SELECT course_id, course_code FROM courses WHERE course_code LIKE ?";
$stmt = $mysqli->prepare($query);
$search_term = "%" . $search . "%";
$stmt->bind_param('s', $search_term);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];

while ($row = $result->fetch_assoc()) {
    $courses[] = [
        'id' => $row['course_id'],
        'text' => $row['course_code']
    ];
}

echo json_encode($courses);

$mysqli->close();
?>
