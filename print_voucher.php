<?php
// print_voucher.php
// minimal clean page for printing vouchers
// expects GET: type={shop|category|subcategory} & id={int}

require_once __DIR__ . '/config/db.php'; // ensure correct DB include if different adjust
// If your project uses header include that sets $conn, you can instead require header and then skip output of header.
// But for clean voucher we do not include page header/footer.

$type = $_GET['type'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$type || $id <= 0) {
    echo "Invalid parameters.";
    exit;
}

// title and query switch
$title = "";
$rows = [];
$grand = 0.0;

if ($type === 'shop') {
    $shopName = $conn->query("SELECT name FROM shops WHERE id = {$id}")->fetch_assoc()['name'] ?? 'Unknown Shop';
    $title = "Shop Voucher: " . $shopName;
    $stmt = $conn->prepare("
        SELECT p.purchase_date, c.name AS category_name, p.description, p.quantity, p.unit_price, p.total_price
        FROM purchases p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.shop_id = ?
        ORDER BY p.purchase_date DESC, p.id DESC
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
        $grand += floatval($r['total_price']);
    }
    $stmt->close();
}
elseif ($type === 'category') {
    $catName = $conn->query("SELECT name FROM categories WHERE id = {$id}")->fetch_assoc()['name'] ?? 'Unknown Category';
    $title = "Category Voucher: " . $catName;
    $stmt = $conn->prepare("
        SELECT p.purchase_date, s.name AS shop_name, p.description, p.quantity, p.unit_price, p.total_price
        FROM purchases p
        LEFT JOIN shops s ON p.shop_id = s.id
        WHERE p.category_id = ?
        ORDER BY p.purchase_date DESC, p.id DESC
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
        $grand += floatval($r['total_price']);
    }
    $stmt->close();
}
elseif ($type === 'subcategory') {
    $scName = $conn->query("SELECT name FROM subcategories WHERE id = {$id}")->fetch_assoc()['name'] ?? 'Unknown Subcategory';
    $title = "Subcategory Voucher: " . $scName;
    $stmt = $conn->prepare("
        SELECT p.purchase_date, s.name AS shop_name, c.name AS category_name, p.description, p.quantity, p.unit_price, p.total_price
        FROM purchases p
        LEFT JOIN shops s ON p.shop_id = s.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.subcategory_id = ?
        ORDER BY p.purchase_date DESC, p.id DESC
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
        $grand += floatval($r['total_price']);
    }
    $stmt->close();
}
else {
    echo "Invalid type.";
    exit;
}

// --- Output printable HTML ---
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title) ?></title>
  <style>
    body { font-family: Arial, sans-serif; margin:20px; color:#222; }
    .header { text-align:center; margin-bottom:20px; }
    h2 { margin:0; }
    table { width:100%; border-collapse:collapse; margin-top:12px; }
    th, td { padding:8px; border:1px solid #444; }
    th { background:#f2f2f2; }
    .right { text-align:right; }
    .small { font-size:12px; color:#666; }
    @media print {
      .no-print { display:none; }
    }
  </style>
</head>
<body>
  <div class="header">
    <h2><?= htmlspecialchars($title) ?></h2>
    <div class="small">Generated: <?= date('Y-m-d H:i:s') ?></div>
  </div>

  <table>
    <thead>
      <tr>
        <th>SL</th>
        <th>Date</th>
        <?php if ($type !== 'shop'): ?><th>Shop</th><?php endif; ?>
        <?php if ($type === 'subcategory'): ?><th>Category</th><?php endif; ?>
        <th>Description</th>
        <th>Qty</th>
        <th>Unit Price (৳)</th>
        <th>Total (৳)</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $i=1;
      foreach ($rows as $r) {
        echo "<tr>";
        echo "<td>{$i}</td>";
        echo "<td>".htmlspecialchars($r['purchase_date'])."</td>";
        if ($type !== 'shop') {
          echo "<td>".htmlspecialchars($r['shop_name'] ?? '—')."</td>";
        }
        if ($type === 'subcategory') {
          echo "<td>".htmlspecialchars($r['category_name'] ?? '—')."</td>";
        }
        echo "<td>".nl2br(htmlspecialchars($r['description'] ?? '—'))."</td>";
        echo "<td class='right'>".(int)$r['quantity']."</td>";
        echo "<td class='right'>".number_format($r['unit_price'],2)."</td>";
        echo "<td class='right'>".number_format($r['total_price'],2)."</td>";
        echo "</tr>";
        $i++;
      }
      ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="<?= ($type === 'shop' ? 5 : ($type === 'subcategory' ? 6 : 6)) ?>" class="right"><strong>Grand Total</strong></td>
        <td class="right"><strong>৳<?= number_format($grand,2) ?></strong></td>
      </tr>
    </tfoot>
  </table>

  <div style="margin-top:18px;">
    <button class="no-print" onclick="window.print()">Print / Save as PDF</button>
    <a href="reports.php" class="no-print" style="margin-left:8px">Back to Reports</a>
  </div>
</body>
</html>
