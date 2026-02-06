<?php
session_start();
include 'includes/auth.php';
include 'includes/db.php';

$toastMessage = $_SESSION['toast'] ?? '';
unset($_SESSION['toast']);

$godown_id = $_SESSION['godown_id'] ?? 0;

// ✅ Handle mark as paid using invoices table only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid_id'])) {
    $invoiceId = intval($_POST['mark_paid_id']);

    $stmtFetch = $conn->prepare("SELECT customer_name, amount_paid, amount_remaining FROM invoices WHERE id = ? AND godown_id = ?");
    $stmtFetch->bind_param("ii", $invoiceId, $godown_id);
    $stmtFetch->execute();
    $result = $stmtFetch->get_result();
    $invoice = $result->fetch_assoc();
    $stmtFetch->close();

    if ($invoice && $invoice['amount_remaining'] > 0) {
        $newAmountPaid = $invoice['amount_paid'] + $invoice['amount_remaining'];

        // ✅ Update invoice
        $stmtUpdate = $conn->prepare("UPDATE invoices SET amount_paid = ?, amount_remaining = 0, payment_date = NOW() WHERE id = ? AND godown_id = ?");
        $stmtUpdate->bind_param("dii", $newAmountPaid, $invoiceId, $godown_id);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        // ✅ Insert into payment_log
        $repaymentAmount = $invoice['amount_remaining'];
        $customerName = $invoice['customer_name'];
        $paymentType = 'repayment';

        $stmtLog = $conn->prepare("INSERT INTO payment_log (invoice_id, customer_name, amount, payment_type, payment_date, godown_id) VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmtLog->bind_param("isdsi", $invoiceId, $customerName, $repaymentAmount, $paymentType, $godown_id);
        $stmtLog->execute();
        $stmtLog->close();

        $_SESSION['toast'] = "✅ Payment marked as paid!";
    } else {
        $_SESSION['toast'] = "⚠️ Already paid or invalid invoice!";
    }

    header("Location: payments.php");
    exit();
}

// Filters
$search = $_GET['search'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$query = "SELECT * FROM invoices WHERE amount_remaining > 0 AND godown_id = ?";
$countQuery = "SELECT COUNT(*) FROM invoices WHERE amount_remaining > 0 AND godown_id = ?";
$params = [$godown_id];
$types = "i";

if (!empty($search)) {
    $query .= " AND customer_name LIKE ?";
    $countQuery .= " AND customer_name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND due_date BETWEEN ? AND ?";
    $countQuery .= " AND due_date BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= "ss";
}

$query .= " ORDER BY due_date ASC LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types .= "ii";

// Fetch payments
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$payments = [];
$totalDue = 0;
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
    $totalDue += $row['amount_remaining'];
}
$stmt->close();

// Total count for pagination
$stmt = $conn->prepare($countQuery);
$stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
$stmt->execute();
$stmt->bind_result($totalCount);
$stmt->fetch();
$stmt->close();

$totalPages = ceil($totalCount / $perPage);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>💰 Payments Due</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="theme.css">
<style>
:root {
  --green: #eafaf1;
  --yellow: #fffbe6;
  --red: #ffe6e6;
  --primary: #4CAF50;
  --danger: #e74c3c;
  --bg: #f1f2f7;
  --footer-bg: #1f5d23;
}

* {
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', sans-serif;
  background: var(--bg);
  margin: 0;
  padding: 30px 15px 100px;
}

.container {
  max-width: 1100px;
  margin: auto;
  background: white;
  padding: 25px 30px;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

h2 {
  text-align: center;
  color: var(--primary);
  margin-bottom: 25px;
  font-weight: 700;
}

.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 15px;
  justify-content: space-between;
}

.filters input[type="text"],
.filters input[type="date"] {
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 1rem;
  flex: 1 1 30%;
  max-width: 100%;
  min-width: 150px;
}

.filter-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.filter-buttons button {
  padding: 10px 18px;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: background 0.3s ease;
}

.apply-btn {
  background-color: var(--primary);
  color: white;
}

.apply-btn:hover {
  background-color: #388e3c;
}

.reset-btn {
  background-color: #555;
  color: white;
}

.reset-btn:hover {
  background-color: #333;
}

.summary {
  font-size: 1rem;
  font-weight: bold;
  text-align: right;
  margin: 15px 0;
  color: #333;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 15px;
  margin-top: 10px;
  min-width: 750px;
}

th, td {
  padding: 12px 10px;
  border-bottom: 1px solid #ccc;
  text-align: center;
}

th {
  background: #e0f2f1;
  color: #333;
  font-weight: 600;
}

.green {
  background-color: var(--green);
}

.yellow {
  background-color: var(--yellow);
}

.red {
  background-color: var(--red);
}

.paid-btn {
  background: var(--danger);
  color: white;
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
}

.paid-btn:hover {
  background: #c0392b;
}

.no-data {
  text-align: center;
  font-style: italic;
  color: #888;
  padding: 20px;
}

.toast {
  position: fixed;
  top: 10px;
  right: 20px;
  background: #28a745;
  color: white;
  padding: 12px 18px;
  border-radius: 6px;
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
  z-index: 9999;
  font-size: 14px;
}

.pagination {
  display: flex;
  justify-content: center;
  margin-top: 25px;
  list-style: none;
  gap: 8px;
  flex-wrap: wrap;
}

.pagination a {
  padding: 8px 14px;
  background: #eee;
  color: black;
  border-radius: 6px;
  text-decoration: none;
  transition: background 0.3s;
}

.pagination .active a,
.pagination a:hover {
  background: var(--primary);
  color: white;
}

     footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #1f5d23;
        color: white;
        text-align: center;
        font-size: 13px;
        padding: 10px 12px;
        z-index: 999;
    }

    footer a {
        color: white;
        text-decoration: none;
    }


@media (max-width: 768px) {
  .filters {
    flex-direction: column;
  }

  .filters input {
    width: 100%;
  }

  .filter-buttons {
    flex-direction: column;
    width: 100%;
  }

  .filter-buttons button {
    width: 100%;
  }

  .summary {
    text-align: left;
  }

  table {
    font-size: 14px;
  }

  th, td {
    padding: 10px 8px;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 20px 15px;
  }

  h2 {
    font-size: 1.4rem;
  }

  .paid-btn {
    padding: 6px 10px;
    font-size: 13px;
  }

  table {
    font-size: 13px;
  }
}
</style>



</head>
<body>
<div class="container">
    <h2>💰 Payments Due</h2>

    <?php if ($toastMessage): ?>
        <div class="toast" id="toast"><?= $toastMessage ?></div>
    <?php endif; ?>

    <form class="filters" method="GET">
        <input type="text" name="search" placeholder="🔍 Search Customer" value="<?= htmlspecialchars($search) ?>">
        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
        <div  class="filter-buttons">
            <button class="apply-btn" type="submit">Apply Filter</button>
            <button class="reset-btn" type="button" onclick="window.location='payments.php'" style="background:#555;">Reset</button>
        </div>
    </form>

    <div class="summary">Total Due: ₹<?= number_format($totalDue, 2) ?></div>

    <?php if (count($payments) === 0): ?>
        <div class="no-data">✅ No pending payments found.</div>
    <?php else: ?>
        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Remaining</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
     <?php foreach ($payments as $payment):
    $due = new DateTime($payment['due_date']);
    $today = new DateTime();

    // Reset both to midnight
    $due->setTime(0, 0, 0);
    $today->setTime(0, 0, 0);

    $interval = (int)$today->diff($due)->format('%r%a');
    $rowClass = 'green';
    $status = '✅ Safe';

    if ($interval < 0) {
        $rowClass = 'red';
        $status = '❌ Payment date has passed!';
    } elseif ($interval === 0) {
        $rowClass = 'yellow';
        $status = "⚠️ Collect today";
    } elseif ($interval === 1) {
        $rowClass = 'yellow';
        $status = "⚠️ Collect tomorrow";
    } elseif ($interval <= 4) {
        $rowClass = 'yellow';
        $status = "⚠️ Collect after " . ($interval - 1) . " day(s)";
    }
?>


                <tr class="<?= $rowClass ?>">
                    <td><?= htmlspecialchars($payment['customer_name']) ?></td>
                    <td>₹<?= number_format($payment['total_amount'], 2) ?></td>
                    <td>₹<?= number_format($payment['amount_paid'], 2) ?></td>
                    <td>₹<?= number_format($payment['amount_remaining'], 2) ?></td>
                    <td><?= $payment['due_date'] ?></td>
                    <td><?= $status ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Mark as paid?');">
                            <input type="hidden" name="mark_paid_id" value="<?= $payment['id'] ?>">
                            <button type="submit" class="paid-btn">Mark Paid</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="<?= ($i === $page) ? 'active' : '' ?>">
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
        <?php endif; ?>
    <?php endif ?>
</div>

<script>
window.addEventListener('pageshow', function (event) {
    if (event.persisted || performance.navigation.type === 2) {
        window.location.href = 'dashboard.php';
    }
});

window.onload = function () {
    const toast = document.getElementById("toast");
    if (toast) {
        setTimeout(() => toast.style.display = "none", 3000);
    }
};
</script>
   <!-- Footer -->
  <footer>
    <p>&copy; 2025 Chamunda Mata Traders.<br>Built by Kandarp Patil | 📧 <a href="mailto:kandarppatil2@gmail.com">kandarppatil2@gmail.com</a></p>
  </footer>

</body>
</html>
