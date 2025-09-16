<?php
// borrowed.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection and authentication file
require_once 'database.php';
require_once 'templates/header.php'; // Includes session_start() and auth check

// --- Handle POST actions: add / update / delete ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $student_id = $_POST['Student_Id'] ?? '';
        $book_id = $_POST['Book_Id'] ?? '';
        $due_date = $_POST['Due_Date'] ?? null;
        $issue_date = date("Y-m-d"); // Use Issue_Date as the borrow date

        $stmt = $conn->prepare("INSERT INTO Report (Student_Id, Book_Id, Issue_Date, Due_Date, Status) VALUES (?, ?, ?, ?, 'Not Returned')");
        $stmt->bind_param("ssss", $student_id, $book_id, $issue_date, $due_date);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'update') {
        $reg_no = $_POST['Reg_no'] ?? '';
        $student_id = $_POST['Student_Id'] ?? '';
        $book_id = $_POST['Book_Id'] ?? '';
        $status = $_POST['Status'] ?? 'Not Returned';

        // Check if the status is being changed to 'Returned'
        if ($status === 'Returned') {
            $return_date = date("Y-m-d"); // Set return date to current date
            $stmt = $conn->prepare("UPDATE Report SET Student_Id=?, Book_Id=?, Status=?, Return_Date=? WHERE Reg_no=?");
            $stmt->bind_param("sssss", $student_id, $book_id, $status, $return_date, $reg_no);
        } else {
            // Keep Return_Date as NULL if status is 'Not Returned'
            $stmt = $conn->prepare("UPDATE Report SET Student_Id=?, Book_Id=?, Status=?, Return_Date=NULL WHERE Reg_no=?");
            $stmt->bind_param("ssss", $student_id, $book_id, $status, $reg_no);
        }
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete') {
        $reg_no = $_POST['Reg_no'] ?? '';
        $stmt = $conn->prepare("DELETE FROM Report WHERE Reg_no=?");
        $stmt->bind_param("s", $reg_no);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: borrowed.php");
    exit();
}

// --- Fetch all currently borrowed books ---
$borrowed_books = [];
$sql = "SELECT r.Reg_no, r.Student_Id, r.Book_Id, r.Issue_Date, r.Due_Date, r.Status, r.Return_Date,
               s.Name AS Student_Name, b.Name AS Book_Name
        FROM Report r
        JOIN Student s ON r.Student_Id = s.Student_Id
        JOIN Book b ON r.Book_Id = b.Book_Id
        WHERE r.Status = 'Not Returned'
        ORDER BY r.Issue_Date DESC"; // Fix applied here

$res = $conn->query($sql);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $borrowed_books[] = $r;
    }
    $res->free();
}
$conn->close();
?>

<div class="main">
    <div class="top-actions">
        <div>
            <h1>Borrowed Books</h1>
            <p class="welcome">Manage currently borrowed books. Click <strong>Edit</strong> to change status or return a book.</p>
        </div>
        <div class="left-actions">
            <button class="btn add" id="addBtn">Add Borrow</button>
        </div>
    </div>

    <table id="borrowedTable">
        <thead>
            <tr>
                <th>Reg No.</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Book ID</th>
                <th>Book Name</th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($borrowed_books as $b): ?>
                <tr data-id="<?= htmlspecialchars($b['Reg_no']) ?>">
                    <td class="c-reg-no"><?= htmlspecialchars($b['Reg_no']) ?></td>
                    <td class="c-student-id"><?= htmlspecialchars($b['Student_Id']) ?></td>
                    <td class="c-student-name"><?= htmlspecialchars($b['Student_Name']) ?></td>
                    <td class="c-book-id"><?= htmlspecialchars($b['Book_Id']) ?></td>
                    <td class="c-book-name"><?= htmlspecialchars($b['Book_Name']) ?></td>
                    <td class="c-issue-date"><?= htmlspecialchars($b['Issue_Date']) ?></td>
                    <td class="c-due-date"><?= htmlspecialchars($b['Due_Date']) ?></td>
                    <td class="c-status"><?= htmlspecialchars($b['Status']) ?></td>
                    <td class="actions">
                        <a href="#" class="edit btn small" onclick="startEdit(this);return false;">Edit</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this record?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="Reg_no" value="<?= htmlspecialchars($b['Reg_no']) ?>">
                            <button type="submit" class="btn delete small">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form id="submitForm" method="POST" style="display:none;">
        <input type="hidden" name="action" id="form_action">
        <input type="hidden" name="Reg_no" id="form_Reg_no">
        <input type="hidden" name="Student_Id" id="form_Student_Id">
        <input type="hidden" name="Book_Id" id="form_Book_Id">
        <input type="hidden" name="Due_Date" id="form_Due_Date">
        <input type="hidden" name="Status" id="form_Status">
    </form>
</div>

<script>
    // Handles inline editing and form submissions
    document.getElementById('addBtn').addEventListener('click', function() {
        if (document.querySelector('#borrowedTable tbody tr.adding')) return;
        const tbody = document.querySelector('#borrowedTable tbody');
        const tr = document.createElement('tr');
        tr.classList.add('form-row', 'adding');
        tr.innerHTML = `
            <td>Auto</td>
            <td><input class="inline" id="tmp_Student_Id" placeholder="Student ID"></td>
            <td>N/A</td>
            <td><input class="inline" id="tmp_Book_Id" placeholder="Book ID"></td>
            <td>N/A</td>
            <td>Auto</td>
            <td><input class="inline" type="date" id="tmp_Due_Date" placeholder="YYYY-MM-DD"></td>
            <td>Not Returned</td>
            <td>
                <button class="btn save small" onclick="saveNew(this);return false;">Save</button>
                <button class="btn cancel small" onclick="cancelAdd(this);return false;">Cancel</button>
            </td>
        `;
        tbody.insertBefore(tr, tbody.firstChild);
        document.getElementById('tmp_Student_Id').focus();
    });

    function cancelAdd(btn) {
        btn.closest('tr').remove();
    }

    function saveNew(btn) {
        const tr = btn.closest('tr');
        const studentId = tr.querySelector('#tmp_Student_Id').value.trim();
        const bookId = tr.querySelector('#tmp_Book_Id').value.trim();
        const dueDate = tr.querySelector('#tmp_Due_Date').value.trim();

        if (!studentId || !bookId || !dueDate) {
            alert('Student ID, Book ID, and Due Date are required.');
            return;
        }

        document.getElementById('form_action').value = 'add';
        document.getElementById('form_Student_Id').value = studentId;
        document.getElementById('form_Book_Id').value = bookId;
        document.getElementById('form_Due_Date').value = dueDate;
        document.getElementById('submitForm').submit();
    }

    function startEdit(anchor) {
        const tr = anchor.closest('tr');
        if (tr.classList.contains('editing')) return;
        tr.classList.add('editing');

        tr.querySelector('.c-student-id').innerHTML = `<input class="inline" value="${escapeHtml(tr.querySelector('.c-student-id').textContent.trim())}">`;
        tr.querySelector('.c-book-id').innerHTML = `<input class="inline" value="${escapeHtml(tr.querySelector('.c-book-id').textContent.trim())}">`;
        
        // Replace Status with a dropdown menu
        const statusCell = tr.querySelector('.c-status');
        const currentStatus = statusCell.textContent.trim();
        statusCell.innerHTML = `
            <select class="inline" id="edit_Status">
                <option value="Not Returned" ${currentStatus === 'Not Returned' ? 'selected' : ''}>Not Returned</option>
                <option value="Returned" ${currentStatus === 'Returned' ? 'selected' : ''}>Returned</option>
            </select>
        `;

        const actions = tr.querySelector('.actions');
        actions.dataset.orig = actions.innerHTML;
        actions.innerHTML = `
            <a href="#" class="btn save small" onclick="saveEdit(this);return false;">Save</a>
            <a href="#" class="btn cancel small" onclick="cancelEdit(this);return false;">Cancel</a>
        `;
    }

    function cancelEdit(anchor) {
        window.location = window.location.href.split('#')[0];
    }

    function saveEdit(anchor) {
        const tr = anchor.closest('tr');
        const regNo = tr.dataset.id; // Changed to Reg_no
        const studentId = tr.querySelector('.c-student-id input').value.trim();
        const bookId = tr.querySelector('.c-book-id input').value.trim();
        const status = tr.querySelector('.c-status select').value;

        document.getElementById('form_action').value = 'update';
        document.getElementById('form_Reg_no').value = regNo; // Changed to Reg_no
        document.getElementById('form_Student_Id').value = studentId;
        document.getElementById('form_Book_Id').value = bookId;
        document.getElementById('form_Status').value = status;
        document.getElementById('submitForm').submit();
    }

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
</script>

<?php
require_once 'templates/footer.php';
?>
