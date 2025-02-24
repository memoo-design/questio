<?php
session_start();
require_once "../../config.php";

// Ensure only admins can perform deletions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../guest/login.php");
    exit();
}

// Check if user ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Prevent deleting admin users
    $checkRoleQuery = "SELECT role FROM user WHERE id = ?";
    $stmt = $mysqli->prepare($checkRoleQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['role'] === 'admin') {
            $_SESSION['error'] = "You cannot delete an admin user.";
            header("Location: admin_dashboard.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "User not found.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Delete from related tables first
    $mysqli->query("DELETE FROM student_info WHERE user_id = $user_id");
    $mysqli->query("DELETE FROM teacher_info WHERE user_id = $user_id");
    $mysqli->query("DELETE FROM student_attempts WHERE student_id = $user_id");
    $mysqli->query("DELETE FROM quiz WHERE teacher_id = $user_id");
    $mysqli->query("DELETE FROM login_logs WHERE user_id = $user_id");

    // Delete the user
    $deleteQuery = "DELETE FROM user WHERE id = ?";
    $stmt = $mysqli->prepare($deleteQuery);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user.";
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid user ID.";
}

$mysqli->close();
header("Location: admin_dashboard.php");
exit();
?>
