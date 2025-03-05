<?php
session_start();
require_once '../../config.php'; 

// Check if the admin is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../guest/login.php");
    exit();
}

$email = $_SESSION['email'];
$success = $error = "";

// Fetch admin details
$sql = "SELECT id, first_name, last_name, email, university_name, password FROM user WHERE email = ? AND role = 'admin'";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    echo "Admin not found.";
    exit();
}

$user_id = $admin['id'];

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_first_name = trim($_POST['first_name']);
    $new_last_name = trim($_POST['last_name']);
    $new_university = trim($_POST['university']);

    $update_user_sql = "UPDATE user SET first_name = ?, last_name = ?, university_name = ? WHERE id = ?";
    $stmt = $mysqli->prepare($update_user_sql);
    $stmt->bind_param("sssi", $new_first_name, $new_last_name, $new_university, $user_id);

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        $_SESSION['first_name'] = $new_first_name;
        $_SESSION['university_name'] = $new_university;
    } else {
        $error = "Error updating profile.";
    }
    $stmt->close();
}

// Handle password change (With Current Password Verification)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if (!password_verify($current_password, $admin['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif ($new_password === $current_password) {
        $error = "New password must be different from the current password.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_pass_sql = "UPDATE user SET password = ? WHERE id = ?";
        $stmt = $mysqli->prepare($update_pass_sql);
        $stmt->bind_param("si", $hashed_password, $user_id);

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
    <title>Admin Profile</title>
    <link href="../../public/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
<?php include '../../components/admin_header.php'; ?>

    <div class="container mt-4">
        <h2 class="text-center">Admin Profile</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mx-auto p-4 shadow" style="max-width: 500px;">
            <h4>Update Profile</h4>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">First Name:</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($admin['first_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Name:</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($admin['last_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">University Name:</label>
                    <input type="text" name="university" class="form-control" value="<?= htmlspecialchars($admin['university_name']) ?>" required>
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
    
    <?php include '../../components/admin_footer.php'; ?>

</body>
</html>