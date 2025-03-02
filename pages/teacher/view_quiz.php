<?php
session_start();
require_once "../../config.php";

// Check if the teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../guest/login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Ensure a quiz id is provided
if (!isset($_GET['id'])) {
    header("Location: teacher_dashboard.php");
    exit();
}

$quiz_id = intval($_GET['id']);

// Fetch the quiz details, now including the 'subject' field
$sql = "SELECT * FROM quiz WHERE id = ? AND teacher_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $quiz_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Quiz not found or you do not have permission to view it.</div></div>";
    exit();
}

$quiz = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Quiz - <?= htmlspecialchars($quiz['title']) ?></title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/css/teacher_dashboard.css">
</head>
<body>
    <?php include '../../components/teacher_header.php'; ?>

    <div class="container mt-4">
        <h2><?= htmlspecialchars($quiz['title']) ?></h2>
        <p><strong>Subject:</strong> <?= htmlspecialchars($quiz['subject']) ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($quiz['department']) ?></p>
        <p><strong>Semester:</strong> <?= htmlspecialchars($quiz['semester'] ?: 'N/A') ?></p>
        <p><strong>Time Limit:</strong> <?= htmlspecialchars($quiz['time_limit']) ?> minutes</p>
        <p><strong>Status:</strong> <?= htmlspecialchars($quiz['status']) ?></p>
        <p><strong>Created At:</strong> <?= date("d M Y, h:i A", strtotime($quiz['created_at'])) ?></p>

        <!-- Additional details like quiz questions could be added here -->

        <a href="teacher_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <?php include '../../components/teacher_footer.php'; ?>
</body>
</html>
