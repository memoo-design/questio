<?php

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $mysqli->query("UPDATE sessions SET last_activity = CURRENT_TIMESTAMP WHERE user_id = '$user_id'");
}
?>