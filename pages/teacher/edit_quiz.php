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
    $stmt = $mysqli->prepare("SELECT title, time_limit, status FROM quiz WHERE id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quiz = $result->fetch_assoc();
    $stmt->close();

    if (!$quiz) {
        die("Quiz not found!");
    }

    // Fetch questions and options
    $sql = "SELECT q.id as question_id, q.question_text, o.id as option_id, o.option_text, o.is_correct
            FROM question q
            LEFT JOIN options o ON q.id = o.question_id
            WHERE q.quiz_id = ?
            ORDER BY q.id ASC, o.id ASC";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $q_id = $row['question_id'];
        if (!isset($questions[$q_id])) {
            $questions[$q_id] = [
                'question_id' => $q_id,
                'question_text' => $row['question_text'],
                'options' => []
            ];
        }
        if ($row['option_id']) {
            $questions[$q_id]['options'][] = [
                'option_id' => $row['option_id'],
                'option_text' => $row['option_text'],
                'is_correct' => $row['is_correct']
            ];
        }
    }
    $stmt->close();
}
// Handle question deletion
if (isset($_GET['delete_question'])) {
    $q_id = intval($_GET['delete_question']);

    $stmt = $mysqli->prepare("DELETE FROM options WHERE question_id = ?");
    $stmt->bind_param("i", $q_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("DELETE FROM question WHERE id = ?");
    $stmt->bind_param("i", $q_id);
    $stmt->execute();
    $stmt->close();

    header("Location: edit_quiz.php?id=$quiz_id");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_title = $_POST['quiz_title'] ?? '';
    $time_limit = $_POST['time_limit'] ?? 10;
    $action = $_POST['action']; // 'draft' or 'publish'

    // Update quiz details with correct status
    $stmt = $mysqli->prepare("UPDATE quiz SET title = ?, time_limit = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sisi", $quiz_title, $time_limit, $action, $quiz_id);
    $stmt->execute();
    $stmt->close();

    // Update existing questions and options
    foreach ($_POST['question'] as $q_id => $question_text) {
        if (strpos($q_id, 'new_') === 0) {
            // Insert new question
            $stmt = $mysqli->prepare("INSERT INTO question (quiz_id, question_text) VALUES (?, ?)");
            $stmt->bind_param("is", $quiz_id, $question_text);
            $stmt->execute();
            $q_id = $stmt->insert_id; // Get new question ID
            $stmt->close();
        } else {
            // Update existing question
            $stmt = $mysqli->prepare("UPDATE question SET question_text = ? WHERE id = ?");
            $stmt->bind_param("si", $question_text, $q_id);
            $stmt->execute();
            $stmt->close();
        }

        // Insert or update options
        foreach ($_POST['option'][$q_id] as $opt_id => $opt_text) {
            if (strpos($opt_id, 'new_') === 0) {
                $stmt = $mysqli->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                $is_correct = ($_POST['correct_option'][$q_id] == $opt_id) ? 1 : 0;
                $stmt->bind_param("isi", $q_id, $opt_text, $is_correct);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $mysqli->prepare("UPDATE options SET option_text = ?, is_correct = ? WHERE id = ?");
                $is_correct = ($_POST['correct_option'][$q_id] == $opt_id) ? 1 : 0;
                $stmt->bind_param("sii", $opt_text, $is_correct, $opt_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    header("Location: edit_quiz.php?id=$quiz_id");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function addQuestion() {
            const questionContainer = document.getElementById('questions');
            const questionId = `new_${Date.now()}`;

            let questionHtml = `<div class='question mb-3 p-3 border rounded'>
                <label class='form-label'>Question:</label>
                <input type='text' name='question[${questionId}]' class='form-control mb-2' required>
                
                <label class='form-label'>Options:</label>
                <div id='options_${questionId}'>
                    ${generateOptionHtml(questionId, 1)}
                    ${generateOptionHtml(questionId, 2)}
                    ${generateOptionHtml(questionId, 3)}
                    ${generateOptionHtml(questionId, 4)}
                </div>
                
                <label class='form-label'>Correct Answer:</label>
                <select name='correct_option[${questionId}]' class='form-select' id='correct_option_${questionId}'>
                    <option value='1'>Option 1</option>
                    <option value='2'>Option 2</option>
                    <option value='3'>Option 3</option>
                    <option value='4'>Option 4</option>
                </select>
            </div>`;
            
            questionContainer.insertAdjacentHTML('beforeend', questionHtml);
        }

        function addQuestion() {
    const questionContainer = document.getElementById('questions');
    const questionId = `new_${Date.now()}`;

    let questionHtml = `<div class='question mb-3 p-3 border rounded' id='${questionId}'>
        <label class='form-label'>Question:</label>
        <input type='text' name='question[${questionId}]' class='form-control mb-2' required>
        
        <label class='form-label'>Options:</label>
        <div id='options_${questionId}'>
            ${generateOptionHtml(questionId, 1)}
            ${generateOptionHtml(questionId, 2)}
            ${generateOptionHtml(questionId, 3)}
            ${generateOptionHtml(questionId, 4)}
        </div>
        
        <label class='form-label'>Correct Answer:</label>
        <select name='correct_option[${questionId}]' class='form-select' id='correct_option_${questionId}'>
            <option value='1'>Option 1</option>
            <option value='2'>Option 2</option>
            <option value='3'>Option 3</option>
            <option value='4'>Option 4</option>
        </select>

        <button type='button' class='btn btn-danger mt-2' onclick='removeQuestion("${questionId}")'>Delete Question</button>
    </div>`;
    
    questionContainer.insertAdjacentHTML('beforeend', questionHtml);
}

function generateOptionHtml(qId, optNum) {
    return `<div class='input-group mb-2'>
        <input type='text' name='option[${qId}][new_${Date.now()}]' class='form-control' required>
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
                <label class="form-label">Quiz Title:</label>
                <input type="text" name="quiz_title" class="form-control" value="<?= htmlspecialchars($quiz['title'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Time Limit (minutes):</label>
                <input type="number" name="time_limit" class="form-control" min="1" value="<?= htmlspecialchars($quiz['time_limit'] ?? '') ?>" required>
            </div>

            <div id="questions">
                <?php foreach ($questions as $q_id => $question): ?>
                    <div class="question mb-3 p-3 border rounded">
                        <label class="form-label">Question:</label>
                        <input type="text" name="question[<?= $q_id ?>]" class="form-control mb-2" value="<?= htmlspecialchars($question['question_text']) ?>" required>

                        <label class="form-label">Options:</label>
                        <?php foreach ($question['options'] as $option): ?>
                            <div class="input-group mb-2">
                                <input type="text" name="option[<?= $q_id ?>][<?= $option['option_id'] ?>]" class="form-control" value="<?= htmlspecialchars($option['option_text']) ?>" required>
                            </div>
                        <?php endforeach; ?>

                        <label class="form-label">Correct Answer:</label>
                        <select name="correct_option[<?= $q_id ?>]" class="form-select">
                            <?php foreach ($question['options'] as $option): ?>
                                <option value="<?= $option['option_id'] ?>" <?= $option['is_correct'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($option['option_text']) ?>
                                </option>
                                
                            <?php endforeach; ?>
                        </select>
                        <a href="edit_quiz.php?id=<?= $quiz_id ?>&delete_question=<?= $q_id ?>" class="btn btn-danger mt-2">Delete Question</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="btn btn-success mt-3" onclick="addQuestion()">Add Question</button>
            <button type="submit" name="action" value="draft" class="btn btn-warning mt-3">Save as Draft</button>
            <button type="submit" name="action" value="publish" class="btn btn-primary mt-3">Update & Publish</button>
        </form>
    </div>
</div>

<?php include '../../components/teacher_footer.php'; ?>
</body>
</html>
