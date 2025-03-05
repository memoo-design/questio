<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignment_id = $_POST['assignment_id'];
    $new_subject_id = $_POST['new_subject_id'];

    $query = "UPDATE teacher_subjects SET subject_id = ? WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $new_subject_id, $assignment_id);

    if ($stmt->execute()) {
        echo "<script>alert('Subject reassigned successfully!'); window.location.href=document.referrer;</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.location.href=document.referrer;</script>";
    }
}
?>