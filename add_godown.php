<?php
include 'includes/db.php';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $conn->query("INSERT INTO godowns (name, location) VALUES ('$name', '$location')");
    header("Location: select_godown.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Godown</title>
    <link rel="stylesheet" href="theme.css">

</head>
<body>
    <h2>Add New Godown</h2>
    <form method="post">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>
        <label>Location:</label><br>
        <input type="text" name="location" required><br><br>
        <button type="submit">Add Godown</button>
    </form>
</body>
</html>
