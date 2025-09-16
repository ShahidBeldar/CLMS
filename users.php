<?php
// users.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection and authentication file
require_once 'database.php';
require_once 'templates/header.php'; // Includes session_start() and auth check

// --- Authorization Check ---
if ($role !== "Admin") {
    // Redirect non-admin users
    header("Location: dashboard.php");
    exit();
}

// --- Handle POST actions: add / update / delete ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $user_id = $_POST['user_Id'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'Staff';

        $stmt = $conn->prepare("INSERT INTO User (user_Id, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user_id, $password, $role);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'update') {
        $user_id = $_POST['user_Id'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'Staff';
        
        $stmt = $conn->prepare("UPDATE User SET password=?, role=? WHERE user_Id=?");
        $stmt->bind_param("sss", $password, $role, $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete') {
        $user_id = $_POST['user_Id'] ?? '';
        // Prevent deleting the currently logged-in user
        if ($user_id !== $_SESSION['user_Id']) {
            $stmt = $conn->prepare("DELETE FROM User WHERE user_Id=?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: users.php");
    exit();
}

// --- Fetch all users ---
$users = [];
$res = $conn->query("SELECT user_Id, password, role FROM User ORDER BY user_Id ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $users[] = $r;
    }
    $res->free();
}
$conn->close();
?>

<div class="main">
    <div class="top-actions">
        <div>
            <h1>User Management</h1>
            <p class="welcome">Create and manage users. Only available to admins.</p>
        </div>
        <div class="left-actions">
            <button class="btn add" id="addBtn">Add User</button>
        </div>
    </div>

    <table id="usersTable">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Password</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr data-id="<?= htmlspecialchars($u['user_Id']) ?>">
                    <td class="c-user-id"><?= htmlspecialchars($u['user_Id']) ?></td>
                    <td class="c-password"><?= htmlspecialchars($u['password']) ?></td>
                    <td class="c-role"><?= htmlspecialchars($u['role']) ?></td>
                    <td class="actions">
                        <a href="#" class="edit btn small" onclick="startEdit(this);return false;">Edit</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_Id" value="<?= htmlspecialchars($u['user_Id']) ?>">
                            <button type="submit" class="btn delete small" <?= $u['user_Id'] === $_SESSION['user_Id'] ? 'disabled' : '' ?>>Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form id="submitForm" method="POST" style="display:none;">
        <input type="hidden" name="action" id="form_action">
        <input type="hidden" name="user_Id" id="form_user_Id">
        <input type="hidden" name="password" id="form_password">
        <input type="hidden" name="role" id="form_role">
    </form>
</div>

<script>
    // Inline editing and form submission logic
    document.getElementById('addBtn').addEventListener('click', function() {
        if (document.querySelector('#usersTable tbody tr.adding')) return;
        const tbody = document.querySelector('#usersTable tbody');
        const tr = document.createElement('tr');
        tr.classList.add('form-row', 'adding');
        tr.innerHTML = `
            <td><input class="inline" id="tmp_user_Id" placeholder="User ID"></td>
            <td><input class="inline" id="tmp_password" placeholder="Password"></td>
            <td>
                <select class="inline" id="tmp_role">
                    <option value="Admin">Admin</option>
                    <option value="Staff" selected>Staff</option>
                </select>
            </td>
            <td>
                <button class="btn save small" onclick="saveNew(this);return false;">Save</button>
                <button class="btn cancel small" onclick="cancelAdd(this);return false;">Cancel</button>
            </td>
        `;
        tbody.insertBefore(tr, tbody.firstChild);
        document.getElementById('tmp_user_Id').focus();
    });

    function cancelAdd(btn) {
        btn.closest('tr').remove();
    }

    function saveNew(btn) {
        const tr = btn.closest('tr');
        const userId = tr.querySelector('#tmp_user_Id').value.trim();
        const password = tr.querySelector('#tmp_password').value.trim();

        if (!userId || !password) {
            alert('User ID and Password are required.');
            return;
        }

        document.getElementById('form_action').value = 'add';
        document.getElementById('form_user_Id').value = userId;
        document.getElementById('form_password').value = password;
        document.getElementById('form_role').value = tr.querySelector('#tmp_role').value;
        document.getElementById('submitForm').submit();
    }

    function startEdit(anchor) {
        const tr = anchor.closest('tr');
        if (tr.classList.contains('editing')) return;
        tr.classList.add('editing');

        tr.querySelector('.c-password').innerHTML = `<input class="inline" value="${escapeHtml(tr.querySelector('.c-password').textContent.trim())}">`;
        
        const roleCell = tr.querySelector('.c-role');
        const currentRole = roleCell.textContent.trim();
        roleCell.innerHTML = `
            <select class="inline" id="edit_role">
                <option value="Admin" ${currentRole === 'Admin' ? 'selected' : ''}>Admin</option>
                <option value="Staff" ${currentRole === 'Staff' ? 'selected' : ''}>Staff</option>
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
        const userId = tr.dataset.id;
        const password = tr.querySelector('.c-password input').value.trim();
        const role = tr.querySelector('.c-role select').value;

        document.getElementById('form_action').value = 'update';
        document.getElementById('form_user_Id').value = userId;
        document.getElementById('form_password').value = password;
        document.getElementById('form_role').value = role;
        document.getElementById('submitForm').submit();
    }

    function escapeHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
</script>

<?php
require_once 'templates/footer.php';
?>
