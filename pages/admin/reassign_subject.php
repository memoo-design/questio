<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = $_POST['assignment_id'];
    $new_subject_id = $_POST['new_subject_id'];

    $query = "UPDATE teacher_subjects SET subject_id = ? WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $new_subject_id, $assignment_id);

    if ($stmt->execute()) {
        echo "Subject reassigned successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>