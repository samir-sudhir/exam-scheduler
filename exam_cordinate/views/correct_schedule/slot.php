<!-- Step 1: Define the Exam Slots in HTML (Bootstrap)
Objective: Create a table to visually represent the exam slots for each day of the week.

Steps:

Use Bootstrap's grid system for creating a responsive table layout.
Define the days (Monday to the next week) as headers.
Define slots as rows under each day. -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Scheduling Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    

    <div class="container mt-5">
        <h2 class="text-center">Exam Scheduling Form</h2>
        <form action="" method="POST">

            <div class="row mb-3">
                <label for="slots_per_day" class="col-md-4 col-form-label">Slots Per Day(1-5)</label>
                <div class="col-md-8">
                    <input type="number" id="slots_per_day" name="slots_per_day" class="form-control"  min="1" max="5"  required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="min_days" class="col-md-4 col-form-label">Minimum Days Between Exams (1-30):</label>
                <div class="col-md-8">
                    <input type="number" id="min_days" name="min_days" class="form-control" required min="1" max="30">
                </div>
            </div>

            <div class="text-center">
                <button type="submit" name="generate_schedule" class="btn btn-primary">Generate Exam Schedule</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>