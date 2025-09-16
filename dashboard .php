<?php
// dashboard.php

// Display all errors for debugging purposes
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection and authentication logic
require_once 'database.php';
checkAuth();

// Fetch counts for the cards
$studentCount = 0;
$bookCount = 0;
$borrowedCount = 0;
$returnedCount = 0;
$userCount = 0;

// Get total students
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Student");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $studentCount = $result->fetch_assoc()['count'];
}
$stmt->close();

// Get total books
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Book");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $bookCount = $result->fetch_assoc()['count'];
}
$stmt->close();

// Get total borrowed books (Status = 'Not Returned')
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Report WHERE Status = 'Not Returned'");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $borrowedCount = $result->fetch_assoc()['count'];
}
$stmt->close();

// Get total returned books (Status = 'Returned')
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM Report WHERE Status = 'Returned'");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $returnedCount = $result->fetch_assoc()['count'];
}
$stmt->close();

// Get total users (Admin-only)
if ($_SESSION['role'] === 'Admin') {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM User");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $userCount = $result->fetch_assoc()['count'];
    }
    $stmt->close();
}

$conn->close();

// Include the header template
require_once 'templates/header.php';
?>

<div class="main">
    <h1>Dashboard</h1>
    <p class="welcome">Welcome back, <?php echo htmlspecialchars($_SESSION['user_Id']); ?>! Here's a quick overview of the library.</p>
    
    <div class="card-container">
        <div class="card">
            <h3>Total Students</h3>
            <p class="count"><?php echo $studentCount; ?></p>
        </div>
        
        <div class="card">
            <h3>Total Books</h3>
            <p class="count"><?php echo $bookCount; ?></p>
        </div>
        
        <div class="card">
            <h3>Borrowed Books</h3>
            <p class="count"><?php echo $borrowedCount; ?></p>
        </div>
        
        <div class="card">
            <h3>Returned Books</h3>
            <p class="count"><?php echo $returnedCount; ?></p>
        </div>
        
        <?php if ($_SESSION['role'] === 'Admin'): ?>
            <div class="card">
                <h3>Total Users</h3>
                <p class="count"><?php echo $userCount; ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .card-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
    }
    .card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        padding: 24px;
        flex-grow: 1;
        flex-basis: 250px;
        text-align: center;
        transition: transform 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .card h3 {
        margin: 0 0 10px 0;
        color: var(--muted);
        font-size: 1.2rem;
    }
    .card .count {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--navy);
    }
</style>

<?php
// Include the footer template
require_once 'templates/footer.php';
?>
