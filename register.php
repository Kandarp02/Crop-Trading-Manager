<?php
session_start();
include 'includes/db.php';

$message = "";

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password_plain = $_POST['password'];

    if (empty($username) || empty($password_plain)) {
        $_SESSION['message'] = "<p style='color:red;'>❌ Username and password cannot be empty.</p>";
        header("Location: register.php");
        exit();
    }

    $checkSql = "SELECT id FROM admins WHERE username = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['message'] = "<p style='color:red;'>❌ Username already taken. Please choose another username.</p>";
        $stmt->close();
        header("Location: register.php");
        exit();
    }
    $stmt->close();

    $hashed_password = password_hash($password_plain, PASSWORD_DEFAULT);

    $insertSql = "INSERT INTO admins (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        $stmt->close();
        // Show success message with delay redirect
        echo "<p style='color:green;'>✅ Admin created successfully! Redirecting to login page...</p>";
        echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 3000);</script>";
        exit();
    } else {
        $_SESSION['message'] = "<p style='color:red;'>❌ Error: " . htmlspecialchars($conn->error) . "</p>";
        $stmt->close();
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Register Admin</title>
</head>
<body>
    <h2>Register New Admin</h2>

    <?= $message ?>

    <form method="post" action="">
        <label for="username">Username:</label><br>
        <input type="text" name="username" id="username" required /><br><br>

        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password" required /><br><br>

        <input type="submit" value="Register" />
    </form>
</body>
<script>
  setTimeout(() => {
    // Clear form inputs
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
  }, 100); // small delay to wait after success
</script>

</html>
