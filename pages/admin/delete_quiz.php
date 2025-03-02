<?php
session_start();
require_once "../../config.php";

// Ensure only admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../guest/login.php");
    exit();
}
if (isset($_GET['id'])) {
    $quiz_id = $_GET['id'];

    // Check if the quiz belongs to the teacher
    $sql = "DELETE FROM quiz WHERE id = ? ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $quiz_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Quiz deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete quiz!";
    }

    $stmt->close();
    $mysqli->close();

    header("Location: admin_dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid request!";
    header("Location: admin_dashboard.php");
    exit();
}