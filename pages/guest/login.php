<?php
session_start();
require_once "../../config.php"; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = isset($_POST["role"]) ? htmlspecialchars($_POST["role"]) : "admin";
    $password = $_POST["password"];
    $error_msg = "";

    if ($role === "student") {
        $roll_no = htmlspecialchars($_POST["roll_no"]);

        // Check if student exists
        $sql = "SELECT u.id, u.password FROM user u 
                JOIN student_info s ON u.id = s.user_id 
                WHERE s.roll_no = ? AND u.role = 'student'";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $roll_no);
    } else {
        $email = htmlspecialchars($_POST["email"]);

        // Check if teacher/admin exists
        $sql = "SELECT id, password FROM user WHERE email = ? AND role = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $email, $role);
    }

    // Execute query and fetch result
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user["password"])) {
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["role"] = $role;
                
                // Store Roll No only for students
                if ($role === "student") {
                    $_SESSION["roll_no"] = $roll_no;
                } else {
                    $_SESSION["email"] = $email;
                }

                // Redirect user based on role
                switch ($role) {
                    case "student":
                        header("Location: ../student/student_dashboard.php");
                        break;
                    case "teacher":
                        header("Location: ../teacher/teacher_dashboard.php");
                        break;
                    case "admin":
                        header("Location: ../admin/admin_dashboard.php");
                        break;
                }
                exit();
            } else {
                $error_msg = "Invalid password!";
            }
        } else {
            $error_msg = "User not found!";
        }
    } else {
        $error_msg = "Database error: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
    $mysqli->close();

    // Show error message using JavaScript alert
    echo "<script>alert('$error_msg'); window.history.back();</script>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../../public/css/register.css">
    <script>
        function updateForm() {
            var role = document.getElementById("role").value;
            document.getElementById("formTitle").innerText = role.toUpperCase() + " LOGIN";
            
            var emailField = document.getElementById("emailField");
            var rollNoField = document.getElementById("rollNoField");
            var emailInput = document.getElementById("email");
            var rollNoInput = document.getElementById("roll_no");

            if (role === "student") {
                emailField.style.display = "none";
                rollNoField.style.display = "block";
                emailInput.removeAttribute("required");
                rollNoInput.setAttribute("required", "true");
            } else {
                emailField.style.display = "block";
                rollNoField.style.display = "none";
                rollNoInput.removeAttribute("required");
                emailInput.setAttribute("required", "true");
            }
        }
    </script>
</head>
<body>
    <form id="pform" action="login.php" method="POST">
        <div style="position: absolute; top: 10px; left: 10px;">
            <label for="role">Select Role:</label>
            <select id="role" name="role" onchange="updateForm()">
                <option value="admin" selected>Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
            </select>
        </div>

        <section id="user">
            <h1 id="formTitle">ADMIN LOGIN</h1>

            <?php if (!empty($error_msg)): ?>
                <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error_msg); ?></p>
            <?php endif; ?>

            <div id="login">
                <div id="emailField" style="margin-left: 270px;">
                    <label>Email: <input type="email" id="email" name="email"></label>
                </div>

                <div id="rollNoField" style="margin-left: 260px; display: none;">
                    <label>Roll No: <input type="text" id="roll_no" name="roll_no"></label> 
                </div>

                <div style="margin-left: 230px;">
                    <label>Password: <input type="password" id="password" name="password" required autocomplete="off"></label> 
                </div>

                <div style="margin-left: 280px; margin-top:20px">
                    <input type="submit" id="Btn" value="Log In">
                    <p> haven't any account? <a href="register.php" style="color: lightblue; text-decoration: none;">Signup here</a>.</p>

                </div>
            </div>
        </section>
    </form>
</body>
</html>
