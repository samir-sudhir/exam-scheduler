<?php
require 'C:\xampp\htdocs\e_v\config\database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $building = $_POST['building'];
    $floor = $_POST['floor'];
    $hall_number = $_POST['hall_number'];
    $seating_capacity = $_POST['seating_capacity'];

    $stmt = $pdo->prepare("INSERT INTO exam_halls (building, floor, hall_number, seating_capacity) VALUES (?, ?, ?, ?)");
    $stmt->execute([$building, $floor, $hall_number, $seating_capacity]);

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Exam Hall</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<?php include('../layout/header.php');?>

    <div class="container mt-5">
        <h1 class="mb-4">Add Exam Hall</h1>
        <form method="POST" class="card p-4 shadow">
            <div class="mb-3">
                <label class="form-label">Building:</label>
                <input type="text" name="building" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Floor:</label>
                <input type="number" name="floor" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Hall Number:</label>
                <input type="text" name="hall_number" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Seating Capacity:</label>
                <input type="number" name="seating_capacity" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Save</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
