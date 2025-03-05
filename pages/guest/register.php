<?php
require_once "../../config.php"; // Ensure correct database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST["fName"]);
    $lastName = trim($_POST["lName"]);
    $gender = $_POST["gender"] ?? "";
    $email = trim($_POST["email"]);
    $university = trim($_POST["university_name"]);
    $role = $_POST["role"] ?? "student";
    $department = trim($_POST["department"]);

    $mysqli->begin_transaction();
    try {
        if ($role === "student") {
            $semester = trim($_POST["semester"]);
            $roll_no = trim($_POST["roll_no"]);

            // Validate Roll Number Format (SSI-SSS-III)
            if (!preg_match("/^[A-Z]{2}[0-9]{2}-[A-Z]{3}-[0-9]{3}$/", $roll_no)) {
                throw new Exception("Invalid roll number format! Must be in IA12-CSE-016 format.");
            }

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
            $plainPassword = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
        } else {
            $plainPassword = trim($_POST["password"]);
            if (strlen($plainPassword) < 6) {
                throw new Exception("Password must be at least 6 characters long.");
            }
        }

        // Hash the password
        $password = password_hash($plainPassword, PASSWORD_DEFAULT);

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

            echo "<script>
                alert('Student account created successfully! Your password is: $plainPassword');
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

            echo "<script>
                alert('Teacher account created successfully!');
                window.location.href = 'login.php';
            </script>";
        }

        $mysqli->commit();
    } catch (Exception $e) {
        $mysqli->rollback();
        error_log("Error: " . $e->getMessage());
        $errorMessage = addslashes($e->getMessage()); // Escape special characters
echo "<script>
    alert('Registration failed: $errorMessage');
    window.history.back();
</script>";

    }

    $mysqli->close();
}
?>

?>






<!DOCTYPE html>
<html lang="en">
<head>

    <title>Sign Up - Questio</title>
   
    <link rel="stylesheet" href="../../public/css/register.css">
    <script>
        function toggleFields() {
            var role = document.getElementById("role").value;
            var studentFields = document.getElementById("studentFields");
            var teacherFields = document.getElementById("teacherFields");
            var passwordInput = document.getElementById("password");
            var rollNo = document.getElementById("roll_no");
            var semester = document.getElementById("semester");

            if (role === "student") {
                studentFields.style.display = "block";
                teacherFields.style.display = "none";
                rollNo.setAttribute("required", "true");
                semester.setAttribute("required", "true");
                passwordInput.removeAttribute("required");
            } else {
                studentFields.style.display = "none";
                teacherFields.style.display = "block";
                rollNo.removeAttribute("required");
                semester.removeAttribute("required");
                passwordInput.setAttribute("required", "true");
            }
        }

        function validateRollNumber() {
            var rollNo = document.getElementById("roll_no").value;
            var errorSpan = document.getElementById("rollNoError");

            if (!/^[A-Z]{3}-[A-Z]{3}-[0-9]{3}$/.test(rollNo)) {
                errorSpan.textContent = "Invalid format! Use SSI-SSS-III (e.g., CSE-ABC-001).";
            } else {
                errorSpan.textContent = "";
            }
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
            document.getElementById("role").addEventListener("change", toggleFields);
            document.getElementById("roll_no").addEventListener("keyup", validateRollNumber);
            document.getElementById("password").addEventListener("keyup", validatePassword);
            toggleFields();
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
        <label for="semester">Semester:
        <select id="semester" name="semester" required>
            <option value="">Select Semester</option>
            <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                    <option value="3rd Semester">3rd Semester</option>
                    <option value="4th Semester">4th Semester</option>
                    <option value="5th Semester">5th Semester</option>
                    <option value="6th Semester">6th Semester</option>
                    <option value="7th Semester">7th Semester</option>
                    <option value="8th Semester">8th Semester</option>
        </select>
    

            <label for="roll_no">Roll Number: <input type="text" id="roll_no" name="roll_no"></label>
        </div>
        <label for="department">Department:
        <select id="department" name="department" required>
            <option value="">Select Department</option>
            <option value="Computer Science">Computer Science</option>
            <option value="Information Technology">Information Technology</option>
            <option value="Software Engineering">Software Engineering</option>
            <option value="Artificial Intelligence">Artificial Intelligence</option>
            <option value="Data Science">Data Science</option>
        </select>
    </label>
        <!-- Teacher-specific fields -->
        <div id="teacherFields">
            <label for="password">Password:
                <input type="password" id="password" name="password" onkeyup="validatePassword()"></label>
               <label> <input type="checkbox" id="togglePassword"> Show Password
                <script>
        document.getElementById("togglePassword").addEventListener("change", function() {
            let passwordField = document.getElementById("password");
            if (this.checked) {
                passwordField.type = "text"; // Show password
            } else {
                passwordField.type = "password"; // Hide password
            }
        });
    </script>
            </label>
            <small id="passwordError"></small>
        </div>

        <input type="submit" value="Sign Up">
        <p>Already have an account? <a href="login.php" style="color: lightblue";>Login here</a>.</p>
    </form>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>