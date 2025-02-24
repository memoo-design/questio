<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../guest/login.php");
    exit();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="student_dashboard.php">Questio - Student Panel</a>

        <!-- Toggle button for mobile view -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="student_dashboard.php">Dashboard</a>
                </li>
              
                <li class="nav-item">
                    <a class="nav-link" href="student_profile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-light" href="../../index.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
