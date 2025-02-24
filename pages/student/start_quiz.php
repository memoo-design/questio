<?php
session_start();
require '../../config.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student' || !isset($_GET['quiz_id'])) {
    header("Location: student_dashboard.php");
    exit();
}

$quiz_id = (int)$_GET['quiz_id'];

// Fetch quiz details
$stmt = $mysqli->prepare("SELECT title, time_limit FROM quiz WHERE id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$quiz = $result->fetch_assoc();

if (!$quiz) {
    echo "Quiz not found.";
    exit();
}

// Fetch questions
$stmt = $mysqli->prepare("SELECT id, question_text FROM question WHERE quiz_id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        let timeLeft = <?= $quiz['time_limit'] * 60 ?>; // Convert minutes to seconds

        function startTimer() {
            const timerDisplay = document.getElementById('timer');
            const interval = setInterval(() => {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                timerDisplay.textContent = `Time Left: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                if (timeLeft <= 0) {
                    clearInterval(interval);
                    document.getElementById('quizForm').submit(); // Auto-submit
                }
                timeLeft--;
            }, 1000);
        }

        window.onload = startTimer;
    </script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center"><?= htmlspecialchars($quiz['title']) ?></h1>
        <h3 id="timer" class="text-danger text-center"></h3>

        <form id="quizForm" method="POST" action="submit_quiz.php">
            <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
            <?php while ($question = $questions->fetch_assoc()): ?>
                <div class="mb-3">
                    <p><strong><?= htmlspecialchars($question['question_text']) ?></strong></p>
                    <?php
                    $stmt = $mysqli->prepare("SELECT id, option_text FROM options WHERE question_id = ?");
                    $stmt->bind_param("i", $question['id']);
                    $stmt->execute();
                    $options = $stmt->get_result();
                    ?>
                    <?php while ($option = $options->fetch_assoc()): ?>
                        <div class="form-check">
                            <input type="radio" name="answer[<?= $question['id'] ?>]" value="<?= $option['id'] ?>" required>
                            <label><?= htmlspecialchars($option['option_text']) ?></label>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endwhile; ?>
            <button type="submit" class="btn btn-success">Submit</button>
        </form>
    </div>
</body>
</html>
