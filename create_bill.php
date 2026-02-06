<?php 
session_start();
include 'includes/auth.php'; 
include 'includes/db.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['godown_id'])) {
    header("Location: select_godown.php");
    exit();
}
$godown_id = $_SESSION['godown_id'];

// Fetch stock items
$stockItems = [];
$stmt = $conn->prepare("SELECT id, item_name, quantity, unit FROM stock WHERE godown_id = ?");
$stmt->bind_param("i", $godown_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $stockItems[] = $row;
$stmt->close();

// Toast messages
$toastMessages = $_SESSION['toast_messages'] ?? [];
$successBillId = $_SESSION['bill_success'] ?? null;
unset($_SESSION['toast_messages'], $_SESSION['bill_success']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = trim($_POST['customer_name']);
    $items = $_POST['items'];
    $quantities = $_POST['quantities'];
    $prices = $_POST['prices'];
    $amount_paid = floatval($_POST['amount_paid']);
    $due_date = $_POST['due_date'] ?? null;

    $errors = [];
    $insufficientItems = [];

    foreach ($items as $index => $item_id) {
        $qty = (float)$quantities[$index];
        $price = (float)$prices[$index];

        if ($qty <= 0 || $price <= 0) {
            $errors[] = "Quantity and price must be greater than zero.";
            continue;
        }

        $stmtCheck = $conn->prepare("SELECT item_name, quantity FROM stock WHERE id = ? AND godown_id = ?");
        $stmtCheck->bind_param("ii", $item_id, $godown_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $stockRow = $resultCheck->fetch_assoc();
        $stmtCheck->close();

        if (!$stockRow) {
            $errors[] = "Invalid item selected.";
            continue;
        }

        if ($qty > floatval($stockRow['quantity'])) {
            $insufficientItems[] = [
                'name' => $stockRow['item_name'],
                'available' => $stockRow['quantity'],
            ];
        }
    }

    // Calculate totals
    $total_amount = 0;
    $total_items = count($items);
    for ($i = 0; $i < $total_items; $i++) {
        $total_amount += (float)$quantities[$i] * (float)$prices[$i];
    }

    if ($amount_paid > $total_amount) {
        $errors[] = "Amount paid cannot be more than total amount.";
    }

    if (!empty($insufficientItems)) {
        foreach ($insufficientItems as $item) {
            $errors[] = "Available quantity of " . htmlspecialchars($item['name']) . " is: " . $item['available'];
        }
    }

    $amount_remaining = $total_amount - $amount_paid;

    if ($amount_remaining > 0 && empty($due_date)) {
        $errors[] = "Please enter a due date if payment is partial.";
    }

    if (!empty($errors)) {
        $_SESSION['toast_messages'] = $errors;
        header("Location: create_bill.php");
        exit();
    }

    // ✅ Insert into invoices
 if ($amount_remaining == 0) {
    // Full payment
    $stmt = $conn->prepare("INSERT INTO invoices (
        customer_name, bill_date, total_items, total_amount,
        amount_paid, initial_paid, amount_remaining, payment_date, godown_id
    ) VALUES (?, NOW(), ?, ?, ?, ?, 0, NOW(), ?)");

    if (!$stmt) {
        die("Prepare failed (full payment): " . $conn->error);
    }

    $stmt->bind_param("sidddi", 
        $customer_name, 
        $total_items, 
        $total_amount, 
        $amount_paid, 
        $amount_paid, 
        $godown_id
    );

    $stmt->execute();
    $bill_id = $conn->insert_id;
    $stmt->close();

} else {
    // Partial payment
    $stmt = $conn->prepare("INSERT INTO invoices (
        customer_name, bill_date, total_items, total_amount,
        amount_paid, initial_paid, amount_remaining, due_date, godown_id
    ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Prepare failed (partial payment): " . $conn->error);
    }

    $stmt->bind_param("sidddssi", 
        $customer_name, 
        $total_items, 
        $total_amount, 
        $amount_paid, 
        $amount_paid, 
        $amount_remaining, 
        $due_date, 
        $godown_id
    );

    $stmt->execute();
    $bill_id = $conn->insert_id;
    $stmt->close();
}



  

    // ✅ Insert into payment_log
    $payment_type = ($amount_remaining == 0) ? 'full' : 'partial';
    $stmtLog = $conn->prepare("INSERT INTO payment_log (invoice_id, customer_name, amount, payment_type, payment_date, godown_id) VALUES (?, ?, ?, ?, NOW(), ?)");
    $stmtLog->bind_param("isdsi", $bill_id, $customer_name, $amount_paid, $payment_type, $godown_id);
    $stmtLog->execute();
    $stmtLog->close();

    // ✅ Insert invoice items
    $stmtItem = $conn->prepare("INSERT INTO invoice_items (invoice_id, stock_id, quantity, price) VALUES (?, ?, ?, ?)");
    for ($i = 0; $i < count($items); $i++) {
        $item_id = (int)$items[$i];
        $qty = (float)$quantities[$i];
        $price = (float)$prices[$i];
        $stmtItem->bind_param("iiid", $bill_id, $item_id, $qty, $price);
        $stmtItem->execute();
    }
    $stmtItem->close();

    $_SESSION['bill_success'] = $bill_id;
    header("Location: create_bill.php");
    exit();
}
?>





<!-- HTML & UI Section Starts -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>🧾 Create Bill</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="theme.css">

  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
  :root {
    --primary: #4CAF50;
    --danger: #e74c3c;
    --bg: #f4f6f8;
  }

  body {
    font-family: 'Segoe UI', sans-serif;
    background: #f1f8e9;
    padding: 30px 10px;
    margin: 0;
    display: flex;
    justify-content: center;
  }

  form {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 850px;
  }

  h2 {
    text-align: center;
    color: #1b5e20;
  }

  label {
    font-weight: 600;
    display: block;
    margin-top: 10px;
  }

  input,
  select {
    padding: 10px;
    width: 100%;
    margin-top: 5px;
    border-radius: 8px;
    border: 1px solid #ccc;
  }

  .item-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
  }

  .item-row input,
  .item-row select {
    flex: 1;
  }

  .item-row button {
    background: var(--danger);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px;
    cursor: pointer;
    flex-shrink: 0;
  }

  .item-row button:hover {
    background: #c0392b;
  }

  .add-btn,
  input[type="submit"] {
    background: #1b5e20;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    margin-top: 20px;
    cursor: pointer;
  }

  #toast {
    visibility: hidden;
    min-width: 300px;
    background-color: darkgreen;
    color: white;
    text-align: center;
    border-radius: 8px;
    padding: 16px;
    position: fixed;
    z-index: 9999;
    top: 20px;
    right: 20px;
  }

  #toast.show {
    visibility: visible;
    animation: fadein 0.5s, fadeout 0.5s 3s;
  }

  @keyframes fadein {
    from {
      top: 0;
      opacity: 0;
    }

    to {
      top: 20px;
      opacity: 1;
    }
  }

  @keyframes fadeout {
    from {
      top: 20px;
      opacity: 1;
    }

    to {
      top: 0;
      opacity: 0;
    }
  }

  /* ✅ Fully responsive layout */
  @media (max-width: 768px) {
    .item-row {
      flex-direction: column;
    }

    .item-row input,
    .item-row select,
    .item-row button {
      width: 100%;
    }

    .item-row button {
      margin-top: 10px;
    }
  }
</style>

</head>
<body>

<form method="post" data-aos="fade-up">
  <h2>🧾 Create Bill</h2>

  <label>Customer Name:</label>
  <input type="text" name="customer_name" required>

  <div id="items-container">
    <div class="item-row" data-aos="fade-right">
      <select name="items[]" required>
        <option value="">Select Item</option>
        <?php foreach ($stockItems as $item): ?>
          <option value="<?= $item['id'] ?>">
            <?= htmlspecialchars($item['item_name']) ?> (Available: <?= $item['quantity'] . ' ' . $item['unit'] ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <input type="number" name="quantities[]" placeholder="Qty" step="0.01" required>
      <input type="number" name="prices[]" placeholder="Unit Price" step="0.01" required>
      <input type="number" class="total-price" placeholder="Total" readonly>
      <button type="button" onclick="removeItem(this)">❌</button>
    </div>
  </div>

  <button type="button" class="add-btn" onclick="addItem()">➕ Add Item</button>

  <label>Total Bill:</label>
  <input type="number" id="totalBill" readonly>

  <label>Amount Paid:</label>
  <input type="number" name="amount_paid" id="amountPaid" step="0.01" required>

  <label>Remaining Amount:</label>
  <input type="number" id="remainingAmount" readonly>

  <div id="dueDateSection" style="display:none;">
    <label>Due Date:</label>
    <input type="date" name="due_date" id="dueDateInput" min="<?= date('Y-m-d') ?>">
  </div>

  <input type="submit" value="✅ Create Bill">
</form>

<div id="toast"></div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 800, once: true });</script>

<script>
function updateTotals() {
  let totalBill = 0;
  document.querySelectorAll('.item-row').forEach(row => {
    const qty = parseFloat(row.querySelector('input[name="quantities[]"]').value) || 0;
    const price = parseFloat(row.querySelector('input[name="prices[]"]').value) || 0;
    const total = qty * price;
    row.querySelector('.total-price').value = total.toFixed(2);
    totalBill += total;
  });

  document.getElementById('totalBill').value = totalBill.toFixed(2);
  const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
  const remaining = Math.max(0, totalBill - paid);
  document.getElementById('remainingAmount').value = remaining.toFixed(2);

  const dueDateSection = document.getElementById('dueDateSection');
  dueDateSection.style.display = paid < totalBill ? 'block' : 'none';
}

function addItem() {
  const row = document.querySelector('.item-row').cloneNode(true);
  row.querySelectorAll('input').forEach(i => i.value = '');
  document.getElementById('items-container').appendChild(row);
  attachEvents(row);
}

function removeItem(btn) {
  const container = document.getElementById('items-container');
  if (container.children.length > 1) {
    btn.parentElement.remove();
    updateTotals();
  }
}

function attachEvents(row) {
  row.querySelectorAll('input[name="quantities[]"], input[name="prices[]"]').forEach(i =>
    i.addEventListener('input', updateTotals)
  );
}
document.querySelectorAll('.item-row').forEach(attachEvents);
document.getElementById('amountPaid').addEventListener('input', updateTotals);
updateTotals();

function showToast(msg) {
  const toast = document.getElementById("toast");
  toast.textContent = msg;
  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 3000);
}

<?php if (!empty($toastMessages)): ?>
window.onload = function() {
  <?php foreach ($toastMessages as $msg): ?>
    showToast(<?= json_encode($msg) ?>);
  <?php endforeach; ?>
};
<?php elseif ($successBillId): ?>
window.onload = function() {
  showToast("✅ Bill generated successfully! Redirecting...");
  setTimeout(() => {
    window.location.href = "view_bill.php?bill_id=<?= $successBillId ?>";
  }, 3000);
};
<?php endif; ?>

window.addEventListener('pageshow', function (event) {
  if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
    window.location.href = 'select_godown.php';
  }
});
</script>

</body>
</html>
