<?php
// students.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database and auth check file
require_once 'database.php';
require_once 'templates/header.php';

// --- Handle POST actions: add / update / delete ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Collect all student data from the form
    $student_id = $_POST['Student_Id'] ?? '';
    $name = $_POST['Name'] ?? '';
    $year = $_POST['Year'] ?? '';
    $department = $_POST['Department'] ?? '';
    $email = $_POST['Email'] ?? '';
    $phone_number = $_POST['Phone_Number'] ?? '';
    $dob = $_POST['DOB'] ?? null;

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO Student (Student_Id, Name, Year, Department, Email, Phone_Number, DOB) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $student_id, $name, $year, $department, $email, $phone_number, $dob);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'update') {
        $stmt = $conn->prepare("UPDATE Student SET Name=?, Year=?, Department=?, Email=?, Phone_Number=?, DOB=? WHERE Student_Id=?");
        $stmt->bind_param("sssssss", $name, $year, $department, $email, $phone_number, $dob, $student_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM Student WHERE Student_Id=?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to prevent form resubmission
    header("Location: students.php");
    exit();
}

// --- Fetch all students ---
$students = [];
$res = $conn->query("SELECT * FROM Student ORDER BY Student_Id ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $students[] = $r;
    }
    $res->free();
}

$conn->close();
?>

<div class="main">
    <div class="top-actions">
        <div>
            <h1>Students</h1>
            <p class="welcome">Manage student records. Inline edit: click <strong>Edit</strong> on a row to change fields and then <strong>Save</strong>.</p>
        </div>
        <div class="left-actions">
            <button class="btn add" id="addBtn">Add Student</button>
        </div>
    </div>

    <table id="studentsTable">
        <thead>
            <tr>
                <th>Student ID</th><th>Name</th><th>Year</th><th>Department</th>
                <th>Email</th><th>Phone</th><th>DOB</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
                <tr data-id="<?= htmlspecialchars($s['Student_Id']) ?>">
                    <td class="c-id"><?= htmlspecialchars($s['Student_Id']) ?></td>
                    <td class="c-name"><?= htmlspecialchars($s['Name']) ?></td>
                    <td class="c-year"><?= htmlspecialchars($s['Year']) ?></td>
                    <td class="c-dept"><?= htmlspecialchars($s['Department']) ?></td>
                    <td class="c-email"><?= htmlspecialchars($s['Email']) ?></td>
                    <td class="c-phone"><?= htmlspecialchars($s['Phone_Number']) ?></td>
                    <td class="c-dob"><?= htmlspecialchars($s['DOB']) ?></td>
                    <td class="actions">
                        <a href="#" class="edit btn small" onclick="startEdit(this);return false;">Edit</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this student?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="Student_Id" value="<?= htmlspecialchars($s['Student_Id']) ?>">
                            <button type="submit" class="btn delete small">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form id="submitForm" method="POST" style="display:none;">
        <input type="hidden" name="action" id="form_action">
        <input type="hidden" name="Student_Id" id="form_Student_Id">
        <input type="hidden" name="Name" id="form_Name">
        <input type="hidden" name="Year" id="form_Year">
        <input type="hidden" name="Department" id="form_Department">
        <input type="hidden" name="Email" id="form_Email">
        <input type="hidden" name="Phone_Number" id="form_Phone_Number">
        <input type="hidden" name="DOB" id="form_DOB">
    </form>
</div>

<script>
    // All the inline JavaScript for CRUD functionality on the students table
    document.getElementById('addBtn').addEventListener('click', function(){
        if (document.querySelector('#studentsTable tbody tr.adding')) return;
        const tbody = document.querySelector('#studentsTable tbody');
        const tr = document.createElement('tr');
        tr.classList.add('form-row', 'adding');
        tr.innerHTML = `
            <td><input class="inline" name="Student_Id" id="tmp_Student_Id" placeholder="Student ID"></td>
            <td><input class="inline" name="Name" id="tmp_Name" placeholder="Full name"></td>
            <td><input class="inline" name="Year" id="tmp_Year" placeholder="SY/TY"></td>
            <td><input class="inline" name="Department" id="tmp_Department" placeholder="Department"></td>
            <td><input class="inline" name="Email" id="tmp_Email" placeholder="email@domain.com"></td>
            <td><input class="inline" name="Phone_Number" id="tmp_Phone_Number" placeholder="Phone"></td>
            <td><input class="inline" type="date" name="DOB" id="tmp_DOB"></td>
            <td>
                <button class="btn save small" onclick="saveNew(this);return false;">Save</button>
                <button class="btn cancel small" onclick="cancelAdd(this);return false;">Cancel</button>
            </td>
        `;
        tbody.insertBefore(tr, tbody.firstChild);
        document.getElementById('tmp_Student_Id').focus();
    });

    function cancelAdd(btn) {
        const tr = btn.closest('tr');
        tr.remove();
    }

    function saveNew(btn) {
        const tr = btn.closest('tr');
        const id = tr.querySelector('#tmp_Student_Id').value.trim();
        if(!id){ alert('Student ID is required'); return; }

        document.getElementById('form_action').value = 'add';
        document.getElementById('form_Student_Id').value = id;
        document.getElementById('form_Name').value = tr.querySelector('#tmp_Name').value.trim();
        document.getElementById('form_Year').value = tr.querySelector('#tmp_Year').value.trim();
        document.getElementById('form_Department').value = tr.querySelector('#tmp_Department').value.trim();
        document.getElementById('form_Email').value = tr.querySelector('#tmp_Email').value.trim();
        document.getElementById('form_Phone_Number').value = tr.querySelector('#tmp_Phone_Number').value.trim();
        document.getElementById('form_DOB').value = tr.querySelector('#tmp_DOB').value || '';
        document.getElementById('submitForm').submit();
    }

    function startEdit(anchor){
        const tr = anchor.closest('tr');
        if (tr.classList.contains('editing')) return;
        tr.classList.add('editing');

        const idCell = tr.querySelector('.c-id');
        const idVal = idCell.textContent.trim();

        const nameCell = tr.querySelector('.c-name'); nameCell.innerHTML = `<input class="inline" value="${escapeHtml(nameCell.textContent.trim())}">`;
        const yearCell = tr.querySelector('.c-year'); yearCell.innerHTML = `<input class="inline" value="${escapeHtml(yearCell.textContent.trim())}">`;
        const deptCell = tr.querySelector('.c-dept'); deptCell.innerHTML = `<input class="inline" value="${escapeHtml(deptCell.textContent.trim())}">`;
        const emailCell = tr.querySelector('.c-email'); emailCell.innerHTML = `<input class="inline" value="${escapeHtml(emailCell.textContent.trim())}">`;
        const phoneCell = tr.querySelector('.c-phone'); phoneCell.innerHTML = `<input class="inline" value="${escapeHtml(phoneCell.textContent.trim())}">`;
        const dobCell = tr.querySelector('.c-dob'); dobCell.innerHTML = `<input class="inline" type="date" value="${dobCell.textContent.trim()}">`;

        const actions = tr.querySelector('.actions');
        actions.dataset.orig = actions.innerHTML;
        actions.innerHTML = `
            <a href="#" class="btn save small" onclick="saveEdit(this);return false;">Save</a>
            <a href="#" class="btn cancel small" onclick="cancelEdit(this);return false;">Cancel</a>
        `;
    }

    function cancelEdit(anchor){
        window.location = window.location.href.split('#')[0];
    }

    function saveEdit(anchor){
        const tr = anchor.closest('tr');
        const id = tr.dataset.id;
        const name = tr.querySelector('.c-name input').value.trim();
        const year = tr.querySelector('.c-year input').value.trim();
        const dept = tr.querySelector('.c-dept input').value.trim();
        const email = tr.querySelector('.c-email input').value.trim();
        const phone = tr.querySelector('.c-phone input').value.trim();
        const dob = tr.querySelector('.c-dob input').value || '';

        document.getElementById('form_action').value = 'update';
        document.getElementById('form_Student_Id').value = id;
        document.getElementById('form_Name').value = name;
        document.getElementById('form_Year').value = year;
        document.getElementById('form_Department').value = dept;
        document.getElementById('form_Email').value = email;
        document.getElementById('form_Phone_Number').value = phone;
        document.getElementById('form_DOB').value = dob;

        document.getElementById('submitForm').submit();
    }

    function escapeHtml(str) {
        return str.replace(/&/g,'&amp;')
                  .replace(/"/g,'&quot;')
                  .replace(/'/g,'&#039;')
                  .replace(/</g,'&lt;')
                  .replace(/>/g,'&gt;');
    }
</script>

<?php
// Include the footer template
require_once 'templates/footer.php';
?>
