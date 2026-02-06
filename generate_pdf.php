<?php
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
include 'includes/db.php';

if (!isset($_POST['bill_id'])) {
    die("No bill ID provided.");
}

$bill_id = intval($_POST['bill_id']);

// Fetch invoice
$stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->bind_param("i", $bill_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invoice) {
    die("Invoice not found.");
}

// Fetch invoice items
$stmtItems = $conn->prepare("
    SELECT ii.*, s.item_name, s.unit
    FROM invoice_items ii
    JOIN stock s ON ii.stock_id = s.id
    WHERE ii.invoice_id = ?
");
$stmtItems->bind_param("i", $bill_id);
$stmtItems->execute();
$itemsResult = $stmtItems->get_result();
$items = [];
while ($row = $itemsResult->fetch_assoc()) {
    $items[] = $row;
}
$stmtItems->close();

// ✅ Update stock
$updateStmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE id = ?");
foreach ($items as $item) {
    $qty = $item['quantity'];
    $stock_id = $item['stock_id'];
    $updateStmt->bind_param("di", $qty, $stock_id);
    $updateStmt->execute();
}
$updateStmt->close();

// ✅ Clear invoice items
$conn->query("DELETE FROM invoice_items");

$customer_name_clean = preg_replace('/[^a-zA-Z0-9_-]/', '_', $invoice['customer_name']);
$imagePath = realpath(__DIR__ . '/images/lll.jpg');
$imageData = base64_encode(file_get_contents($imagePath));
$imgSrc = 'data:image/jpeg;base64,' . $imageData;

// Format due date only if present
$formattedDueDate = '';
if (!empty($invoice['due_date']) && $invoice['amount_remaining'] > 0) {
    $date = new DateTime($invoice['due_date']);
    $day = $date->format('j');
    $month = $date->format('F');
    $year = $date->format('Y');

    $suffix = 'th';
    if ($day % 10 == 1 && $day != 11) $suffix = 'st';
    elseif ($day % 10 == 2 && $day != 12) $suffix = 'nd';
    elseif ($day % 10 == 3 && $day != 13) $suffix = 'rd';

    $formattedDueDate = $day . $suffix . ' ' . $month . ' ' . $year;
}

// PDF HTML content
$html = '
<style>
    @page { margin: 40px; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; border: 2px solid #000; padding: 20px; }
    .header { text-align: center; margin-bottom: 20px; }
    .company-details { font-weight: bold; font-size: 14px; margin-top: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #000; padding: 6px; text-align: left; }
    .total-row { font-weight: bold; background-color: #f0f0f0; }
    .footer { position: absolute; bottom: 20px; left: 0; width: 100%; border-top: 2px solid #000; text-align: center; padding-top: 8px; font-size: 11px; }
    .signature { margin-top: 40px; width: 100%; display: flex; justify-content: flex-end; font-size: 12px; }
    .signature div { border-top: 1px solid #000; width: 200px; text-align: center; padding-top: 5px; }
    .thank-you { margin: 60px 0 30px 0; text-align: center; font-weight: bold; font-size: 14px; }
</style>
<div class="header">
    <img src="' . $imgSrc . '" style="max-height:120px; max-width:200px;" />
    <div class="company-details">CHAMUNDA MATA TRADERS<br>Pimpri, Maharashtra, India<br>Phone: +917249743220 | Email: mayurbadgujar@gmail.com</div>
</div>
<h2 style="text-align:center;">Receipt</h2>
<p><strong>Customer Name:</strong> ' . htmlspecialchars($invoice['customer_name']) . '</p>
<p><strong>Bill Date:</strong> ' . htmlspecialchars($invoice['bill_date']) . '</p>
<table>
    <thead>
        <tr><th>Item</th><th>Qty</th><th>Unit</th><th>Price (₹)</th><th>Total (₹)</th></tr>
    </thead>
    <tbody>';

$total_all_items = 0;
foreach ($items as $item) {
    $total = $item['quantity'] * $item['price'];
    $total_all_items += $total;
    $html .= '<tr>
        <td>' . htmlspecialchars($item['item_name']) . '</td>
        <td>' . $item['quantity'] . '</td>
        <td>' . htmlspecialchars($item['unit']) . '</td>
        <td>' . number_format($item['price'], 2) . '</td>
        <td>' . number_format($total, 2) . '</td>
    </tr>';
}

$html .= '
    <tr class="total-row"><td colspan="4" style="text-align:right;">Total Amount (₹)</td><td>' . number_format($invoice['total_amount'], 2) . '</td></tr>
    <tr class="total-row"><td colspan="4" style="text-align:right;">Amount Paid (₹)</td><td>' . number_format($invoice['amount_paid'], 2) . '</td></tr>
    <tr class="total-row"><td colspan="4" style="text-align:right;">Amount Remaining (₹)</td><td>' . number_format($invoice['amount_remaining'], 2) . '</td></tr>';

if (!empty($formattedDueDate)) {
    $html .= '<tr><td colspan="5" style="text-align:center;"><strong>Repayment Due Date:</strong> ' . $formattedDueDate . '</td></tr>';
}

$html .= '
</tbody></table>
<div class="signature"><div>Authorized Signature</div></div>
<div class="thank-you">Thank you for doing business with us!</div>
<div class="footer">Software Developed by Kandarp Patil</div>
';

// Stream PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$filename = "receipt_" . $customer_name_clean . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);

exit;
