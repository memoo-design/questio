<?php
session_start();
require '../../config.php';

if (!isset($_SESSION['roll_no'])) {
    header("Location: ../guest/login.php");
    exit();
}

// Ensure database connection is established
if (!$mysqli) {
    die("Database connection failed: " . mysqli_connect_error());
}

$roll_no = $_SESSION['roll_no'];

// Fetch student ID, department, and semester in **one query**
$sql_student = "SELECT u.id AS student_id, s.department, s.semester 
                FROM user u
                JOIN student_info s ON u.id = s.user_id
                WHERE s.roll_no = ? AND u.role = 'student'";

$stmt_student = $mysqli->prepare($sql_student);
$stmt_student->bind_param("s", $roll_no);
$stmt_student->execute();
$result_student = $stmt_student->get_result();

if ($result_student->num_rows === 0) {
    session_destroy();
    header("Location: ../guest/login.php");
    exit();
}

$student_info = $result_student->fetch_assoc();
$student_id = $student_info['student_id'];
$department = $student_info['department'];
$semester = $student_info['semester'];
$stmt_student->close();

// Fetch pending quizzes
$sql_pending = "SELECT id, title, time_limit 
                FROM quiz 
                WHERE department = ? 
                AND semester = ? 
                AND status = 'published' 
                AND id NOT IN (SELECT quiz_id FROM student_attempts WHERE student_id = ?)";

$stmt_pending = $mysqli->prepare($sql_pending);
$stmt_pending->bind_param("ssi", $department, $semester, $student_id);
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();
$stmt_pending->close();

// Fetch submitted quizzes
$sql_submitted = "SELECT q.title, q.subject, sa.score, sa.total_questions, sa.grade 
                  FROM student_attempts sa
                  JOIN quiz q ON sa.quiz_id = q.id 
                  WHERE sa.student_id = ?";

$stmt_submitted = $mysqli->prepare($sql_submitted);
$stmt_submitted->bind_param("i", $student_id);
$stmt_submitted->execute();
$result_submitted = $stmt_submitted->get_result();
$stmt_submitted->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="/questio-git/public/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: white; }
    </style>
</head>

<body>
    <?php require_once "../../components/student_header.php"; ?>

    <div class="container mt-5">
        <h1 class="text-center">Pending Quizzes</h1>

        <div class="table-responsive">
            <?php if ($result_pending->num_rows > 0): ?>
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Quiz Title</th>
                            <th>Time Limit (min)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($quiz = $result_pending->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($quiz['title']) ?></td>
                                <td><?= htmlspecialchars($quiz['time_limit']) ?> min</td>
                                <td>
                                    <a href="start_quiz.php?quiz_id=<?= (int)$quiz['id'] ?>" class="btn btn-primary btn-sm">Start Quiz</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-muted">No pending quizzes available.</p>
            <?php endif; ?>
        </div>

        <h1 class="text-center mt-5">Submitted Quizzes</h1>

        <div class="table-responsive">
            <?php if ($result_submitted->num_rows > 0): ?>
                <table class="table table-bordered table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Quiz Title</th>
                            <th>Subject</th>
                            <th>Score</th>
                            <th>Total Questions</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($quiz = $result_submitted->fetch_assoc()): ?>
                            <tr class="<?= getGradeClass($quiz['grade']) ?>">
                                <td><?= htmlspecialchars($quiz['title']) ?></td>
                                <td><?= htmlspecialchars($quiz['subject']) ?></td>
                                <td><?= htmlspecialchars($quiz['score']) ?></td>
                                <td><?= htmlspecialchars($quiz['total_questions']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($quiz['grade']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-muted">No quizzes attempted yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../../components/student_footer.php'; ?>

    <?php
    // Function to get the Bootstrap class for the grade
    function getGradeClass($grade) {
        switch ($grade) {
            case 'A': return 'table-success'; // Green for A
            case 'B': return 'table-info';    // Light Blue for B
            case 'C': return 'table-warning'; // Yellow for C
            case 'D': return 'table-secondary'; // Grey for D
            case 'F': return 'table-danger';   // Red for F
            default: return ''; // No class for undefined grades
        }
    }
    ?>

    <!-- ✅ Correct Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
