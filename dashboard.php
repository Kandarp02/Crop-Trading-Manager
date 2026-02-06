<?php 
include 'includes/auth.php'; 

// ✅ Ensure godown is selected
if (!isset($_SESSION['godown_id'])) {
    header("Location: select_godown.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Chamunda Mata Traders</title>

  <link rel="stylesheet" href="theme.css">


  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;600;700&display=swap" rel="stylesheet" />
  <!-- AOS Animation -->
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />

  <style>
  :root {
    --primary: #2e7d32;
    --accent: #a5d6a7;
    --purple: #81c784;
    --bg-light: #f1f8e9;
    --dark: #1b5e20;
  }

  body {
    background-color: var(--bg-light);
    font-family: 'Segoe UI', sans-serif;
    padding-bottom: 70px;
    margin: 0;
  }

  .navbar {
    background: linear-gradient(90deg, var(--primary), var(--purple));
    padding: 15px 30px;
    color: white;
  }

  .navbar-brand {
    font-weight: bold;
    font-size: 1.8rem;
    color: white;
    display: flex;
    align-items: center;
  }

  .navbar-brand i {
    margin-right: 10px;
    color: #fff59d;
  }

  .profile-dropdown {
    position: absolute;
    top: 15px;
    right: 30px;
  }

  .dashboard {
    padding: 60px 20px 30px;
    text-align: center;
  }

  .dashboard h1 {
    color: var(--primary);
    margin-bottom: 20px;
    font-weight: bold;
  }

  .dashboard h2 {
    color: var(--dark);
    margin-bottom: 40px;
    font-size: 1.3rem;
  }

  .card-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
  }

  .card {
    background-color: white;
    width: 260px;
    height: 150px;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.4s ease;
    cursor: pointer;
    padding: 20px;
  }

  .card:hover {
    height: 270px;
    transform: translateY(-5px) scale(1.03);
    background-color: #e8f5e9;
  }

  .card h5 {
    color: var(--primary);
    font-size: 1.25rem;
  }

  .card-body p {
    opacity: 0;
    transition: opacity 0.3s ease;
    height: 0;
    overflow: hidden;
    color: #333;
    font-size: 0.95rem;
  }

  .card:hover .card-body p {
    opacity: 1;
    height: auto;
    margin-top: 10px;
  }

  .card-body a {
    opacity: 1 !important;
    height: auto;
    display: inline-block;
    margin-top: 10px;
    background-color: var(--primary);
    color: white;
    padding: 6px 14px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: background-color 0.3s ease;
  }

  .card-body a:hover {
    background-color: #256b28;
  }

  footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color: var(--dark);
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
    .card {
      width: 90%;
    }
  }

  @media (max-width: 576px) {
    .navbar-brand {
      font-size: 1.4rem;
    }

    .profile-dropdown {
      top: 10px;
      right: 15px;
    }
  }
</style>

</head>
<body>
  <nav class="navbar">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <a class="navbar-brand" href="#"><i class="fas fa-store-alt fa-lg"></i> Chamunda Mata Traders</a>
      <div class="profile-dropdown">
        <div class="dropdown">
          <button class="btn dropdown-toggle text-white" type="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user-circle fa-2x"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
            <li><h6 class="dropdown-header">Logged in as:</h6></li>
            <li><a class="dropdown-item fw-semibold text-primary" href="#">
              <?php echo $_SESSION['admin_username']; ?>
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
          </ul>
        </div>
      </div>
    </div>
  </nav>

  <div class="dashboard" data-aos="fade-up">
    <h1>Welcome to the Dashboard</h1>
    <h2>Hello, <?php echo $_SESSION['admin_username']; ?> 👋</h2>
    <div class="container mt-4">
      <div class="card-container">
        <div class="card shadow-sm border-0" data-aos="zoom-in">
          <div class="card-body text-center">
            <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
            <h5 class="card-title">Stock</h5>
            <p>Manage inventory, reorder alerts, and supplier info.</p>
            <a href="stock.php" class="btn btn-outline-primary btn-sm">Open</a>
          </div>
        </div>
        <div class="card shadow-sm border-0" data-aos="zoom-in" data-aos-delay="100">
          <div class="card-body text-center">
            <i class="fas fa-file-invoice-dollar fa-2x text-success mb-2"></i>
            <h5 class="card-title">Billing</h5>
            <p>Create, view, and print invoices with ease.</p>
            <a href="create_bill.php" class="btn btn-outline-success btn-sm">Open</a>
          </div>
        </div>
        <div class="card shadow-sm border-0" data-aos="zoom-in" data-aos-delay="200">
          <div class="card-body text-center">
            <i class="fas fa-money-check-alt fa-2x text-warning mb-2"></i>
            <h5 class="card-title">Payments</h5>
            <p>Track payments, pending dues, and receipts.</p>
            <a href="payments.php" class="btn btn-outline-warning btn-sm">Open</a>
          </div>
        </div>
        <div class="card shadow-sm border-0" data-aos="zoom-in" data-aos-delay="300">
          <div class="card-body text-center">
            <i class="fas fa-chart-line fa-2x text-danger mb-2"></i>
            <h5 class="card-title">Reports</h5>
            <p>Access all business reports and analytics.</p>
            <a href="reports.php" class="btn btn-outline-danger btn-sm">Open</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Session Timeout Toast -->
  <div class="toast align-items-center text-white bg-danger border-0 position-fixed bottom-0 end-0 m-4" id="sessionToast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        ⚠️ Your session has expired due to inactivity.
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 Chamunda Mata Traders. <br>Built by Kandarp Patil | 📧 <a href="mailto:kandarppatil2@gmail.com">kandarppatil2@gmail.com</a></p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init({ duration: 1000, once: true });

    if (new URLSearchParams(window.location.search).get('timeout') === 'true') {
      const toast = new bootstrap.Toast(document.getElementById('sessionToast'));
      toast.show();
    }
  </script>
</body>
</html>
