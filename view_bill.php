<?php
// view_bill.php

include 'includes/db.php';

if (!isset($_GET['bill_id'])) {
    die("Bill ID is required.");
}

$bill_id = intval($_GET['bill_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="theme.css">

    <title>Generate & Redirect</title>
</head>
<body>
    <form id="pdfForm" action="generate_pdf.php" method="POST" target="_blank">
        <input type="hidden" name="bill_id" value="<?= $bill_id ?>">
    </form>

    <script>
        // Submit PDF form to download the bill
        window.onload = function () {
            document.getElementById('pdfForm').submit();

            // Wait for download, then redirect
            setTimeout(function () {
                window.location.href = "dashboard.php";
            }, 3000); // 3 seconds
        };
    </script>
</body>
</html>
