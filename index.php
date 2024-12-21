<?php
include_once('../e_v/config/database.php');
session_start();

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    // Get form data
    $userName = $_POST['userName'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $actor = $_POST['actor'];

    if ($password === $confirmPassword) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the user into the database
        $query = "INSERT INTO users (username, password, role) VALUES ('$userName', '$hashedPassword', '$actor')";
        if ($mysqli->query($query)) {
            echo "<div class='alert alert-success'>User registered successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $mysqli->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Passwords do not match.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <?php
    // include_once('../e_v/header/header.php');
    ?>

    <div class="container border-1 mt-5">
        <div class="card">
            <div class="card-header bg-success">
                <h2 class="text-light">Sign Up Form</h2>
            </div>
            <div class="card-body">
                <form action="" method="post">
                    <!-- Full Name and Username -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="fullName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="userName" class="form-label">User Name</label>
                            <input type="text" class="form-control" name="userName" required>
                        </div>
                    </div>

                    <!-- Email and Phone Number -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phoneNumber" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phoneNumber" pattern="[0-9]{10,12}" title="Enter a valid phone number (10-12 digits)" required>
                        </div>
                    </div>

                    <!-- Password and Confirm Password -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirmPassword" required>
                        </div>
                    </div>

                    <!-- Profile Selection -->
                    <div class="mb-3">
                        <label for="actor" class="form-label">Choose Your Profile</label>
                        <select name="actor" class="form-control" required>
                            <option value="" selected disabled>---Select Here---</option>
                            <option value="admin">Admin</option>
                            <option value="superintendent">Superintendent</option>
                            <option value="Exam Coordinator">Exam Coordinator</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name='role' class="btn btn-primary">Submit</button>
                </form>
            </div>
            <p class="ms-3">If you already have an account? <a href="../e_v/sign_in.php">Sign in</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>