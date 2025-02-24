<?php
session_start();
require_once '../../config.php';

// Check if the student is logged in
if (!isset($_SESSION['roll_no'])) { // Change email to roll_no
    header("Location: ../guest/login.php");
    exit();
}

$roll_no = $_SESSION['roll_no']; // Use roll_no instead of email
$success = $error = "";

// Fetch student details
$sql = "SELECT u.first_name, u.last_name, u.email, u.university_name, u.password 
        FROM user u
        JOIN student_info s ON u.id = s.user_id
        WHERE s.roll_no = ? AND u.role = 'student'";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $roll_no);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    echo "Student not found.";
    exit();
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_first_name = trim($_POST['first_name']);
    $new_last_name = trim($_POST['last_name']);
    $new_university = trim($_POST['university']);

    $update_sql = "UPDATE user u 
    JOIN student_info s ON u.id = s.user_id
    SET u.first_name = ?, u.last_name = ?, u.university_name = ? 
    WHERE s.roll_no = ?";

    $stmt = $mysqli->prepare($update_sql);
    $stmt->bind_param("ssss", $new_first_name, $new_last_name, $new_university, $roll_no);
    
    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        $_SESSION['first_name'] = $new_first_name;
        $_SESSION['university_name'] = $new_university;
    } else {
        $error = "Error updating profile.";
    }
    $stmt->close();
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($current_password, $student['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_pass_sql = "UPDATE user u 
        JOIN student_info s ON u.id = s.user_id
        SET u.password = ? 
        WHERE s.roll_no = ?";

        $stmt = $mysqli->prepare($update_pass_sql);
        $stmt->bind_param("ss", $hashed_password, $roll_no);

        if ($stmt->execute()) {
            $success = "Password changed successfully!";
        } else {
            $error = "Error updating password.";
        }
        $stmt->close();
    }
}

$mysqli->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
<?php include '../../components/student_header.php'; ?>

    <div class="container mt-4">
        <h2 class="text-center">Student Profile</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card mx-auto p-4 shadow" style="max-width: 500px;">
            <h4>Update Profile</h4>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">First Name:</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Name:</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">University Name:</label>
                    <input type="text" name="university" class="form-control" value="<?= htmlspecialchars($student['university_name']) ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
            </form>
        </div>

        <div class="card mx-auto mt-4 p-4 shadow" style="max-width: 500px;">
            <h4>Change Password</h4>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Current Password:</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password:</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password:</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-danger">Change Password</button>
            </form>
        </div>
    </div>
    <?php include '../../components/student_footer.php'; ?>
   
</body>
</html>
