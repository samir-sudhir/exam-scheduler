<?php
require '../config/database.php';
session_start();

// Handle form submission
if (isset($_GET['id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id'];
    $username = $_POST['username'];
    // Only hash the password if it's not empty
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $role = $_POST['role'];

    // Debugging: Check if the role is being passed correctly
    // var_dump($role); exit;  // Uncomment this line for debugging

    // Update both username, password (if provided), and role
    if ($password) {
        $query = "UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssi", $username, $password, $role, $id);
    } else {
        // Update only username and role (if password is not provided)
        $query = "UPDATE users SET username = ?, role = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssi", $username, $role, $id);
    }

    // Execute the query
    if ($stmt->execute()) {
        // Set success message in session
        $_SESSION['success_message'] = "User updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Fetch user data if ID is passed
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $user_result = $mysqli->query("SELECT * FROM users WHERE id = $id");
    $user = $user_result->fetch_assoc();
    // Check if user is found
    if (!$user) {
        echo "User not found.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2>Update User</h2>

        <!-- Display Success Message if Available -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); // Clear the message after displaying 
            ?>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password (Leave blank to keep unchanged)</label>
                <input type="password" name="password" id="password" class="form-control">
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-control">
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="coordinator" <?= $user['role'] === 'coordinator' ? 'selected' : '' ?>>Exam Coordinator</option>
                    <option value="superintendent" <?= $user['role'] === 'superintendent' ? 'selected' : '' ?>>Superintendent</option>
                </select>

            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="../admin/index.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</body>

</html>