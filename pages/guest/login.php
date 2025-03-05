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
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
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
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            height: auto;
            background-image: url('../../public/images/signup.jpg');
            background-repeat: no-repeat;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: rgba(179, 188, 185, 0.3);
            color: black;
            width: 80%;
            max-width: 600px;
          
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 18px;
        }

        h2 {
            text-align: center;
        }

        label {
            display: block;
            margin: 10px 5px 10px 0px;
        }

        input, select {
            width: 100%;
            height: 30px;
            border-radius: 5px;
            background-color: rgba(242, 240, 239, 0.685) !important;
            padding: 5px;
        }

        .btn-primary {
            background-color: rgb(96, 94, 94);
            width: 100%;
            height: 45px;
            font-size: 18px;
            margin-top: 15px;
            text-align: center;
            border: none;
        }

        .btn-primary:hover {
            background-color: black;
            color: whitesmoke;
        }

        @media (max-width: 768px) {
            .login-container {
                width: 90%;
                padding: 15px;
                font-size: 16px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                width: 95%;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="POST">
            <div class="mb-3">
                <label for="role" class="form-label">Select Role:</label>
                <select id="role" name="role" class="form-select" onchange="updateForm()">
                    <option value="admin" selected>Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>
            </div>
            
            <h2 id="formTitle">ADMIN LOGIN</h2>
            
            <?php if (!empty($error_msg)): ?>
                <p class="text-danger text-center"><?php echo htmlspecialchars($error_msg); ?></p>
            <?php endif; ?>

            <div id="emailField" class="mb-3">
                <label class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>
            
            <div id="rollNoField" class="mb-3" style="display: none;">
                <label class="form-label">Roll No:</label>
                <input type="text" id="roll_no" name="roll_no" class="form-control">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="off">
            </div>
            
            <button type="submit" class="btn btn-primary">Log In</button>
            
            <p class="text-center mt-3">Haven't any account? <a href="register.php" style="color: lightblue; text-decoration: none;">Signup here</a>.</p>
        </form>
    </div>
</body>
</html>