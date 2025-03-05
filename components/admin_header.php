<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questio - admin</title>
    <link rel="stylesheet" href="../public/css/bootstrap.min.css">
    <style>
        .navbar {
            position: sticky;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1050;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-0">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand ms-0" href="admin_dashboard.php">
            <img src="/questio-git/public/images/logo.png" class="img-fluid" style="max-width: 80px;" alt="Questio-Logo">
            Admin-Panel
        </a>
     
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link px-3" href="admin_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="assign_subject.php">Manage Subjects</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="admin_profile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <form method="POST" action="../../index.php" class="d-inline ms-3">
                        <button type="submit" class="btn btn-outline-light px-4">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Bootstrap JS (Ensure proper navbar toggling) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
