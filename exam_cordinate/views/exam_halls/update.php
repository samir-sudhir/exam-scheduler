<?php
require 'C:\xampp\htdocs\e_v\config\database.php';  // Adjust path if necessary

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    // Get the current details for the exam hall
    $id = $_GET['id'];
    
    // Use MySQLi to fetch the hall details
    $result = $mysqli->query("SELECT * FROM exam_halls WHERE id = $id");
    
    if ($result) {
        $hall = $result->fetch_assoc();
        
        if (!$hall) {
            // If no exam hall found, redirect to the list page
            header('Location: index.php');
            exit;
        }
    } else {
        die('Query failed: ' . $mysqli->error);
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update the exam hall details
    $id = $_POST['id'];
    $building = $_POST['building'];
    $floor = $_POST['floor'];
    $hall_number = $_POST['hall_number'];
    $seating_capacity = $_POST['seating_capacity'];

    // Prepare the update query
    $stmt = $mysqli->prepare("UPDATE exam_halls SET building = ?, floor = ?, hall_number = ?, seating_capacity = ? WHERE id = ?");
    
    if ($stmt) {
        // Bind the parameters and execute the statement
        $stmt->bind_param("ssssi", $building, $floor, $hall_number, $seating_capacity, $id);
        $stmt->execute();

        // Redirect after the update
        header('Location: index.php');
        exit;
    } else {
        die('Query preparation failed: ' . $mysqli->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Exam Hall</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">
<?php include('../layout/header.php'); ?>  <!-- Include the header -->

    <div class="container mt-5">
        <h1 class="mb-4">Update Exam Hall</h1>
        <form method="POST" class="card p-4 shadow">
            <input type="hidden" name="id" value="<?= htmlspecialchars($hall['id']) ?>">
            <div class="mb-3">
                <label class="form-label">Building:</label>
                <input type="text" name="building" class="form-control" value="<?= htmlspecialchars($hall['building']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Floor:</label>
                <input type="number" name="floor" class="form-control" value="<?= htmlspecialchars($hall['floor']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Hall Number:</label>
                <input type="text" name="hall_number" class="form-control" value="<?= htmlspecialchars($hall['hall_number']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Seating Capacity:</label>
                <input type="number" name="seating_capacity" class="form-control" value="<?= htmlspecialchars($hall['seating_capacity']) ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
