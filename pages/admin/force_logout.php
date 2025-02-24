<?php
require_once "../../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ip_address"])) {
    $ip_address = $_POST["ip_address"];

    // Remove the session from the database
    $sql = "DELETE FROM sessions WHERE ip_address = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $ip_address);

    if ($stmt->execute()) {
        // Successfully removed session
        $_SESSION['success_message'] = "User logged out successfully!";
    } else {
        // Error handling
        $_SESSION['error_message'] = "Error logging out user!";
    }

    $stmt->close();
    $mysqli->close();
}

// Redirect back to Security & Logs page
header("Location: security.php");
exit();
