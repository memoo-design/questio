<?php
session_start();
require '../../config.php';

// Check if student is logged in and request method is POST
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: student_dashboard.php");
    exit();
}

// Validate inputs
if (!isset($_POST['quiz_id'], $_POST['answer']) || !is_array($_POST['answer'])) {
    die("Invalid request.");
}

$quiz_id = (int)$_POST['quiz_id'];
$student_id = $_SESSION['user_id'];
$score = 0;
$total_questions = count($_POST['answer']);

// Check if student has already attempted this quiz
$check_stmt = $mysqli->prepare("SELECT id FROM student_attempts WHERE student_id = ? AND quiz_id = ?");
$check_stmt->bind_param("ii", $student_id, $quiz_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    $check_stmt->close();
    die("<script>alert('You have already attempted this quiz.'); window.location='student_dashboard.php';</script>");
}
$check_stmt->close();

// Process answers
foreach ($_POST['answer'] as $question_id => $selected_option) {
    $stmt = $mysqli->prepare("SELECT is_correct FROM options WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $selected_option);
        $stmt->execute();
        $stmt->bind_result($is_correct);
        $stmt->fetch();
        $stmt->close();

        if (!empty($is_correct)) {
            $score++;
        }
    }
}

// Store attempt
$stmt = $mysqli->prepare("INSERT INTO student_attempts (student_id, quiz_id, score, total_questions) VALUES (?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("iiii", $student_id, $quiz_id, $score, $total_questions);
    $stmt->execute();
    $stmt->close();
}

// Redirect to results page
$_SESSION['quiz_result'] = "Quiz Submitted! Your Score: $score / $total_questions";
header("Location: quiz_result.php");
exit();
?>
