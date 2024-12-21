<?php
include_once('../e_v/config/database.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    // Get form data
    $userName = $_POST['userName'];
    $password = $_POST['password'];

    // Validate user credentials
    $query = "SELECT id, username, password, role, approved FROM users WHERE username = ?";
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        die('Query preparation failed: ' . $mysqli->error); // Updated to use `$mysqli`
    }

    $stmt->bind_param("s", $userName); // Use $userName instead of $username
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password and check approval status
        if (password_verify($password, $user['password'])) {
            if ($user['approved'] == 1) {
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../e_v/admin/index.php");
                    exit();
                } elseif ($user['role'] === 'superintendent') {
                    header("Location: ../e_v/Supertendent/index.php");
                    exit();
                } elseif ($user['role'] === 'coordinator') {
                    header("Location: ../e_v/exam_cordinate/");
                    exit();
                }
            } else {
                $error = "Your account is not approved yet.";
            }
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<a href=""></a>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <?php
    // include_once('../e_v/header/header.php');
    ?>

    <div class="container border-1 mt-5">
        <div class="card">
            <div class="card-header bg-success">
                <h2 class="text-light">Sign In Form</h2>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="" method="post">
                    <!-- Full Name and Username -->
                    <div class="row  mb-3">
                        <div class="form-group">
                            <label for="userName" class="form-label">User Name</label>
                            <input type="text" class="form-control" name="userName" required>
                        </div>
                    </div>

                    <!-- Password and Confirm Password -->
                    <div class="row  mb-3">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group mb-3">
                        <button type="submit" name='role' class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
            <p class="ms-3">If you do not have an account? <a href="../e_v/index.php">Sign Up</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>