<?php
require_once "../../config.php"; // Ensure database connection is correct

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST["fName"]);
    $lastName = trim($_POST["lName"]);
    $gender = $_POST["gender"] ?? "";
    $email = trim($_POST["email"]);
    $university = trim($_POST["university_name"]);
    $role = $_POST["role"] ?? "student";

    $mysqli->begin_transaction();
    try {
        if ($role === "student") {
            $semester = trim($_POST["semester"]);
            $department = trim($_POST["department"]);
            $roll_no = trim($_POST["roll_no"]);

            // Check if roll number already exists (case-insensitive)
            $checkRoll = $mysqli->prepare("SELECT roll_no FROM student_info WHERE LOWER(roll_no) = LOWER(?)");
            $checkRoll->bind_param("s", $roll_no);
            $checkRoll->execute();
            $checkRoll->store_result();

            if ($checkRoll->num_rows > 0) {
                throw new Exception("Roll number already exists!");
            }
            $checkRoll->close();

            // Auto-generate a password for students
            $plainPassword = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$%&*"), 0, 8);
            $password = password_hash($plainPassword, PASSWORD_DEFAULT);
        } else {
            $department = trim($_POST["teacher_department"] ?? "");
            if (empty($department)) {
                throw new Exception("Teacher department is required.");
            }

            $plainPassword = trim($_POST["password"]);
            if (strlen($plainPassword) < 6) {
                throw new Exception("Password must be at least 6 characters long.");
            }
            $password = password_hash($plainPassword, PASSWORD_DEFAULT);
        }

        // Insert into the user table
        $sql = "INSERT INTO user (first_name, last_name, gender, email, password, university_name, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sssssss", $firstName, $lastName, $gender, $email, $password, $university, $role);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting user: " . $stmt->error);
        }
        $userId = $stmt->insert_id;
        $stmt->close();

        if ($role === "student") {
            $sql = "INSERT INTO student_info (user_id, semester, department, roll_no) VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("isss", $userId, $semester, $department, $roll_no);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting student info: " . $stmt->error);
            }
            $stmt->close();

            // Show password alert before redirecting
            echo "<script>
                alert('Account created successfully! Your password is: $plainPassword');
                window.location.href = 'login.php';
            </script>";
        } else {
            $sql = "INSERT INTO teacher_info (user_id, department) VALUES (?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("is", $userId, $department);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting teacher info: " . $stmt->error);
            }
            $stmt->close();

            // Ensure transaction commits before redirecting
            echo "<script>
                alert('Teacher account created successfully!');
                window.location.href = 'login.php';
            </script>";
        }

        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        error_log("Error: " . $e->getMessage());
        echo "<script>alert('Registration failed: " . $e->getMessage() . "'); window.history.back();</script>";
    }

    $mysqli->close();
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="../../public/css/register.css">
    <script>
        function toggleFields() {
            var role = document.getElementById("role").value;
            var studentFields = document.getElementById("studentFields");
            var teacherFields = document.getElementById("teacherFields");
            var passwordInput = document.getElementById("password");
            var teacherDept = document.getElementById("teacher_department");
            var rollNo = document.getElementById("roll_no");
            var semester = document.getElementById("semester");
            var dept = document.getElementById("department");

            if (role === "student") {
                studentFields.style.display = "block";
                teacherFields.style.display = "none";
                rollNo.setAttribute("required", "true");
                semester.setAttribute("required", "true");
                dept.setAttribute("required", "true");
                passwordInput.removeAttribute("required");
                teacherDept.removeAttribute("required");
            } else {
                studentFields.style.display = "none";
                teacherFields.style.display = "block";
                rollNo.removeAttribute("required");
                semester.removeAttribute("required");
                dept.removeAttribute("required");
                passwordInput.setAttribute("required", "true");
                teacherDept.setAttribute("required", "true");
            }
        }

        function togglePassword() {
            var passwordField = document.getElementById("password");
            passwordField.type = passwordField.type === "password" ? "text" : "password";
        }

        function validatePassword() {
            var password = document.getElementById("password").value;
            var errorSpan = document.getElementById("passwordError");

            if (password.length < 6) {
                errorSpan.textContent = "Password must be at least 6 characters.";
            } else {
                errorSpan.textContent = "";
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("togglePassword").addEventListener("click", togglePassword);
            toggleFields(); // Ensure correct fields are shown on page load
        });
    </script>
</head>
<body>
    <form id="pform" action="register.php" method="POST">
        <h1>SIGN UP FORM</h1>

        <label for="fName">First Name: <input type="text" id="fName" name="fName" required></label>
        <label for="lName">Last Name: <input type="text" id="lName" name="lName"></label>

        <fieldset>
            <legend>Gender:</legend>
            <label><input type="radio" name="gender" value="male" required> Male</label>
            <label><input type="radio" name="gender" value="female" required> Female</label>
        </fieldset>

        <label for="role">Role:
            <select id="role" name="role" required onchange="toggleFields()">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
        </label>

        <label for="email">Email: <input type="email" id="email" name="email" required></label>
        <label for="university_name">University Name: <input type="text" id="university_name" name="university_name" required></label>

        <!-- Student-specific fields -->
        <div id="studentFields">
            <label for="semester">Semester: <input type="text" id="semester" name="semester"></label>
            <label for="department">Department: <input type="text" id="department" name="department"></label>
            <label for="roll_no">Roll Number: <input type="text" id="roll_no" name="roll_no"></label>
        </div>

        <!-- Teacher-specific fields -->
        <div id="teacherFields">
            <label for="teacher_department">Department: <input type="text" id="teacher_department" name="teacher_department"></label>
            <label for="password">Password:
                <input type="password" id="password" name="password" onkeyup="validatePassword()">
                <span id="togglePassword" style="cursor: pointer;">👁️</span>
            </label>
            <small id="passwordError"></small>
        </div>

        <input type="submit" value="Sign Up">
    </form>
</body>
</html>
