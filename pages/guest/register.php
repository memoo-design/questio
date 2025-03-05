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
    <title>Sign Up</title>
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Questio</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center">Sign Up</h2>
            <form id="pform" action="register.php" method="POST">
                <div class="mb-3">
                    <label for="fName" class="form-label">First Name</label>
                    <input type="text" id="fName" name="fName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="lName" class="form-label">Last Name</label>
                    <input type="text" id="lName" name="lName" class="form-control">
                </div>
                <fieldset class="mb-3">
                    <legend class="fs-6">Gender</legend>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" value="male" required>
                        <label class="form-check-label">Male</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" value="female" required>
                        <label class="form-check-label">Female</label>
                    </div>
                </fieldset>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-select" required onchange="toggleFields()">
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="university_name" class="form-label">University Name</label>
                    <input type="text" id="university_name" name="university_name" class="form-control" required>
                </div>
                <div id="studentFields">
                    <div class="mb-3">
                        <label for="semester" class="form-label">Semester</label>
                        <select id="semester" name="semester" class="form-select" required>
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
                    </div>
                    <div class="mb-3">
                        <label for="roll_no" class="form-label">Roll Number</label>
                        <input type="text" id="roll_no" name="roll_no" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="department" class="form-label">Department</label>
                    <select id="department" name="department" class="form-select" required>
                        <option value="">Select Department</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Software Engineering">Software Engineering</option>
                        <option value="Artificial Intelligence">Artificial Intelligence</option>
                        <option value="Data Science">Data Science</option>
                    </select>
                </div>
                <div id="teacherFields" class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" onkeyup="validatePassword()">
                        <span class="input-group-text" id="togglePassword" style="cursor: pointer;">👁️</span>
                    </div>
                    <small id="passwordError" class="text-danger"></small>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary w-100">Sign Up</button>
                </div>
                <p class="text-center mt-3">Already have an account? <a href="login.php" style="color: blue;">Login here</a>.</p>
            </form>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
