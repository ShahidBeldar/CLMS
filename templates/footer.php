<?php
// templates/footer.php

// This script handles the logout logic.
// Check if the form has been submitted and the 'logout' button was pressed.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_start();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<div style="text-align: right; padding: 20px;">
    <form method="POST" action="">
        <button type="submit" name="logout" style="
            background-color: #e74c3c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        ">
            Logout
        </button>
    </form>
</div>

</body>
</html>
