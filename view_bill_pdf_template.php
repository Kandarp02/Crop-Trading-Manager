<?php
// view_bill_pdf_template.php

// No PHP processing here — only HTML + PHP variables passed in

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="theme.css">

    <style>
        body { font-family: DejaVu Sans, sans-serif; padding: 20px; }
        h1 { text-align: center; font-size: 24px; }
        h3 { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: center; }
        .summary { margin-top: 20px; font-size: 16px; }
        .summary strong { display: inline-block; width: 200px; }
    </style>
</head>
<body>

<h1><?= htmlspecialchars($business_name) ?></h1>
<h3>Invoice #<?= $invoice['id'] ?> | Customer: <?= htmlspecialchars($invoice['customer_name']) ?></h3>
<p>Date: <?= $invoice['bill_date'] ?></p>

<table>
    <tr>
        <th>Sr</th>
        <th>Item Name</th>
        <th>Quantity</th>
        <th>Selling Price</th>
        <th>Cost Price</th>
        <th>Total</th>
        <th>Profit</th>
    </tr>
    <?php foreach ($items as $index => $item): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($item['item_name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>₹<?= number_format($item['price'], 2) ?></td>
            <td>₹<?= number_format($item['cost_price'], 2) ?></td>
            <td>₹<?= number_format($item['total'], 2) ?></td>
            <td>₹<?= number_format($item['profit'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<div class="summary">
    <p><strong>Total Amount:</strong> ₹<?= number_format($invoice['total_amount'], 2) ?></p>
    <p><strong>Amount Paid:</strong> ₹<?= number_format($invoice['amount_paid'], 2) ?></p>
    <p><strong>Balance Due:</strong> ₹<?= number_format($invoice['balance'], 2) ?></p>
</div>

</body>
</html>
