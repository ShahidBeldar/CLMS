<?php
// index.php

// Display errors during development (turn off in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include DB connection and session management
require_once 'database.php';

// Dummy users (used silently if DB fails)
$dummyUsers = [
    "admin1" => ["password" => "admin123", "role" => "Admin"],
    "staff1" => ["password" => "staff123", "role" => "Staff"],
];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_Id = trim($_POST['user_Id']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($user_Id) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        $authenticated = false;

        // Try database login if DB is connected
        if (!$conn->connect_error) {
            $stmt = $conn->prepare("SELECT user_Id, password, role FROM User WHERE user_Id = ? AND role = ?");
            $stmt->bind_param("ss", $user_Id, $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if ($password === $user['password']) {
                    $_SESSION['user_Id'] = $user['user_Id'];
                    $_SESSION['role'] = $user['role'];
                    $authenticated = true;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No user found with that ID and role.";
            }
            $stmt->close();
        }

        // If DB fails or no match, try dummy login (hidden feature)
        if (!$authenticated && isset($dummyUsers[$user_Id])) {
            if ($dummyUsers[$user_Id]['password'] === $password && $dummyUsers[$user_Id]['role'] === $role) {
                $_SESSION['user_Id'] = $user_Id;
                $_SESSION['role'] = $role;
                $authenticated = true;
            }
        }

        if ($authenticated) {
            header("Location: dashboard.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Library Management System - Login</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f2f5;
        }
        .login-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 370px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 1rem;
            color: #333;
        }
        .login-container form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .login-container input, 
        .login-container select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .login-container button {
            background-color: #001f3f;
            color: #fff;
            padding: 0.75rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
        .login-container button:hover {
            background-color: #003366;
        }
        .error-message {
            color: #d9534f;
            margin-top: 1rem;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Library System Login</h2>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="Admin">Admin</option>
                <option value="Staff">Staff</option>
            </select>
            <input type="text" name="user_Id" placeholder="User ID" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>