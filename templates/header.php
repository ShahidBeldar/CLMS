<?php
// templates/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["user_Id"])) {
    header("Location: index.php");
    exit();
}
$user_Id = $_SESSION["user_Id"];
$role = $_SESSION["role"];
function isActive($page) {
    return (basename($_SERVER['PHP_SELF']) == $page) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>College Library Management System</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="navbar">
    <div class="logo">LMS</div>
    <div class="nav-links">
        <a href="dashboard.php" class="<?php echo isActive('dashboard.php'); ?>">Dashboard</a>
        <a href="students.php" class="<?php echo isActive('students.php'); ?>">Students</a>
        <a href="books.php" class="<?php echo isActive('books.php'); ?>">Books</a>
        <a href="borrowed.php" class="<?php echo isActive('borrowed.php'); ?>">Borrowed Books</a>
        <a href="returned.php" class="<?php echo isActive('returned.php'); ?>">Returned Books</a>
        <?php if ($role === "Admin"): ?>
            <a href="users.php" class="<?php echo isActive('users.php'); ?>">Users</a>
        <?php endif; ?>
    </div>
</div>
