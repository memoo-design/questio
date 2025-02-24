<?php
require_once "../../config.php";
require_once "login_logs.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security & Logs</title>
    <link rel="stylesheet" href="../../public/css/admin.css"> <!-- Your CSS File -->
    <link rel="stylesheet" href="../../public/css/bootstrap.min.css">
</head>
<body>

<?php include '../../components/admin_header.php'; ?>

<div class="container">
    <h2>Security & Logs</h2>

    <?php
    // Fetch Active Sessions
    $sqlActiveSessions = "SELECT user.first_name, user.last_name, user.role, sessions.ip_address, sessions.last_activity 
                          FROM sessions 
                          JOIN user ON sessions.user_id = user.id 
                          ORDER BY sessions.last_activity DESC";
    $resultActiveSessions = $mysqli->query($sqlActiveSessions);

    // Fetch Blocked IPs
    $sqlBlockedIPs = "SELECT ip_address, block_time FROM blocked_ips ORDER BY block_time DESC";
    $resultBlockedIPs = $mysqli->query($sqlBlockedIPs);
    ?>

    <h4 class="mt-5">🛡️ Active Sessions</h4>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>User</th>
                <th>Role</th>
                <th>IP Address</th>
                <th>Last Activity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($session = $resultActiveSessions->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($session['first_name'] . ' ' . $session['last_name']) ?></td>
                    <td><?= htmlspecialchars($session['role']) ?></td>
                    <td><?= htmlspecialchars($session['ip_address']) ?></td>
                    <td><?= htmlspecialchars($session['last_activity']) ?></td>
                    <td>
                        <form method="POST" action="force_logout.php">
                            <input type="hidden" name="ip_address" value="<?= htmlspecialchars($session['ip_address']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Force Logout</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h4 class="mt-5">🚫 Blocked IPs</h4>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>IP Address</th>
                <th>Blocked At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($blockedIP = $resultBlockedIPs->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($blockedIP['ip_address']) ?></td>
                    <td><?= htmlspecialchars($blockedIP['block_time']) ?></td>
                    <td>
                        <form method="POST" action="unblock_ip.php">
                            <input type="hidden" name="ip_address" value="<?= htmlspecialchars($blockedIP['ip_address']) ?>">
                            <button type="submit" class="btn btn-success btn-sm">Unblock</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../../components/admin_footer.php'; ?> <!-- Include Footer -->

</body>
</html>
