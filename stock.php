<?php 
include 'includes/auth.php'; 
include 'includes/db.php';

// ✅ Session-based redirects (no JS!)
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['godown_id'])) {
    header("Location: dashboard.php"); // Redirect to dashboard if godown not selected
    exit;
}

$godown_id = $_SESSION['godown_id'];
$godown_name = "";
$stmtGodown = $conn->prepare("SELECT name FROM godowns WHERE id = ?");
$stmtGodown->bind_param("i", $godown_id);
$stmtGodown->execute();
$stmtGodown->bind_result($godown_name);
$stmtGodown->fetch();
$stmtGodown->close();

$message = "";

// ✅ Add New Stock Item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_stock'])) {
    $item_name = trim($_POST["item_name"]);
    $quantity = floatval($_POST["quantity"]);
    $unit = trim($_POST["unit"]);
    $min_quantity = floatval($_POST["min_quantity"]);

    if ($min_quantity > $quantity) {
        $message = "❌ Minimum quantity cannot be greater than actual quantity.";
    } else {
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM stock WHERE BINARY item_name = ? AND godown_id = ?");
        if ($stmtCheck) {
            $stmtCheck->bind_param("si", $item_name, $godown_id);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                $message = "❌ Item with name '$item_name' already exists in this godown.";
            } else {
                $stmt = $conn->prepare("INSERT INTO stock (item_name, quantity, unit, min_quantity, godown_id) VALUES (?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sdssi", $item_name, $quantity, $unit, $min_quantity, $godown_id);
                    $stmt->execute();

                    if ($stmt->affected_rows > 0) {
                        header("Location: " . $_SERVER['PHP_SELF'] . "?added=1");
                        exit;
                    } else {
                        $message = "❌ Failed to insert item. Please check inputs.";
                    }
                    $stmt->close();
                } else {
                    $message = "❌ Error preparing insert query: " . $conn->error;
                }
            }
        } else {
            $message = "❌ Error checking for duplicates: " . $conn->error;
        }
    }
}

// ✅ Update Stock Item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['id']);
    $item_name = $_POST["item_name"];
    $quantity = floatval($_POST["quantity"]);
    $min_quantity = floatval($_POST["min_quantity"]);

    if ($min_quantity > $quantity) {
        $message = "❌ Minimum quantity cannot be greater than actual quantity.";
    } else {
        $stmt = $conn->prepare("UPDATE stock SET item_name = ?, quantity = ?, min_quantity = ? WHERE id = ? AND godown_id = ?");
        $stmt->bind_param("sdsii", $item_name, $quantity, $min_quantity, $id, $godown_id);
        $stmt->execute();
        header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
        exit;
    }
}

// ✅ Delete Stock Item
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM stock WHERE id = ? AND godown_id = ?");
    $stmt->bind_param("ii", $id, $godown_id);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=1");
    exit;
}

// ✅ Fetch stock items for this godown
$stmtFetch = $conn->prepare("SELECT * FROM stock WHERE godown_id = ? ORDER BY item_name ASC");
$stmtFetch->bind_param("i", $godown_id);
$stmtFetch->execute();
$result = $stmtFetch->get_result();
$stockItems = $result->fetch_all(MYSQLI_ASSOC);
$stmtFetch->close();

$chartData = [];
?>





<!DOCTYPE html>
<html>
<head>
    <title>Manage Stock - Chamunda Mata Traders</title>
    <link rel="stylesheet" href="theme.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <style>
  :root {
    --primary: #2e7d32;
    --accent: #a5d6a7;
    --bg-light: #f1f8e9;
    --table-bg: #ffffff;
    --table-header: #66bb6a;
    --footer-bg: #1f5d23;
    --danger-light: #ffebee;
    --highlight: #fff9c4;
  }

  html, body {
    height: 100%;
    margin: 0;
  }

  body {
    font-family: 'Urbanist', 'Segoe UI', sans-serif;
    background-color: var(--bg-light);
    margin: 0;
    padding: 0;
  }

  .page-wrapper {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  main {
    flex: 1;
    padding-bottom: 30px;
  }

  h2, h3 {
    text-align: center;
    color: var(--primary);
    margin-top: 20px;
    font-weight: 700;
  }

  input[readonly] {
    background-color: #e8f5e9;
  }

  .highlight {
    background-color: var(--danger-light);
  }

  .search-box {
    width: 60%;
    margin: 20px auto;
  }

  #searchResult {
    background: var(--highlight);
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    margin: 20px auto;
    width: 80%;
    display: none;
    color: #333;
  }

  table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: var(--table-bg);
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    border-radius: 8px;
    overflow: hidden;
  }

  th {
    background-color: var(--table-header);
    color: white;
    padding: 12px 10px;
    text-align: center;
    font-weight: 600;
  }

  td {
    padding: 10px 12px;
    text-align: center;
    border-bottom: 1px solid #ddd;
  }

  tr:nth-child(even) {
    background-color: #f9fbe7;
  }

  tr:hover {
    background-color: #e0f2f1;
  }

  #stockChart {
    max-width: 350px;
    margin: 0 auto;
  }

  .toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
    background-color: #4caf50;
    color: white;
    border-radius: 8px;
    padding: 10px 15px;
  }

  footer {
    background-color: var(--footer-bg);
    color: white;
    text-align: center;
    font-size: 13px;
    padding: 12px 12px;
    margin-top: auto;
  }

  footer a {
    color: white;
    text-decoration: none;
  }

  /* Responsive Tweaks */
  @media (max-width: 768px) {
    .search-box {
      width: 90%;
    }

    #searchResult {
      width: 90%;
    }

    table {
      font-size: 14px;
      width: 100%;
    }

    td, th {
      padding: 8px 6px;
    }

    #stockChart {
      max-width: 100%;
    }
  }

  @media (max-width: 576px) {
    h2, h3 {
      font-size: 1.3rem;
    }

    .toast {
      width: 90%;
      left: 5%;
      right: 5%;
      font-size: 0.9rem;
    }
  }
</style>



</head>
<body>
    <div class="page-wrapper">
<main>
<!-- Toasts -->
<div class="toast align-items-center text-white bg-success border-0" id="addedToast" role="alert" aria-live="assertive" aria-atomic="true">
  <div class="d-flex">
    <div class="toast-body">✅ Stock item added successfully!</div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
  </div>
</div>

<div class="toast align-items-center text-white bg-info border-0" id="updatedToast" role="alert" aria-live="assertive" aria-atomic="true">
  <div class="d-flex">
    <div class="toast-body">✏️ Stock item updated successfully!</div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
  </div>
</div>

<div class="toast align-items-center text-white bg-danger border-0" id="deletedToast" role="alert" aria-live="assertive" aria-atomic="true">
  <div class="d-flex">
    <div class="toast-body">🗑️ Stock item deleted successfully!</div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
  </div>
</div>

<h2>Stock Management - <?= htmlspecialchars($godown_name) ?></h2>

<?php if (isset($message)) echo "<p class='text-center text-danger fw-bold'>$message</p>"; ?>

<div class="search-box">
    <input class="form-control" type="text" id="searchInput" placeholder="Search stock item by name...">
</div>

<div id="searchResult"></div>

<h3>Add New Stock Item</h3>
<form method="POST" id="addStockForm" class="container border rounded p-4 bg-white" style="max-width: 600px;" autocomplete="off">
    <input type="hidden" name="add_stock" value="1">
    <div class="mb-3">
        <label class="form-label">Item Name:</label>
        <input type="text" name="item_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Quantity:</label>
        <input type="number" step="0.01" name="quantity" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Unit:</label>
        <input type="text" name="unit" value="kg" class="form-control" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Minimum Quantity:</label>
        <input type="number" step="0.01" name="min_quantity" class="form-control" required>
    </div>
    <input type="submit" value="Add Stock" class="btn btn-primary w-100">
</form>

<h3>Current Stock</h3>

<?php if (empty($stockItems)): ?>
    <div class="text-center mt-4"><strong>No items in stock.</strong></div>
<?php else: ?>
<table class="table table-bordered table-striped table-hover">
    <thead class="table-primary">
    <tr>
        <th>Item</th>
        <th>Quantity</th>
        <th>Unit</th>
        <th>Min Level</th>
        <th>Last Updated</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody id="stockTable">
    <?php foreach ($stockItems as $row): ?>
        <form method="POST">
            <tr id="row-<?= $row['id'] ?>" class="<?= $row['quantity'] < $row['min_quantity'] ? 'highlight' : '' ?>">
                <td>
                    <input class="form-control" type="text" name="item_name" value="<?= htmlspecialchars($row['item_name']) ?>" readonly required>
                    <?php if ($row['quantity'] <= $row['min_quantity']): ?>
                        <div class="text-danger fw-bold small">⚠️ Low Stock! Please Refill</div>
                    <?php endif; ?>
                </td>
                <td><input class="form-control" type="number" name="quantity" value="<?= $row['quantity'] ?>" step="0.01" readonly required></td>
                <td><input class="form-control" type="text" name="unit" value="<?= $row['unit'] ?>" readonly></td>
                <td><input class="form-control" type="number" name="min_quantity" value="<?= $row['min_quantity'] ?>" step="0.01" readonly required></td>
                <td><?= $row['last_updated'] ?></td>
                <td>
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <button type="button" class="btn btn-sm btn-warning" id="edit-btn-<?= $row['id'] ?>" onclick="enableEdit(<?= $row['id'] ?>)">Edit</button>
                    <button type="submit" name="action" value="update" class="btn btn-sm btn-success" id="update-btn-<?= $row['id'] ?>" style="display:none;">Update</button>
                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this item?');">Delete</button>
                </td>
            </tr>
        </form>
        <?php $chartData[] = ['item' => $row['item_name'], 'qty' => $row['quantity']]; ?>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Pagination -->
<nav aria-label="Page navigation" id="paginationNav">
    <ul class="pagination justify-content-center"></ul>
</nav>
<?php endif; ?>

<div class="container my-5">
    <h4 class="text-center">Stock Chart</h4>
    <div style="max-width: 350px; margin: auto;">
        <canvas id="stockChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function enableEdit(rowId) {
    const row = document.getElementById("row-" + rowId);
    row.querySelectorAll("input[type='text'], input[type='number']").forEach(input => {
        if (input.name !== 'unit') input.removeAttribute("readonly");
    });
    document.getElementById("edit-btn-" + rowId).style.display = "none";
    document.getElementById("update-btn-" + rowId).style.display = "inline";
}

document.getElementById("searchInput").addEventListener("input", function () {
    const filter = this.value.toLowerCase();
    let result = "";
    document.querySelectorAll("#stockTable tr").forEach(row => {
        const item = row.querySelector("input[name='item_name']").value.toLowerCase();
        if (item.includes(filter)) {
            row.style.display = "";
            const qty = row.querySelector("input[name='quantity']").value;
            const unit = row.querySelector("input[name='unit']").value;
            result = `<h5>🔍 Search Result:</h5><strong>${item}</strong>: ${qty} ${unit}`;
        } else {
            row.style.display = "none";
        }
    });
    const box = document.getElementById("searchResult");
    if (filter && result) {
        box.innerHTML = result;
        box.style.display = "block";
    } else {
        box.style.display = "none";
    }
});

const ctx = document.getElementById('stockChart').getContext('2d');
const chartData = <?= json_encode($chartData); ?>;
const itemNames = chartData.map(item => item.item);
const itemQtys = chartData.map(item => item.qty);

new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: itemNames,
        datasets: [{
            label: 'Stock Quantity',
            data: itemQtys,
            backgroundColor: ['#4dc9f6','#f67019','#f53794','#537bc4','#acc236','#166a8f','#00a950','#58595b','#8549ba']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Show toasts
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('added') === '1') {
    document.querySelector("#addStockForm").reset();
    new bootstrap.Toast(document.getElementById('addedToast')).show();
} else if (urlParams.get('updated') === '1') {
    new bootstrap.Toast(document.getElementById('updatedToast')).show();
} else if (urlParams.get('deleted') === '1') {
    new bootstrap.Toast(document.getElementById('deletedToast')).show();
}
if (urlParams.has('added') || urlParams.has('updated') || urlParams.has('deleted')) {
    history.replaceState(null, '', window.location.pathname);
}



// Pagination
const rowsPerPage = 10;
const table = document.getElementById("stockTable");
if (table) {
    const rows = table.querySelectorAll("tr");
    const totalPages = Math.ceil(rows.length / rowsPerPage);
    const paginationNav = document.getElementById("paginationNav").querySelector(".pagination");

    function displayPage(page) {
        rows.forEach((row, index) => {
            row.style.display = (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) ? "" : "none";
        });
        paginationNav.innerHTML = "";
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement("li");
            li.className = "page-item" + (i === page ? " active" : "");
            const link = document.createElement("a");
            link.className = "page-link";
            link.href = "#";
            link.textContent = i;
            link.onclick = (e) => {
                e.preventDefault();
                displayPage(i);
            };
            li.appendChild(link);
            paginationNav.appendChild(li);
        }
    }

    if (rows.length > 0) displayPage(1);
}
</script>
<script>
window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
        window.location.replace("dashboard.php");
    }
});
</script>

   
</main>
<footer>
  <p>&copy; 2025 Chamunda Mata Traders.<br> Built by Kandarp Patil | 📧 <a href="mailto:kandarppatil2@gmail.com">kandarppatil2@gmail.com</a></p>
</footer>
</div>
</body>
</html>