<?php
session_start();
require_once "../../config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../guest/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $quiz_id = $_GET['id'];
    $teacher_id = $_SESSION['user_id'];

    // Check if the quiz belongs to the teacher
    $sql = "DELETE FROM quiz WHERE id = ? AND teacher_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $quiz_id, $teacher_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Quiz deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete quiz!";
    }

    $stmt->close();
    $mysqli->close();

    header("Location: teacher_dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request!";
    header("Location: teacher_dashboard.php");
    exit();
}
