<?php
// books.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database and auth check file
require_once 'database.php';
checkAuth();

// --- Handle POST actions: add / update / delete ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action    = $_POST['action'];
    $book_id   = $_POST['Book_Id'] ?? '';
    $name      = $_POST['Name'] ?? '';
    $category  = $_POST['Category'] ?? '';
    $publisher = $_POST['Publisher'] ?? '';
    $isbn      = $_POST['ISBN'] ?? '';
    $author    = $_POST['Author'] ?? '';
    $price     = $_POST['Price'] ?? 0;

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO Book (Book_Id, Name, Category, Publisher, ISBN, Author, Price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssd", $book_id, $name, $category, $publisher, $isbn, $author, $price);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'update') {
        $stmt = $conn->prepare("UPDATE Book SET Name=?, Category=?, Publisher=?, ISBN=?, Author=?, Price=? WHERE Book_Id=?");
        $stmt->bind_param("ssssdss", $name, $category, $publisher, $isbn, $author, $price, $book_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM Book WHERE Book_Id=?");
        $stmt->bind_param("s", $book_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Redirect to prevent form resubmission
    header("Location: books.php");
    exit();
}

// --- Fetch all books ---
$books = [];
$res = $conn->query("SELECT * FROM Book ORDER BY Book_Id ASC");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $books[] = $r;
    }
    $res->free();
}

$conn->close();

// Include the header template
require_once 'templates/header.php';
?>

<div class="main">
    <div class="top-actions">
        <div>
            <h1>Books</h1>
            <p class="welcome">Manage book records. Inline edit: click <strong>Edit</strong> on a row to change fields and then <strong>Save</strong>.</p>
        </div>
        <div class="left-actions">
            <button class="btn add" id="addBtn">Add Book</button>
        </div>
    </div>

    <table id="booksTable">
        <thead>
            <tr>
                <th>Book ID</th><th>Name</th><th>Category</th><th>Publisher</th>
                <th>ISBN</th><th>Author</th><th>Price</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $b): ?>
                <tr data-id="<?= htmlspecialchars($b['Book_Id']) ?>">
                    <td class="c-id"><?= htmlspecialchars($b['Book_Id']) ?></td>
                    <td class="c-name"><?= htmlspecialchars($b['Name']) ?></td>
                    <td class="c-cat"><?= htmlspecialchars($b['Category']) ?></td>
                    <td class="c-pub"><?= htmlspecialchars($b['Publisher']) ?></td>
                    <td class="c-isbn"><?= htmlspecialchars($b['ISBN']) ?></td>
                    <td class="c-author"><?= htmlspecialchars($b['Author']) ?></td>
                    <td class="c-price"><?= htmlspecialchars($b['Price']) ?></td>
                    <td class="actions">
                        <a href="#" class="edit btn small" onclick="startEdit(this);return false;">Edit</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this book?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="Book_Id" value="<?= htmlspecialchars($b['Book_Id']) ?>">
                            <button type="submit" class="btn delete small">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <form id="submitForm" method="POST" style="display:none;">
        <input type="hidden" name="action" id="form_action">
        <input type="hidden" name="Book_Id" id="form_Book_Id">
        <input type="hidden" name="Name" id="form_Name">
        <input type="hidden" name="Category" id="form_Category">
        <input type="hidden" name="Publisher" id="form_Publisher">
        <input type="hidden" name="ISBN" id="form_ISBN">
        <input type="hidden" name="Author" id="form_Author">
        <input type="hidden" name="Price" id="form_Price">
    </form>
</div>

<script>
    // All the inline JavaScript for CRUD functionality on the books table
    document.getElementById('addBtn').addEventListener('click', function(){
        if (document.querySelector('#booksTable tbody tr.adding')) return;
        const tbody = document.querySelector('#booksTable tbody');
        const tr = document.createElement('tr');
        tr.classList.add('form-row','adding');
        tr.innerHTML = `
            <td><input class="inline" id="tmp_Book_Id" placeholder="Book ID"></td>
            <td><input class="inline" id="tmp_Name" placeholder="Book Name"></td>
            <td><input class="inline" id="tmp_Category" placeholder="Category"></td>
            <td><input class="inline" id="tmp_Publisher" placeholder="Publisher"></td>
            <td><input class="inline" id="tmp_ISBN" placeholder="ISBN"></td>
            <td><input class="inline" id="tmp_Author" placeholder="Author"></td>
            <td><input class="inline" type="number" id="tmp_Price" placeholder="Price" step="0.01"></td>
            <td>
                <button class="btn save small" onclick="saveNew(this);return false;">Save</button>
                <button class="btn cancel small" onclick="cancelAdd(this);return false;">Cancel</button>
            </td>
        `;
        tbody.insertBefore(tr, tbody.firstChild);
        document.getElementById('tmp_Book_Id').focus();
    });

    function cancelAdd(btn) {
        const tr = btn.closest('tr');
        tr.remove();
    }

    function saveNew(btn) {
        const tr = btn.closest('tr');
        const bookId = tr.querySelector('#tmp_Book_Id').value.trim();
        if (!bookId) { alert('Book ID is required'); return; }

        document.getElementById('form_action').value = 'add';
        document.getElementById('form_Book_Id').value = bookId;
        document.getElementById('form_Name').value = tr.querySelector('#tmp_Name').value.trim();
        document.getElementById('form_Category').value = tr.querySelector('#tmp_Category').value.trim();
        document.getElementById('form_Publisher').value = tr.querySelector('#tmp_Publisher').value.trim();
        document.getElementById('form_ISBN').value = tr.querySelector('#tmp_ISBN').value.trim();
        document.getElementById('form_Author').value = tr.querySelector('#tmp_Author').value.trim();
        document.getElementById('form_Price').value = tr.querySelector('#tmp_Price').value || 0;

        document.getElementById('submitForm').submit();
    }

    // Start inline edit for existing row
    function startEdit(anchor) {
        const tr = anchor.closest('tr');
        if (tr.classList.contains('editing')) return;
        tr.classList.add('editing');

        const idCell = tr.querySelector('.c-id');
        const idVal = idCell.textContent.trim();

        const nameCell = tr.querySelector('.c-name'); nameCell.innerHTML = `<input class="inline" value="${escapeHtml(nameCell.textContent.trim())}">`;
        const catCell = tr.querySelector('.c-cat'); catCell.innerHTML = `<input class="inline" value="${escapeHtml(catCell.textContent.trim())}">`;
        const pubCell = tr.querySelector('.c-pub'); pubCell.innerHTML = `<input class="inline" value="${escapeHtml(pubCell.textContent.trim())}">`;
        const isbnCell = tr.querySelector('.c-isbn'); isbnCell.innerHTML = `<input class="inline" value="${escapeHtml(isbnCell.textContent.trim())}">`;
        const authorCell = tr.querySelector('.c-author'); authorCell.innerHTML = `<input class="inline" value="${escapeHtml(authorCell.textContent.trim())}">`;
        const priceCell = tr.querySelector('.c-price'); priceCell.innerHTML = `<input class="inline" type="number" step="0.01" value="${escapeHtml(priceCell.textContent.trim())}">`;

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
        const id = tr.dataset.id;
        const name = tr.querySelector('.c-name input').value.trim();
        const category = tr.querySelector('.c-cat input').value.trim();
        const publisher = tr.querySelector('.c-pub input').value.trim();
        const isbn = tr.querySelector('.c-isbn input').value.trim();
        const author = tr.querySelector('.c-author input').value.trim();
        const price = tr.querySelector('.c-price input').value || 0;

        document.getElementById('form_action').value = 'update';
        document.getElementById('form_Book_Id').value = id;
        document.getElementById('form_Name').value = name;
        document.getElementById('form_Category').value = category;
        document.getElementById('form_Publisher').value = publisher;
        document.getElementById('form_ISBN').value = isbn;
        document.getElementById('form_Author').value = author;
        document.getElementById('form_Price').value = price;

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
