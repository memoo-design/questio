<?php
session_start();
require_once "../../config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../guest/login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$university = $_SESSION['university_name'] ?? ''; // Default to empty if not set


// Fetch Published Quizzes
$sql = "SELECT id, title, department, semester, time_limit, created_at FROM quiz WHERE teacher_id = ? AND status = 'published'";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$publishedQuizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch Draft Quizzes
$sql = "SELECT id, title, department, semester, time_limit, created_at FROM quiz WHERE teacher_id = ? AND status = 'draft'";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$draftQuizzes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch students who attempted quizzes
$sql = "SELECT 
    user.first_name, 
    user.last_name, 
    student_info.department,  
    student_info.semester,   
    quiz.title AS quiz_title, 
    student_attempts.quiz_id, 
    student_attempts.score 
FROM student_attempts
JOIN user ON student_attempts.student_id = user.id 
JOIN student_info ON student_attempts.student_id = student_info.user_id  
JOIN quiz ON student_attempts.quiz_id = quiz.id 
WHERE user.university_name = ? 
AND quiz.teacher_id = ?";


$stmt = $mysqli->prepare($sql);
$stmt->bind_param("si", $university, $teacher_id);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/teacher_dashboard.css">
    <link rel="stylesheet" href="../public/css/teacher_header.css">
</head>
<body>
    <?php include '../../components/teacher_header.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Teacher Dashboard</h2>

        <!-- Published Quizzes -->
        <div class="mb-4">
            <h3>Your Published Quizzes</h3>
            <a href="quiz_create.php" class="btn btn-primary mb-3">Create New Quiz</a>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Time Limit</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($publishedQuizzes)): ?>
                        <?php foreach ($publishedQuizzes as $quiz): ?>
                            <tr>
                                <td><?= htmlspecialchars($quiz['title']) ?></td>
                                <td><?= htmlspecialchars($quiz['department']) ?></td>
                                <td><?= htmlspecialchars($quiz['semester'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($quiz['time_limit']) ?> min</td>
                                <td><?= date("d M Y, h:i A", strtotime($quiz['created_at'])) ?></td>
                                <td>
                                    <a href="view_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-warning btn-sm">View</a>
                                    <a href="delete_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this quiz?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-muted text-center">No published quizzes yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Draft Quizzes -->
        <div class="mb-4">
            <h3>Draft Quizzes</h3>
            <table class="table table-bordered">
                <thead class="table-secondary">
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Time Limit</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($draftQuizzes)): ?>
                        <?php foreach ($draftQuizzes as $quiz): ?>
                            <tr>
                                <td><?= htmlspecialchars($quiz['title']) ?></td>
                                <td><?= htmlspecialchars($quiz['department']) ?></td>
                                <td><?= htmlspecialchars($quiz['semester'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($quiz['time_limit']) ?> min</td>
                                <td><?= date("d M Y, h:i A", strtotime($quiz['created_at'])) ?></td>
                                <td>
                                    <a href="edit_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this quiz?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-muted text-center">No draft quizzes saved.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div>
            <h4>Students Who Attempted Quizzes</h4>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Quiz Title</th>
                        <th>Marks Obtained</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                <td><?= htmlspecialchars($student['department']) ?></td>
                                <td><?= htmlspecialchars($student['semester']) ?></td>
                                <td><?= htmlspecialchars($student['quiz_title']) ?></td>
                                <td><?= $student['score'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-muted text-center">No quiz attempts recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../../components/teacher_footer.php'; ?>
</body>
</html>
