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
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file -->
</head>
<body>
    <div class="container">
        <h2>Quiz Submission Successful!</h2>
        <p><?php echo htmlspecialchars($result_message); ?></p>
        <a href="student_dashboard.php" class="btn">Go Back to Dashboard</a>
    </div>
</body>
</html>
