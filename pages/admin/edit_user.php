<?php
session_start();
require_once "../../config.php";

// Ensure only admins can edit users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../guest/login.php");
    exit();
}

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = intval($_GET['id']);

// Fetch user details
$sql = "SELECT id, first_name, last_name, email, role, password FROM user WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "User not found.";
    header("Location: admin_dashboard.php");
    exit();
}

$user = $result->fetch_assoc();

// Fetch department and semester if user is a student or teacher
$department = "N/A";
$semester = "N/A";

if ($user['role'] === 'student') {
    $query = "SELECT department, semester FROM student_info WHERE user_id = ?";
} elseif ($user['role'] === 'teacher') {
    $query = "SELECT department FROM teacher_info WHERE user_id = ?";
}

if (isset($query)) {
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $department = $row['department'] ?? "N/A";
        $semester = $row['semester'] ?? "N/A";
    }
}

// Update user details
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $department = trim($_POST['department']);
    $semester = trim($_POST['semester']);
    $new_password = trim($_POST['password']);

    // Prevent changing role of an admin
    if ($user['role'] === 'admin' && $role !== 'admin') {
        $_SESSION['error'] = "Cannot change the role of an admin.";
        header("Location: admin_dashboard.php");
        exit();
    }

    // Update user table (with password only if provided)
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updateUserQuery = "UPDATE user SET first_name = ?, last_name = ?, email = ?, role = ?, password = ? WHERE id = ?";
        $stmt = $mysqli->prepare($updateUserQuery);
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $role, $hashed_password, $user_id);
    } else {
        $updateUserQuery = "UPDATE user SET first_name = ?, last_name = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $mysqli->prepare($updateUserQuery);
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $role, $user_id);
    }

    if ($stmt->execute()) {
        // Update department/semester based on role
        if ($role === 'student') {
            $updateStudentQuery = "INSERT INTO student_info (user_id, department, semester) 
                VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE department = VALUES(department), semester = VALUES(semester)";
            $stmt = $mysqli->prepare($updateStudentQuery);
            $stmt->bind_param("iss", $user_id, $department, $semester);
            $stmt->execute();
        } elseif ($role === 'teacher') {
            $updateTeacherQuery = "INSERT INTO teacher_info (user_id, department) 
                VALUES (?, ?) ON DUPLICATE KEY UPDATE department = VALUES(department)";
            $stmt = $mysqli->prepare($updateTeacherQuery);
            $stmt->bind_param("is", $user_id, $department);
            $stmt->execute();
        }

        $_SESSION['success'] = "User updated successfully.";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update user.";
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit User</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                    <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" name="department" value="<?= htmlspecialchars($department) ?>" class="form-control">
            </div>

            <div class="mb-3" id="semester_field" style="display: <?= $user['role'] === 'student' ? 'block' : 'none' ?>;">
                <label class="form-label">Semester</label>
                <input type="text" name="semester" value="<?= htmlspecialchars($semester) ?>" class="form-control">
            </div>

            <?php if ($_SESSION['role'] === 'admin'): ?>
                <div class="mb-3">
                    <label class="form-label">New Password (Leave blank to keep unchanged)</label>
                    <input type="password" name="password" class="form-control">
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script>
        document.querySelector("select[name='role']").addEventListener("change", function() {
            document.getElementById("semester_field").style.display = this.value === "student" ? "block" : "none";
        });
    </script>
</body>
</html>
