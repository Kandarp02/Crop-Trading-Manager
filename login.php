<?php
// Prevent any caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
include 'includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE BINARY username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_username"] = $admin["username"];
        $_SESSION["timeout"] = time();
        header("Location: select_godown.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login – Chamunda Mata Traders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #2e7d32;
            --accent: #a5d6a7;
            --dark: #1b5e20;
            --bg: #f1f8e9;
        }

        * {
            font-family: 'Segoe UI', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background: var(--bg);
            margin: 0;
            padding-bottom: 80px;
        }

        .login-container {
            max-width: 400px;
            margin: 6% auto;
            padding: 35px 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .login-container h2 {
            text-align: center;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background-color: #256b28;
            border-color: #1f5d23;
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

        @media (max-width: 576px) {
            .login-container {
                margin: 20% auto;
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    <form method="POST" autocomplete="off">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input class="form-control" type="text" name="username" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Login</button>

        <?php if ($error): ?>
            <div class="alert alert-danger mt-3 text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </form>
</div>

<!-- Footer -->
<footer>
    <p>&copy; 2025 Chamunda Mata Traders. <br>  Built by Kandarp Patil | 📧 <a href="mailto:kandarppatil2@gmail.com">kandarppatil2@gmail.com</a></p>
</footer>

<script>
(() => {
    // Prevent back nav caching and form resubmission
    window.history.replaceState(null, "", window.location.href);
    window.addEventListener('pageshow', (e) => {
        if (e.persisted || performance.getEntriesByType("navigation")[0]?.type === "back_forward") {
            window.location.replace('index.php');
        }
    });
})();
</script>

</body>
</html>
