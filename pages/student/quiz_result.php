<?php
session_start();

// Redirect if no result is set
if (!isset($_SESSION['quiz_result'])) {
    header("Location: student_dashboard.php");
    exit();
}
// Retrieve and clear the result message
$result_message = $_SESSION['quiz_result'];
unset($_SESSION['quiz_result']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../public/css/quiz_result.css" rel="stylesheet">

  
</head>
<body>

    <div class="container">
        <h2>Quiz Submission Successful!</h2>
        <p class="result-message"><?php echo htmlspecialchars($result_message); ?></p>
        <a href="student_dashboard.php" class="btn">Go Back to Dashboard</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>


