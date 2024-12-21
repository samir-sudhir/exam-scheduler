<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superintendent</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
        <a class="navbar-brand" href="/e_v3">Exam Scheduler </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav" style="justify-content: flex-end;">
        <ul class="navbar-nav">
            <!--  -->
            <li class="nav-item">
                <a class="nav-link" href="../../e_v/exam_cordinate/views/exam_halls/index.php">Exam Halls Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../../e_v/sign_out.php">Sign Out</a>
            </li>
        </ul>
        </div>
        </div>
    </nav>
    <div class="col-md-12">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-setting card-icon"></i>
                <!-- <a href="../e_v3/views/schedule/process_exam_schedule.php">
                    <h5 class="card-title mt-3">Click to generate the Exam Schedule</h5>
                </a> -->
                <a href="../../e_v/exam_cordinate/views/schedule/process_exam_schedule.php"><h5 class="card-title mt-3">Click to generate the Exam Schedule</h5></a>
                <!-- <a href="http://localhost/e_v3/views/schedule/process_exam_schedule.php"><h5 class="card-title mt-3">Click to generate the Exam Schedule</h5></a> -->
            </div>
        </div>
    </div>
</body>

</html>