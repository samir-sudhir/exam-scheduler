<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Scheduling Form</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-D7tnIwpZMi0+zY16yU6hSh6g9n+Xw04djEuCZVm2MY9YYX1u2XXg3sR7VhQsZmje" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Exam Scheduling Form</h2>
        <form action="process_exam_schedule.php" method="POST">
            <div class="row mb-3">
                <label for="time_per_slot" class="col-md-4 col-form-label">Time Per Slot (Minutes)</label>
                <div class="col-md-8">
                    <input type="number" id="time_per_slot" name="time_per_slot" class="form-control" required min="30" max="180">
                </div>
            </div>

            <div class="row mb-3">
                <label for="slots_per_day" class="col-md-4 col-form-label">Slots Per Day</label>
                <div class="col-md-8">
                    <input type="number" id="slots_per_day" name="slots_per_day" class="form-control" min="1" max="10">
                </div>
            </div>

            <div class="row mb-3">
                <label for="break_time" class="col-md-4 col-form-label">Break Time Between Slots (Minutes)</label>
                <div class="col-md-8">
                    <input type="number" id="break_time" name="break_time" class="form-control" required min="0" max="60">
                </div>
            </div>

            <div class="row mb-3">
                <label for="start_time" class="col-md-4 col-form-label">Start Time for First Slot</label>
                <div class="col-md-8">
                    <input type="time" id="start_time" name="start_time" class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="min_days" class="col-md-4 col-form-label">Minimum Days Between Exams</label>
                <div class="col-md-8">
                    <input type="number" id="min_days" name="min_days" class="form-control" required min="1" max="7">
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">Generate Exam Schedule</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap 5 JS (Optional for validation or interactivity) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-XA1mZjZnqTpLJ1wCU1FhFmjtmqfwGQF+5FO+AyNOzBs1cQYtOxDcx0iwvPq5Yskj" crossorigin="anonymous"></script>
</body>
</html>
