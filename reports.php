<?php
session_start();
include 'includes/auth.php';
include 'includes/db.php';

$godown_id = $_SESSION['godown_id'] ?? 0;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$today = date('Y-m-d');
$month = date('Y-m');
$year = date('Y');

// ✅ (Optional) Delete old logs older than 1 year to save space
// This won't affect the daily/monthly/yearly income because we always filter by date
$conn->query("DELETE FROM payment_log WHERE payment_date < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)");

// ✅ Daily Income
$today_income = 0;
$stmt = $conn->prepare("SELECT SUM(amount) FROM payment_log WHERE DATE(payment_date) = ? AND godown_id = ?");
$stmt->bind_param("si", $today, $godown_id);
$stmt->execute();
$stmt->bind_result($today_income);
$stmt->fetch();
$stmt->close();

// ✅ Monthly Income
$month_income = 0;
$stmt = $conn->prepare("SELECT SUM(amount) FROM payment_log WHERE DATE_FORMAT(payment_date, '%Y-%m') = ? AND godown_id = ?");
$stmt->bind_param("si", $month, $godown_id);
$stmt->execute();
$stmt->bind_result($month_income);
$stmt->fetch();
$stmt->close();

// ✅ Yearly Income
$year_income = 0;
$stmt = $conn->prepare("SELECT SUM(amount) FROM payment_log WHERE YEAR(payment_date) = ? AND godown_id = ?");
$stmt->bind_param("ii", $year, $godown_id);
$stmt->execute();
$stmt->bind_result($year_income);
$stmt->fetch();
$stmt->close();

// ✅ Fetch paginated payment log records
$stmt = $conn->prepare("SELECT customer_name, payment_date, amount, payment_type FROM payment_log WHERE godown_id = ? ORDER BY payment_date DESC LIMIT ?, ?");
$stmt->bind_param("iii", $godown_id, $offset, $perPage);
$stmt->execute();
$log_result = $stmt->get_result();
$stmt->close();

// ✅ Count total for pagination
$stmt = $conn->prepare("SELECT COUNT(*) FROM payment_log WHERE godown_id = ?");
$stmt->bind_param("i", $godown_id);
$stmt->execute();
$stmt->bind_result($totalRecords);
$stmt->fetch();
$stmt->close();

$totalPages = ceil($totalRecords / $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📊 Reports - Income Summary</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <style>
    :root {
      --green: #eafaf1;
      --yellow: #fffbe6;
      --red: #ffe6e6;
      --primary: #4CAF50;
      --danger: #e74c3c;
    }
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--green);
      padding: 30px 10px 100px;
    }
    .card-summary {
      background-color: #fff;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      text-align: center;
    }
    .card-summary h5 {
      color: #333;
      margin-bottom: 10px;
    }
    .card-summary p {
      font-size: 24px;
      font-weight: bold;
      color: green;
    }
    .table thead {
      background-color: var(--yellow);
    }
    .table tbody tr:hover {
      background-color: #f1f1f1;
    }
    .pagination {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 20px;
      flex-wrap: wrap;
    }
    .pagination a {
      padding: 6px 12px;
      background: #eee;
      border-radius: 6px;
      text-decoration: none;
      color: black;
    }
    .pagination .active a, .pagination a:hover {
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
  </style>
</head>
<body>
<div class="container" data-aos="fade-up">
  <h2 class="text-center mb-4">📊 Income Reports Summary</h2>
  <div class="row">
    <div class="col-md-4" data-aos="zoom-in">
      <div class="card-summary">
        <h5>Today's Income</h5>
        <p>₹ <?= number_format($today_income ?? 0, 2) ?></p>
      </div>
    </div>
    <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
      <div class="card-summary">
        <h5>This Month's Income</h5>
        <p>₹ <?= number_format($month_income ?? 0, 2) ?></p>
      </div>
    </div>
    <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
      <div class="card-summary">
        <h5>This Year's Income</h5>
        <p>₹ <?= number_format($year_income ?? 0, 2) ?></p>
      </div>
    </div>
  </div>

  <div class="mt-5">
    <h4 class="mb-3">🧾 Payment Log</h4>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Payment Date</th>
            <th>Amount (₹)</th>
            <th>Payment Type</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($log_result->num_rows > 0): ?>
            <?php while($row = $log_result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= date('d-m-Y', strtotime($row['payment_date'])) ?></td>
                <td><?= number_format($row['amount'], 2) ?></td>
                <td><?= ucfirst(str_replace('_', ' ', $row['payment_type'])) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center">No payments logged yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($totalPages > 1): ?>
    <ul class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="<?= $i === $page ? 'active' : '' ?>">
          <a href="?page=<?= $i ?>"> <?= $i ?> </a>
        </li>
      <?php endfor; ?>
    </ul>
  <?php endif; ?>
</div>

<footer>
  <p>&copy; <?= date('Y') ?> Chamunda Mata Traders.<br>Built by Kandarp Patil | 📧
    <a href="mailto:kandarppatil2@gmail.com">kandarppatil2@gmail.com</a>
  </p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>
