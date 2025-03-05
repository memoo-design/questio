<?php
session_start();
require_once "../../config.php";


// Ensure only admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../guest/login.php");
    exit();
}

// Fetch statistics
$students_count = $mysqli->query("SELECT COUNT(*) AS total FROM user WHERE role = 'student'")->fetch_assoc()['total'];
$teachers_count = $mysqli->query("SELECT COUNT(*) AS total FROM user WHERE role = 'teacher'")->fetch_assoc()['total'];
$quizzes_count = $mysqli->query("SELECT COUNT(*) AS total FROM quiz")->fetch_assoc()['total'];
$attempts_count = $mysqli->query("SELECT COUNT(*) AS total FROM student_attempts")->fetch_assoc()['total'];

// Fetch users
$sqlUsers = "
    SELECT 
        user.id, 
        user.first_name, 
        user.last_name, 
        user.email, 
        user.role, 
        user.university_name,
        COALESCE(student_info.department, teacher_info.department, 'N/A') AS department,
        COALESCE(student_info.semester, 'N/A') AS semester
    FROM user
    LEFT JOIN student_info ON user.id = student_info.user_id
    LEFT JOIN teacher_info ON user.id = teacher_info.user_id
    WHERE user.role != 'admin' 
    ORDER BY user.role, department, semester
";

$resultUsers = $mysqli->query($sqlUsers);
// Fetch quizzes
$sqlQuizzes = "SELECT quiz.id, quiz.title, quiz.department, quiz.semester, quiz.status, quiz.teacher_id, user.first_name, user.last_name 
               FROM quiz 
               JOIN user ON quiz.teacher_id = user.id";
$resultQuizzes = $mysqli->query($sqlQuizzes);

// Fetch quiz attempts
$sqlAttempts = "SELECT 
    user.first_name, 
    user.last_name, 
    COALESCE(student_info.department, teacher_info.department, 'N/A') AS department,
    COALESCE(student_info.semester, 'N/A') AS semester,
    quiz.title AS quiz_title, 
    student_attempts.score, 
    student_attempts.total_questions, 
    student_attempts.grade 
FROM student_attempts 
JOIN user ON student_attempts.student_id = user.id 
LEFT JOIN student_info ON user.id = student_info.user_id
LEFT JOIN teacher_info ON user.id = teacher_info.user_id
JOIN quiz ON student_attempts.quiz_id = quiz.id 
ORDER BY student_attempts.id DESC";

$resultAttempts = $mysqli->query($sqlAttempts);


$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
</head>
<body>
    <?php include '../../components/admin_header.php'; ?>

    <div class="container-fluid mt-4">
        <h2 class="mb-4 text-center">Admin Dashboard</h2>

        <!-- Statistics Section -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
            <div class="col">
                <div class="card bg-primary text-white p-3 text-center">
                    <h5>Total Students</h5>
                    <h3><?= $students_count ?></h3>
                </div>
            </div>
            <div class="col">
                <div class="card bg-success text-white p-3 text-center">
                    <h5>Total Teachers</h5>
                    <h3><?= $teachers_count ?></h3>
                </div>
            </div>
            <div class="col">
                <div class="card bg-warning text-dark p-3 text-center">
                    <h5>Total Quizzes</h5>
                    <h3><?= $quizzes_count ?></h3>
                </div>
            </div>
            <div class="col">
                <div class="card bg-danger text-white p-3 text-center">
                    <h5>Quiz Attempts</h5>
                    <h3><?= $attempts_count ?></h3>
                </div>
            </div>
        </div>

        <!-- Manage Users -->
        <h4 class="mt-5">Manage Users</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>University</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $resultUsers->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['department']) ?></td>
                            <td><?= htmlspecialchars($user['semester']) ?></td>
                            <td><?= htmlspecialchars($user['university_name']) ?></td>
                            <td class="text-center">
                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Manage Quizzes -->
        <h4 class="mt-5">Manage Quizzes</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Title</th>
                        <th>Department</th>
                        <th>Semester</th>
                        <th>Teacher</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($quiz = $resultQuizzes->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($quiz['title']) ?></td>
                            <td><?= htmlspecialchars($quiz['department']) ?></td>
                            <td><?= htmlspecialchars($quiz['semester']) ?></td>
                            <td><?= htmlspecialchars($quiz['first_name'] . ' ' . $quiz['last_name']) ?></td>
                            <td><?= htmlspecialchars($quiz['status']) ?></td>
                            <td class="text-center">
                                <a href="delete_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include '../../components/admin_footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
