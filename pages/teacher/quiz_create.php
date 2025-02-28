<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../guest/login.php");
    exit();
}

require '../../config.php'; // Ensure database connection works

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (empty($_POST['quiz_title']) || empty($_POST['department']) || empty($_POST['semester']) || empty($_POST['time_limit']) || !isset($_POST['question'])) {
        echo "<script>alert('Please fill all fields correctly.');</script>";
    } else {
        $quiz_title = trim($_POST['quiz_title']);
        $department = $_POST['department'];
        $semester = $_POST['semester'];
        $time_limit = (int)$_POST['time_limit'];
        $teacher_id = $_SESSION['user_id'];

        // Determine quiz status (published or draft)
        $status = isset($_POST['publish_quiz']) ? 'published' : 'draft';

        // Insert quiz into database
        $stmt = $mysqli->prepare("INSERT INTO quiz (title, department, semester, time_limit, teacher_id, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $quiz_title, $department, $semester, $time_limit, $teacher_id, $status);

        if ($stmt->execute()) {
            $quiz_id = $stmt->insert_id;

            // Insert questions and options (even =if it's a draft)
            foreach ($_POST['question'] as $index => $question_text) {
                $question_text = trim($question_text);

                $stmt = $mysqli->prepare("INSERT INTO question (quiz_id, question_text) VALUES (?, ?)");
                $stmt->bind_param("is", $quiz_id, $question_text);
                if ($stmt->execute()) {
                    $question_id = $stmt->insert_id;

                    foreach ($_POST['option'][$index] as $opt_index => $option_text) {
                        $option_text = trim($option_text);
                        $is_correct = ($_POST['correct_option'][$index] == $opt_index) ? 1 : 0;

                        $stmt = $mysqli->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                        $stmt->bind_param("isi", $question_id, $option_text, $is_correct);
                        $stmt->execute();
                    }
                }
            }

            echo "<script>alert('Quiz " . ($status === 'published' ? "Published" : "Saved as Draft") . " Successfully!'); window.location='teacher_dashboard.php';</script>";
        } else {
            echo "<script>alert('Error Creating Quiz');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include '../../components/teacher_header.php'; ?>

<div class="container mt-5">
    <h1 class="text-center">Create a New Quiz</h1>

    <div class="card shadow p-4 mx-auto" style="max-width: 600px;">
        <form method="POST" action="quiz_create.php">
            <div class="mb-3">
                <label class="form-label">Quiz Title:</label>
                <input type="text" name="quiz_title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Department:</label>
                <select name="department" class="form-select" required>
                    <option value="Software Engineering">Software Engineering</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Information Technology">Information Technology</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Semester:</label>
                <select name="semester" class="form-select" required>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                    <option value="3rd Semester">3rd Semester</option>
                    <option value="4th Semester">4th Semester</option>
                    <option value="5th Semester">5th Semester</option>
                    <option value="6th Semester">6th Semester</option>
                    <option value="7th Semester">7th Semester</option>
                    <option value="8th Semester">8th Semester</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Time Limit (minutes):</label>
                <input type="number" name="time_limit" class="form-control" min="1" required>
            </div>

            <div id="questions">
                <div class="question mb-3 p-3 border rounded position-relative">
                    <label class="form-label">Question:</label>
                    <input type="text" name="question[]" class="form-control mb-2" required>
                    <div class="options">
                        <label class="form-label">Options:</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text">A</span>
                            <input type="text" name="option[0][]" class="form-control" required>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text">B</span>
                            <input type="text" name="option[0][]" class="form-control" required>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text">C</span>
                            <input type="text" name="option[0][]" class="form-control" required>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text">D</span>
                            <input type="text" name="option[0][]" class="form-control" required>
                        </div>
                    </div>
                    <label class="form-label">Correct Answer:</label>
                    <select name="correct_option[0]" class="form-select">
                        <option value="0">A</option>
                        <option value="1">B</option>
                        <option value="2">C</option>
                        <option value="3">D</option>
                    </select>
                    <button type="button" class="btn btn-danger btn-sm mt-2 position-absolute top-0 end-0" onclick="deleteQuestion(this)">✖</button>
                </div>
            </div>

            <button type="button" class="btn btn-secondary mt-3" onclick="addQuestion()">Add Question</button>
            <button type="submit" name="save_as_draft" class="btn btn-warning mt-3">Save as Draft</button>
            <button type="submit" name="publish_quiz" class="btn btn-primary mt-3">Publish Quiz</button>
        </form>
    </div>
</div>

<script>
let questionIndex = 1;

function addQuestion() {
    let questionsDiv = document.getElementById("questions");

    let newQuestion = document.createElement("div");
    newQuestion.classList.add("question", "mb-3", "p-3", "border", "rounded", "position-relative");

    newQuestion.innerHTML = `
        <label class="form-label">Question:</label>
        <input type="text" name="question[]" class="form-control mb-2" required>

        <div class="options">
            <label class="form-label">Options:</label>
            <div class="input-group mb-2">
                <span class="input-group-text">A</span>
                <input type="text" name="option[${questionIndex}][]" class="form-control" required>
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text">B</span>
                <input type="text" name="option[${questionIndex}][]" class="form-control" required>
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text">C</span>
                <input type="text" name="option[${questionIndex}][]" class="form-control" required>
            </div>
            <div class="input-group mb-2">
                <span class="input-group-text">D</span>
                <input type="text" name="option[${questionIndex}][]" class="form-control" required>
            </div>
        </div>

        <label class="form-label">Correct Answer:</label>
        <select name="correct_option[${questionIndex}]" class="form-select">
            <option value="0">A</option>
            <option value="1">B</option>
            <option value="2">C</option>
            <option value="3">D</option>
        </select>

        <button type="button" class="btn btn-danger btn-sm mt-2 position-absolute top-0 end-0" onclick="deleteQuestion(this)">✖</button>
    `;

    questionsDiv.appendChild(newQuestion);
    questionIndex++; // Increment index to maintain unique array keys
}

function deleteQuestion(button) {
    button.parentElement.remove();
}


function deleteQuestion(button) {
    button.parentElement.remove();
}
</script>

<?php include '../../components/teacher_footer.php'; ?>
</body>
</html>
