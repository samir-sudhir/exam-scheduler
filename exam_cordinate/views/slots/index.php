<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "a_4";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        // Add new time slot
        $slot_time = $_POST['slot_time'];
        $weekday = $_POST['weekday'];

        $stmt = $conn->prepare("INSERT INTO time_slots (slot_time, weekday) VALUES (?, ?)");
        $stmt->bind_param("ss", $slot_time, $weekday);
        if ($stmt->execute()) {
            echo "<script>alert('Time slot added successfully');</script>";
        } else {
            echo "<script>alert('Error adding time slot');</script>";
        }
        $stmt->close();
    } elseif (isset($_POST['update'])) {
        // Update time slot
        $slot_id = $_POST['slot_id'];
        $slot_time = $_POST['slot_time'];
        $weekday = $_POST['weekday'];

        $stmt = $conn->prepare("UPDATE time_slots SET slot_time = ?, weekday = ? WHERE slot_id = ?");
        $stmt->bind_param("ssi", $slot_time, $weekday, $slot_id);
        if ($stmt->execute()) {
            echo "<script>alert('Time slot updated successfully');</script>";
        } else {
            echo "<script>alert('Error updating time slot');</script>";
        }
        $stmt->close();
    } elseif (isset($_POST['delete'])) {
        // Delete time slot
        $slot_id = $_POST['slot_id'];

        $stmt = $conn->prepare("DELETE FROM time_slots WHERE slot_id = ?");
        $stmt->bind_param("i", $slot_id);
        if ($stmt->execute()) {
            echo "<script>alert('Time slot deleted successfully');</script>";
        } else {
            echo "<script>alert('Error deleting time slot');</script>";
        }
        $stmt->close();
    }
}

// Fetch time slots
$slotsResult = $conn->query("SELECT * FROM time_slots");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Slots CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include('../layout/header.php'); ?>

    <div class="container mt-5">
        <h2>Manage Time Slots</h2>
        
        <!-- Add Time Slot Form -->
        <h4 class="mt-4">Add Time Slot</h4>
        <form method="POST">
            <div class="mb-3">
                <label for="slot_time" class="form-label">Slot Time</label>
                <input type="time" class="form-control" name="slot_time" required>
            </div>
            <div class="mb-3">
                <label for="weekday" class="form-label">Weekday</label>
                <input type="text" class="form-control" name="weekday" required>
            </div>
            <button type="submit" name="add" class="btn btn-primary">Add Time Slot</button>
        </form>

        <!-- Display Time Slots -->
        <h4 class="mt-5">Existing Time Slots</h4>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Slot Time</th>
                    <th>Weekday</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php

            if ($slotsResult->num_rows > 0) {
                while ($row = $slotsResult->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['slot_time'] . '</td>';
                    echo '<td>' . $row['weekday'] . '</td>';
                    echo '<td>
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#updateModal" data-id="' . $row['slot_id'] . '" data-time="' . $row['slot_time'] . '" data-weekday="' . $row['weekday'] . '">Update</button>
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="slot_id" value="' . $row['slot_id'] . '">
                                <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                            </form>
                          </td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="3">No time slots found</td></tr>';
            }

        echo '</tbody>
        </table>
    </div>

    <!-- Update Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Time Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="slot_id" id="slot_id">
                        <div class="mb-3">
                            <label for="slot_time" class="form-label">Slot Time</label>
                            <input type="time" class="form-control" name="slot_time" id="slot_time" required>
                        </div>
                        <div class="mb-3">
                            <label for="weekday" class="form-label">Weekday</label>
                            <input type="text" class="form-control" name="weekday" id="weekday" required>
                        </div>
                        <button type="submit" name="update" class="btn btn-primary">Update Time Slot</button>
                    </form>
                </div>
            </div>
        </div>
    </div>'
?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var updateModal = document.getElementById('updateModal');
        updateModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var slotId = button.getAttribute('data-id');
            var slotTime = button.getAttribute('data-time');
            var weekday = button.getAttribute('data-weekday');

            var modalSlotId = updateModal.querySelector('#slot_id');
            var modalSlotTime = updateModal.querySelector('#slot_time');
            var modalWeekday = updateModal.querySelector('#weekday');

            modalSlotId.value = slotId;
            modalSlotTime.value = slotTime;
            modalWeekday.value = weekday;
        });
    </script>
</body>
</html>';
<?php
$conn->close();
?>
