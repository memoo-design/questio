<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = $_POST['assignment_id'];

    $query = "DELETE FROM teacher_subjects WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $assignment_id);

    if ($stmt->execute()) {
        // Redirect back with success message
        header("Location: assign_subject.php?success=Subject removed successfully!");
        exit();
    } else {
        // Redirect back with error message
        header("Location: assign_subject.php?error=Error removing subject.");
        exit();
    }
}
?>