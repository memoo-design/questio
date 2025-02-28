<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../guest/login.php");
    exit();
}

require '../../config.php';

$quiz_id = $_GET['id'] ?? null;
$quiz = [];
$questions = [];

if ($quiz_id) {
    // Fetch quiz details
    $result = $mysqli->query("SELECT title, time_limit, status FROM quiz WHERE id = $quiz_id");
    $quiz = $result->fetch_assoc();

    if (!$quiz) die("Quiz not found!");

    // Fetch questions and options
    $sql = "SELECT q.id as question_id, q.question_text, o.id as option_id, o.option_text, o.is_correct
            FROM question q
            LEFT JOIN options o ON q.id = o.question_id
            WHERE q.quiz_id = $quiz_id
            ORDER BY q.id ASC, o.id ASC";
    
    $result = $mysqli->query($sql);
    while ($row = $result->fetch_assoc()) {
        $q_id = $row['question_id'];
        if (!isset($questions[$q_id])) {
            $questions[$q_id] = ['question_id' => $q_id, 'question_text' => $row['question_text'], 'options' => []];
        }
        if ($row['option_id']) {
            $questions[$q_id]['options'][] = [
                'option_id' => $row['option_id'],
                'option_text' => $row['option_text'],
                'is_correct' => $row['is_correct']
            ];
        }
    }
}

// Handle deletion
if (isset($_GET['delete_question'])) {
    $q_id = intval($_GET['delete_question']);
    $mysqli->query("DELETE FROM options WHERE question_id = $q_id");
    $mysqli->query("DELETE FROM question WHERE id = $q_id");
    header("Location: edit_quiz.php?id=$quiz_id");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_title = $_POST['quiz_title'];
    $time_limit = $_POST['time_limit'];
    $status = $_POST['action'];

    $mysqli->query("UPDATE quiz SET title = '$quiz_title', time_limit = '$time_limit', status = '$status' WHERE id = $quiz_id");

    foreach ($_POST['question'] as $q_id => $question_text) {
        if (strpos($q_id, 'new_') === 0) {
            $mysqli->query("INSERT INTO question (quiz_id, question_text) VALUES ($quiz_id, '$question_text')");
            $q_id = $mysqli->insert_id;
        } else {
            $mysqli->query("UPDATE question SET question_text = '$question_text' WHERE id = $q_id");
        }

        foreach ($_POST['option'][$q_id] as $opt_id => $opt_text) {
            $is_correct = ($_POST['correct_option'][$q_id] == $opt_id) ? 1 : 0;
            if (strpos($opt_id, 'new_') === 0) {
                $mysqli->query("INSERT INTO options (question_id, option_text, is_correct) VALUES ($q_id, '$opt_text', $is_correct)");
            } else {
                $mysqli->query("UPDATE options SET option_text = '$opt_text', is_correct = $is_correct WHERE id = $opt_id");
            }
        }
    }

    header("Location: teacher_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function addQuestion() {
            const questionContainer = document.getElementById('questions');
            const questionId = `new_${Date.now()}`;
            let questionHtml = `
                <div class='mb-3 p-3 border rounded' id='${questionId}'>
                    <label>Question:</label>
                    <input type='text' name='question[${questionId}]' class='form-control mb-2' required>
                    
                    <label>Options:</label>
                    <div id='options_${questionId}'>
                        ${generateOptionHtml(questionId, 1)}
                        ${generateOptionHtml(questionId, 2)}
                        ${generateOptionHtml(questionId, 3)}
                        ${generateOptionHtml(questionId, 4)}
                    </div>

                    <label>Correct Answer:</label>
                    <select name='correct_option[${questionId}]' class='form-select'>
                        <option value='1'>Option 1</option>
                        <option value='2'>Option 2</option>
                        <option value='3'>Option 3</option>
                        <option value='4'>Option 4</option>
                    </select>
                    
                    <button type='button' class='btn btn-danger mt-2' onclick='removeQuestion("${questionId}")'>Delete</button>
                </div>`;
            questionContainer.insertAdjacentHTML('beforeend', questionHtml);
        }

        function generateOptionHtml(qId, optNum) {
            return `<div class='input-group mb-2'>
                <input type='text' name='option[${qId}][new_${optNum}]' class='form-control' required>
            </div>`;
        }

        function removeQuestion(questionId) {
            document.getElementById(questionId).remove();
        }
    </script>
</head>
<body class="bg-light">
<?php include '../../components/teacher_header.php'; ?>

<div class="container mt-5">
    <h1 class="text-center">Edit Quiz</h1>
    <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
        <form method="POST">
            <div class="mb-3">
                <label>Quiz Title:</label>
                <input type="text" name="quiz_title" class="form-control" value="<?= htmlspecialchars($quiz['title'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label>Time Limit (minutes):</label>
                <input type="number" name="time_limit" class="form-control" min="1" value="<?= htmlspecialchars($quiz['time_limit'] ?? '') ?>" required>
            </div>

            <div id="questions">
                <?php foreach ($questions as $q_id => $question): ?>
                    <div class="mb-3 p-3 border rounded">
                        <label>Question:</label>
                        <input type="text" name="question[<?= $q_id ?>]" class="form-control mb-2" value="<?= htmlspecialchars($question['question_text']) ?>" required>

                        <label>Options:</label>
                        <?php foreach ($question['options'] as $option): ?>
                            <div class="input-group mb-2">
                                <input type="text" name="option[<?= $q_id ?>][<?= $option['option_id'] ?>]" class="form-control" value="<?= htmlspecialchars($option['option_text']) ?>" required>
                            </div>
                        <?php endforeach; ?>

                        <label>Correct Answer:</label>
                        <select name="correct_option[<?= $q_id ?>]" class="form-select">
                            <?php foreach ($question['options'] as $option): ?>
                                <option value="<?= $option['option_id'] ?>" <?= $option['is_correct'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($option['option_text']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <a href="edit_quiz.php?id=<?= $quiz_id ?>&delete_question=<?= $q_id ?>" class="btn btn-danger mt-2">Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="btn btn-success mt-3" onclick="addQuestion()">Add Question</button>
            <button type="submit" name="action" value="draft" class="btn btn-warning mt-3">Save Draft</button>
            <button type="submit" name="action" value="published" class="btn btn-primary mt-3">Publish</button>
        </form>
    </div>
</div>

<?php include '../../components/teacher_footer.php'; ?>
</body>
</html>
