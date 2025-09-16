<?php
// database.php

// This file handles database connection and authentication logic.

// Start the session at the very beginning of the file.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database credentials
$host = "sql312.infinityfree.com";
$user = "if0_39917959";
$pass = "X1Wa18WooBx";
$dbname = "if0_39917959_collegelibrary";

// Establish a secure MySQLi connection.
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    // Terminate script and show error if connection fails.
    die("Connection failed: " . $conn->connect_error);
}

// Function to handle authentication and redirection.
// This function checks if a user is logged in. If not, it redirects them to the login page.
function checkAuth($requiredRole = null) {
    if (!isset($_SESSION['user_Id'])) {
        // Redirect to login page if user is not logged in.
        header("Location: index.php");
        exit();
    }

    // Check for role-based access control.
    if ($requiredRole && $_SESSION['role'] !== $requiredRole) {
        // Redirect to dashboard or an error page if the user's role doesn't match the required role.
        // For this project, we'll redirect to the dashboard.
        header("Location: dashboard.php");
        exit();
    }
}
?>
