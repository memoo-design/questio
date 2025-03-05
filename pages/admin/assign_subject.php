<?php
include '../../config.php';

// Fetch newly signed-up teachers (those without assigned subjects)
$query = "SELECT u.id, u.first_name, u.last_name, u.email, 
                 COALESCE(COUNT(ts.subject_id), 0) AS subject_count
          FROM user u
          LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_id
          WHERE u.role = 'teacher'
          GROUP BY u.id, u.first_name, u.last_name, u.email
          HAVING subject_count < 3";

$teachers_result = $mysqli->query($query);



// Fetch available subjects
$subjects = [
    'Computer Networks',
    'Operating Systems',
    'Data Structures',
    'Database Management',
    'Artificial Intelligence',
    'Machine Learning',
    'Cyber Security',
    'Software Engineering',
    'Cloud Computing',
    'Web Development'
];

// Insert subjects into subjects table if they don’t exist
foreach ($subjects as $subject) {
    $check_subject = "SELECT id FROM subjects WHERE name = ?";
    $stmt = $mysqli->prepare($check_subject);
    $stmt->bind_param("s", $subject);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $insert_subject = "INSERT INTO subjects (name) VALUES (?)";
        $stmt = $mysqli->prepare($insert_subject);
        $stmt->bind_param("s", $subject);
        $stmt->execute();
    }
}

// Fetch subjects from the database
$subjects_query = "SELECT id, name FROM subjects";
$subjects_result = $mysqli->query($subjects_query);

// Handle Subject Assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];

    // Check how many subjects are already assigned
    $count_query = "SELECT COUNT(*) AS subject_count FROM teacher_subjects WHERE teacher_id = ?";
    $stmt = $mysqli->prepare($count_query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['subject_count'] < 3) { // Allow up to 3 subjects
        // Check if the subject is already assigned
        $check_query = "SELECT * FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?";
        $stmt = $mysqli->prepare($check_query);
        $stmt->bind_param("ii", $teacher_id, $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Assign subject to teacher
            $query = "INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ii", $teacher_id, $subject_id);

            if ($stmt->execute()) {
                echo "<script>alert('Subject assigned successfully!'); window.location.href='assign_subject.php';</script>";
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }
        } else {
            echo "<script>alert('This subject is already assigned to the selected teacher.');</script>";
        }
    } else {
        echo "<script>alert('A teacher can only be assigned up to 3 subjects.');</script>";
    }
}

// Fetch assigned subjects with teacher details
$query = "SELECT ts.id AS assignment_id, u.first_name, u.last_name, u.email, s.name AS subject_name 
          FROM teacher_subjects ts
          JOIN user u ON ts.teacher_id = u.id
          JOIN subjects s ON ts.subject_id = s.id";
$assigned_subjects_result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Subjects-Admin</title>
    <link href="../../public/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../public/css/assign_subject.css">
</head>
<body>
<?php require_once "../../components/admin_header.php" ?>
<div class="container">
    <h2 class="text-center mb-4">Assign Subjects to New Teachers</h2>

    <form action="" method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-5">
                <label for="teacher" class="form-label">Select Teacher:</label>
                <select name="teacher_id" class="form-select" required>
                    <?php while ($row = $teachers_result->fetch_assoc()) { ?>
                        <option value="<?= $row['id'] ?>"><?= $row['first_name'] . ' ' . $row['last_name'] ?> (<?= $row['email'] ?>)</option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-5">
                <label for="subject" class="form-label">Select Subject:</label>
                <select name="subject_id" class="form-select" required>
                    <?php while ($subject = $subjects_result->fetch_assoc()) { ?>
                        <option value="<?= $subject['id'] ?>"><?= $subject['name'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-custom w-100">Assign</button>
            </div>
        </div>
    </form>

    <h2 class="text-center mb-4">Manage Assigned Subjects</h2>
    <table class="table table-dark table-bordered">
        <thead>
            <tr>
                <th>Teacher Name</th>
                <th>Email</th>
                <th>Assigned Subject</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $assigned_subjects_result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['first_name'] . ' ' . $row['last_name'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['subject_name'] ?></td>
                    <td>
                        <form action="remove_subject.php" method="POST" class="d-inline">
                            <input type="hidden" name="assignment_id" value="<?= $row['assignment_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Remove</button>
                        </form>
                        <form action="reassign_subject.php" method="POST" class="d-inline">
    <input type="hidden" name="assignment_id" value="<?= $row['assignment_id'] ?>">
    <select name="new_subject_id" class="form-select d-inline w-auto">
        <?php
        $subjects_result = $mysqli->query("SELECT id, name FROM subjects");
        while ($subject = $subjects_result->fetch_assoc()) {
            $selected = ($subject['name'] == $row['subject_name']) ? "selected" : "";
            echo "<option value='{$subject['id']}' $selected>{$subject['name']}</option>";
        }
        ?>
    </select>
    <button type="submit" class="btn btn-custom btn-sm">Reassign</button>
</form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once "../../components/admin_footer.php" ?>
</body>
</html>