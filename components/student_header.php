<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: ../guest/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questio - Student Dashboard</title>
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
    <style>
        .logo {
            width: 50px; 
            height: auto;
        }
    .logout-btn:hover {
    background-color: white !important;
    border-color: black !important;
    color:black !important;
}
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
    <div class="container">
        <!-- Logo & Brand -->
        <a class="navbar-brand d-flex align-items-center me-auto" href="student_dashboard.php">
            <img src="../../public/images/logo.png" class="logo me-2" alt="Questio Logo">
            <span class="fw-bold text-white">Student Dashboard</span>
        </a>

        <!-- Toggle button for mobile view -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto text-center">
                <li class="nav-item">
                    <a class="nav-link" href="student_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="student_profile.php">Profile</a>
                </li>
                <li class="nav-item">
    <a class="nav-link btn btn-outline-light ms-lg-2 mt-2 mt-lg-0 logout-btn bg-transparent 
       text-light" href="../../index.php">Logout</a>
</li>

            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-w76A8pu4lH07SI/tqPpD1z7gJtIabjA0M2l9gW58cy4Re9Nc3EwRFSFjqv9U5sw+" 
        crossorigin="anonymous"></script>

</body>
</html>
