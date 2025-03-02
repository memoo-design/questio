<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-0">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand ms-0" href="../pages/admin/admin_dashboard.php">
            <img src="../../public/images/logo.png"  width="20%"alt="Questio-Logo" class="logo">
            Admin-Panel
        </a>

   
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
                    <form method="POST" action="../../index.php" class="d-inline">
                        <button type="submit" class="btn btn-outline-light px-4">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>


