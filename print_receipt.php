<?php
session_start();
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
    die("Error: Missing Sale ID.");
}

$sale_id = (int)$_GET['id'];

try {
    // 1. First, get basic sale info to find the company_id if not in session
    $stmt = $pdo->prepare("SELECT company_id FROM sales WHERE id = ?");
    $stmt->execute([$sale_id]);
    $basic_sale = $stmt->fetch();
    
    if (!$basic_sale) {
        die("Error: Sale #$sale_id not found.");
    }
    
    $company_id = $basic_sale['company_id'];
    // Update session if it was missing (optional but helpful)
    if (!isset($_SESSION['company_id'])) {
        $_SESSION['company_id'] = $company_id;
    }

    // 2. Get full Sale data with Company details
    $stmt = $pdo->prepare("
        SELECT s.*, c.name as customer_name, co.name as company_name, co.address as company_address, co.phone as company_phone, 
               co.receipt_header, co.receipt_footer, co.currency_symbol, u.name as cashier_name
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        JOIN companies co ON s.company_id = co.id
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.id = ? AND s.company_id = ?
    ");
    $stmt->execute([$sale_id, $company_id]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        die("Error: Detailed sale data not found.");
    }

    // 3. Get Sale Items
    $stmt = $pdo->prepare("
        SELECT si.*, p.name as product_name 
        FROM sale_items si
        JOIN products p ON si.product_id = p.id
        WHERE si.sale_id = ?
    ");
    $stmt->execute([$sale_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?= $sale['receipt_no'] ?></title>
    <style>
        :root {
            --primary: #000;
            --text-main: #000;
            --text-muted: #333;
            --border: #ccc;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 12px; 
            color: var(--text-main); 
            background: #fff;
            width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-700 { font-weight: 700; }
        .fw-800 { font-weight: 800; }
        
        .header { margin-bottom: 12px; border-bottom: 1px dashed #000; padding-bottom: 8px; }
        .brand-name { 
            font-size: 18px; 
            font-weight: 800; 
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .company-info { font-size: 10px; line-height: 1.3; }
        
        .receipt-info { 
            margin-bottom: 12px; 
            font-size: 11px;
            line-height: 1.4;
        }
        .info-row { display: flex; justify-content: space-between; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .items-table th { 
            padding: 4px 0; 
            font-size: 10px; 
            text-transform: uppercase; 
            border-bottom: 1px dashed #000;
            text-align: left;
        }
        .items-table td { padding: 6px 0; border-bottom: 1px dotted #eee; vertical-align: top; }
        .product-name { font-weight: 700; font-size: 11px; margin-bottom: 1px; }
        .item-meta { font-size: 10px; color: var(--text-muted); }

        .summary-container { margin-top: 8px; }
        .summary-row { display: flex; justify-content: space-between; padding: 2px 0; }
        .summary-row.total { 
            margin-top: 6px; 
            padding: 8px 0; 
            border-top: 1px solid #000; 
            border-bottom: 1px solid #000;
            font-size: 15px; 
            font-weight: 800; 
        }
        
        .payment-info { 
            margin-top: 12px; 
            font-size: 11px;
        }

        .footer { margin-top: 20px; font-size: 11px; border-top: 1px dashed #000; padding-top: 12px; }
        .footer .thanks { font-weight: 700; font-size: 12px; margin-bottom: 4px; }
        .footer .pos-by { font-size: 9px; opacity: 0.7; margin-top: 8px; }

        @media print {
            body { width: 100%; padding: 5mm; }
            @page { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header text-center">
        <div class="brand-name"><?= htmlspecialchars($sale['company_name']) ?></div>
        <div class="company-info">
            <?php if (!empty($sale['receipt_header'])): ?>
                <?= nl2br(htmlspecialchars($sale['receipt_header'])) ?>
            <?php else: ?>
                <?= htmlspecialchars($sale['company_address']) ?><br>
                Tel: <?= htmlspecialchars($sale['company_phone']) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="receipt-info">
        <div class="info-row">
            <span>REC: #<?= $sale['receipt_no'] ?></span>
            <span><?= date('d/m/y H:i', strtotime($sale['created_at'])) ?></span>
        </div>
        <div class="info-row">
            <span>CASHIER: <?= htmlspecialchars(strtoupper($sale['cashier_name'] ?? 'System')) ?></span>
        </div>
        <?php if ($sale['customer_name']): ?>
        <div class="info-row">
            <span>CUSTOMER: <?= htmlspecialchars(strtoupper($sale['customer_name'])) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>ITEM</th>
                <th class="text-center">QTY</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <div class="product-name"><?= htmlspecialchars(strtoupper($item['product_name'])) ?></div>
                    <div class="item-meta">@<?= $sale['currency_symbol'] ?><?= number_format($item['unit_price'], 2) ?></div>
                </td>
                <td class="text-center fw-700"><?= $item['qty'] ?></td>
                <td class="text-right fw-700"><?= number_format($item['total'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary-container">
        <div class="summary-row">
            <span>SUBTOTAL</span>
            <span><?= $sale['currency_symbol'] ?><?= number_format($sale['subtotal'], 2) ?></span>
        </div>
        <?php if ($sale['tax_amount'] > 0): ?>
        <div class="summary-row">
            <span>TAX</span>
            <span><?= $sale['currency_symbol'] ?><?= number_format($sale['tax_amount'], 2) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($sale['discount_amount'] > 0): ?>
        <div class="summary-row">
            <span>DISCOUNT</span>
            <span>-<?= $sale['currency_symbol'] ?><?= number_format($sale['discount_amount'], 2) ?></span>
        </div>
        <?php endif; ?>
        <div class="summary-row total">
            <span>TOTAL DUE</span>
            <span><?= $sale['currency_symbol'] ?><?= number_format($sale['total_amount'], 2) ?></span>
        </div>
    </div>

    <div class="payment-info">
        <div class="summary-row">
            <span>PAID (<?= strtoupper($sale['payment_method']) ?>)</span>
            <span><?= $sale['currency_symbol'] ?><?= number_format($sale['amount_received'], 2) ?></span>
        </div>
        <div class="summary-row">
            <span>CHANGE</span>
            <span><?= $sale['currency_symbol'] ?><?= number_format($sale['change_amount'], 2) ?></span>
        </div>
    </div>

    <div class="footer text-center">
        <?php if (!empty($sale['receipt_footer'])): ?>
            <div style="white-space: pre-wrap; margin-bottom: 8px;"><?= htmlspecialchars($sale['receipt_footer']) ?></div>
        <?php else: ?>
            <div class="thanks">THANK YOU!</div>
            <p>PLEASE VISIT US AGAIN</p>
        <?php endif; ?>
        <div class="pos-by">VENDORA POS SYSTEM</div>
    </div>

    <script>
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
