<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questio - Teacher</title>
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
    <style>
        .navbar {
            position: sticky;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1050;
        }
        .logo {
            width: 100px; /* Adjust for consistency */
            height: auto;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top py-0">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand ms-0" href="teacher_dashboard.php">
                <img src="../../public/images/logo.png" alt="Questio-Logo" class="logo">
                Teacher-Dashboard
            </a>

            <!-- Toggle button for mobile view -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="teacher_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="quiz_create.php">Manage Quizzes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="teacher_profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-black" href="../../index.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
