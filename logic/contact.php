<?php
require_once "../config.php";

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];

// Insert into database
$sql = "INSERT INTO contacts (name, email, message) VALUES ('$name', '$email', '$message')";

if ($mysqli->query($sql) === TRUE) {
    // Redirect to thank you page
    header("Location: ../pages/guest/thankyou.php");
} else {
    echo "Error: " . $sql . "<br>" . $mysqli->error;
}

$mysqli->close();
?>
