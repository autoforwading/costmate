<?php
// reports.php
require_once __DIR__ . '/includes/header.php';

// sanitize GET
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$id   = isset($_GET['id']) ? intval($_GET['id']) : 0;

// lists
$shops = $conn->query("SELECT id, name FROM shops ORDER BY name ASC");
$cats  = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$subcs = $conn->query("SELECT id, name FROM subcategories ORDER BY name ASC");

// helper to safe fetch sum (returns float)
function safe_sum($conn, $sql) {
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        return floatval($row[array_keys($row)[0]] ?? 0);
    }
    return 0.0;
}

?>
<div class="container">
  <h3 class="mb-3">Reports</h3>

  <!-- top single dropdown card (hidden when type set) -->
  <?php if ($type === ''): ?>
  <div class="card p-3 mb-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-6">
        <label class="form-label">Select Report Type</label>
        <select id="reportType" class="form-select" aria-label="Report Type">
          <option value="">-- Select Report Type --</option>
          <option value="shop">Shop Wise</option>
          <option value="category">Category Wise</option>
          <option value="subcategory">Subcategory Wise</option>
          <option value="payment">Payment Wise</option>
        </select>
      </div>
    </div>
    <small class="text-muted mt-2 d-block">Choose a report type — results load automatically and the dropdown will hide.</small>
  </div>
  <?php endif; ?>


  <!-- ================= SHOP WISE - list of shops ================= -->
  <?php if ($type === 'shop' && $id === 0): ?>
    <div class="card p-3 mb-4">
      <h5>All Shops</h5>
      <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>SL</th>
              <th>Shop Name</th>
              <th>Total Purchases (৳)</th>
              <th>Total Paid (৳)</th>
              <th>Due (৳)</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1;
            if ($shops && $shops->num_rows) {
              while ($s = $shops->fetch_assoc()) {
                $sid = (int)$s['id'];

                // total purchases for this shop (safe)
                $q1 = $conn->query("SELECT IFNULL(SUM(total_price),0) AS tot FROM purchases WHERE shop_id = {$sid}");
                $purchase = ($q1 && $r1 = $q1->fetch_assoc()) ? floatval($r1['tot']) : 0.0;

                // total paid from payment_records
                $q2 = $conn->query("SELECT IFNULL(SUM(amount),0) AS paid FROM payment_records WHERE shop_id = {$sid}");
                $payment = ($q2 && $r2 = $q2->fetch_assoc()) ? floatval($r2['paid']) : 0.0;

                $due = $purchase - $payment;

                echo "<tr>";
                echo "<td>{$i}</td>";
                echo "<td class='text-start'><a href='?type=shop&id={$sid}'>".htmlspecialchars($s['name'])."</a></td>";
                echo "<td>".number_format($purchase,2)."</td>";
                echo "<td>".number_format($payment,2)."</td>";
                echo "<td class='".($due>0?'text-danger':'text-success')."'>".number_format($due,2)."</td>";
                echo "<td><a class='btn btn-sm btn-outline-primary' href='?type=shop&id={$sid}'>View</a></td>";
                echo "</tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='6'>No shops found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  <!-- ================= SHOP WISE - single shop purchases ================= -->
  <?php elseif ($type === 'shop' && $id > 0): ?>
    <?php
    // shop name safe
    $shopName = '';
    $sres = $conn->query("SELECT name FROM shops WHERE id = {$id}");
    if ($sres && $sr = $sres->fetch_assoc()) $shopName = $sr['name'];
    // purchases list with category/subcategory
    $pstmt = $conn->prepare("
      SELECT p.id, p.purchase_date, p.description, p.quantity, p.unit_price, p.total_price,
             c.name AS category_name, sc.name AS subcategory_name
      FROM purchases p
      LEFT JOIN categories c ON p.category_id = c.id
      LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
      WHERE p.shop_id = ?
      ORDER BY p.purchase_date DESC, p.id DESC
    ");
    $pstmt->bind_param("i", $id);
    $pstmt->execute();
    $pRes = $pstmt->get_result();

    ?>
    <div class="card p-3 mb-4">
      <h5>Purchases for: <?= htmlspecialchars($shopName) ?></h5>
      <div class="mb-2">
        <a href="reports.php?type=shop" class="btn btn-secondary btn-sm">← Back to Shops</a>
        <a href="print_voucher.php?type=shop&id=<?= $id ?>" target="_blank" class="btn btn-sm btn-outline-success ms-2">🖨 Print Voucher</a>
      </div>

      <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>SL</th>
              <th>Date</th>
              <th>Category</th>
              <th>Description</th>
              <th>Quantity</th>
              <th>Unit Price (৳)</th>
              <th>TK (৳)</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1; $grand = 0;
            if ($pRes && $pRes->num_rows) {
              while ($r = $pRes->fetch_assoc()) {
                $grand += floatval($r['total_price']);
                echo "<tr>";
                echo "<td>{$i}</td>";
                echo "<td>".htmlspecialchars($r['purchase_date'])."</td>";
                echo "<td>".htmlspecialchars($r['category_name'] ?: '—')."</td>";
                echo "<td class='text-start'>".nl2br(htmlspecialchars($r['description'] ?: '—'))."</td>";
                echo "<td>".htmlspecialchars($r['quantity'])."</td>";
                echo "<td>".number_format($r['unit_price'],2)."</td>";
                echo "<td>".number_format($r['total_price'],2)."</td>";
                echo "</tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='7'>No purchases found for this shop.</td></tr>";
            }
            ?>
          </tbody>
          <tfoot class="fw-bold">
            <tr>
              <td colspan="6" class="text-end">Total</td>
              <td>৳<?= number_format($grand,2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <?php $pstmt->close(); ?>


  <!-- ================= CATEGORY WISE (list) ================= -->
  <?php elseif ($type === 'category' && $id === 0): ?>
    <div class="card p-3 mb-4">
      <h5>All Categories</h5>
      <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>SL</th>
              <th>Category</th>
              <th>Total Qty</th>
              <th>Total Amount (৳)</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i=1;
            if ($cats && $cats->num_rows) {
              while ($c = $cats->fetch_assoc()) {
                $cid = (int)$c['id'];
                $r = $conn->query("SELECT IFNULL(SUM(quantity),0) AS qty, IFNULL(SUM(total_price),0) AS tot FROM purchases WHERE category_id = {$cid}");
                $row = ($r && $rr = $r->fetch_assoc()) ? $rr : ['qty'=>0,'tot'=>0];
                echo "<tr>";
                echo "<td>{$i}</td>";
                echo "<td class='text-start'><a href='?type=category&id={$cid}'>".htmlspecialchars($c['name'])."</a></td>";
                echo "<td>".number_format($row['qty'],0)."</td>";
                echo "<td>".number_format($row['tot'],2)."</td>";
                echo "<td><a class='btn btn-sm btn-outline-primary' href='?type=category&id={$cid}'>View</a></td>";
                echo "</tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='5'>No categories found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  <!-- ================= CATEGORY WISE - details ================= -->
  <?php elseif ($type === 'category' && $id > 0): ?>
    <?php
    $catName = $conn->query("SELECT name FROM categories WHERE id = {$id}")->fetch_assoc()['name'] ?? '';
    $stmt = $conn->prepare("
      SELECT p.id, p.purchase_date, p.description, p.quantity, p.unit_price, p.total_price, s.name AS shop_name
      FROM purchases p
      LEFT JOIN shops s ON p.shop_id = s.id
      WHERE p.category_id = ?
      ORDER BY p.purchase_date DESC, p.id DESC
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $cres = $stmt->get_result();
    ?>
    <div class="card p-3 mb-4">
      <h5>Category: <?= htmlspecialchars($catName) ?></h5>
      <div class="mb-2">
        <a href="reports.php?type=category" class="btn btn-secondary btn-sm">← Back to Categories</a>
        <a href="print_voucher.php?type=category&id=<?= $id ?>" target="_blank" class="btn btn-sm btn-outline-success ms-2">🖨 Print Voucher</a>
      </div>

      <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>SL</th>
              <th>Date</th>
              <th>Shop</th>
              <th>Description</th>
              <th>Quantity</th>
              <th>Unit Price (৳)</th>
              <th>TK (৳)</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i=1; $grand=0;
            if ($cres && $cres->num_rows) {
              while ($r = $cres->fetch_assoc()) {
                $grand += floatval($r['total_price']);
                echo "<tr>";
                echo "<td>{$i}</td>";
                echo "<td>".htmlspecialchars($r['purchase_date'])."</td>";
                echo "<td class='text-start'>".htmlspecialchars($r['shop_name'] ?: '—')."</td>";
                echo "<td class='text-start'>".nl2br(htmlspecialchars($r['description'] ?: '—'))."</td>";
                echo "<td>".htmlspecialchars($r['quantity'])."</td>";
                echo "<td>".number_format($r['unit_price'],2)."</td>";
                echo "<td>".number_format($r['total_price'],2)."</td>";
                echo "</tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='7'>No purchase records for this category.</td></tr>";
            }
            ?>
          </tbody>
          <tfoot class="fw-bold">
            <tr>
              <td colspan="6" class="text-end">Total</td>
              <td>৳<?= number_format($grand,2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <?php $stmt->close(); ?>


  <!-- ================= SUBCATEGORY WISE (list) ================= -->
  <?php elseif ($type === 'subcategory' && $id === 0): ?>
    <div class="card p-3 mb-4">
      <h5>All Subcategories</h5>
      <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>SL</th>
              <th>Subcategory</th>
              <th>Total Qty</th>
              <th>Total Amount (৳)</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i=1;
            if ($subcs && $subcs->num_rows) {
              while ($sc = $subcs->fetch_assoc()) {
                $scid = (int)$sc['id'];
                $r = $conn->query("SELECT IFNULL(SUM(quantity),0) AS qty, IFNULL(SUM(total_price),0) AS tot FROM purchases WHERE subcategory_id = {$scid}");
                $row = ($r && $rr = $r->fetch_assoc()) ? $rr : ['qty'=>0,'tot'=>0];
                echo "<tr>";
                echo "<td>{$i}</td>";
                echo "<td class='text-start'><a href='?type=subcategory&id={$scid}'>".htmlspecialchars($sc['name'])."</a></td>";
                echo "<td>".number_format($row['qty'],0)."</td>";
                echo "<td>".number_format($row['tot'],2)."</td>";
                echo "<td><a class='btn btn-sm btn-outline-primary' href='?type=subcategory&id={$scid}'>View</a></td>";
                echo "</tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='5'>No subcategories found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  <!-- ================= SUBCATEGORY WISE - details ================= -->
  <?php elseif ($type === 'subcategory' && $id > 0): ?>
    <?php
    $scName = $conn->query("SELECT name FROM subcategories WHERE id = {$id}")->fetch_assoc()['name'] ?? '';
    $stmt = $conn->prepare("
      SELECT p.id, p.purchase_date, p.description, p.quantity, p.unit_price, p.total_price, s.name AS shop_name, c.name AS category_name
      FROM purchases p
      LEFT JOIN shops s ON p.shop_id = s.id
      LEFT JOIN categories c ON p.category_id = c.id
      WHERE p.subcategory_id = ?
      ORDER BY p.purchase_date DESC, p.id DESC
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $sres = $stmt->get_result();
    ?>
    <div class="card p-3 mb-4">
      <h5>Subcategory: <?= htmlspecialchars($scName) ?></h5>
      <div class="mb-2">
        <a href="reports.php?type=subcategory" class="btn btn-secondary btn-sm">← Back to Subcategories</a>
        <a href="print_voucher.php?type=subcategory&id=<?= $id ?>" target="_blank" class="btn btn-sm btn-outline-success ms-2">🖨 Print Voucher</a>
      </div>

      <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>SL</th>
              <th>Date</th>
              <th>Shop</th>
              <th>Category</th>
              <th>Description</th>
              <th>Quantity</th>
              <th>TK (৳)</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i=1; $grand=0;
            if ($sres && $sres->num_rows) {
              while ($r = $sres->fetch_assoc()) {
                $grand += floatval($r['total_price']);
                echo "<tr>";
                echo "<td>{$i}</td>";
                echo "<td>".htmlspecialchars($r['purchase_date'])."</td>";
                echo "<td class='text-start'>".htmlspecialchars($r['shop_name'] ?: '—')."</td>";
                echo "<td>".htmlspecialchars($r['category_name'] ?: '—')."</td>";
                echo "<td class='text-start'>".nl2br(htmlspecialchars($r['description'] ?: '—'))."</td>";
                echo "<td>".htmlspecialchars($r['quantity'])."</td>";
                echo "<td>".number_format($r['total_price'],2)."</td>";
                echo "</tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='7'>No purchases found for this subcategory.</td></tr>";
            }
            ?>
          </tbody>
          <tfoot class="fw-bold">
            <tr>
              <td colspan="6" class="text-end">Total</td>
              <td>৳<?= number_format($grand,2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <?php $stmt->close(); ?>


  <!-- ================= PAYMENT WISE - list of shops (payments summary) ================= -->
  <?php elseif ($type === 'payment' && $id === 0): ?>
    <div class="card p-3 mb-4">
      <h5>All Shops (Payments Summary)</h5>
      <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>SL</th>
              <th>Shop Name</th>
              <th>Total Purchases (৳)</th>
              <th>Total Payments (৳)</th>
              <th>Due (৳)</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i=1;
            // reuse shops result — need to re-query because earlier we may have exhausted it
            $shopsAll = $conn->query("SELECT id, name FROM shops ORDER BY name ASC");
            if ($shopsAll && $shopsAll->num_rows) {
              while ($s = $shopsAll->fetch_assoc()) {
                $sid = (int)$s['id'];
                $purchase_total = safe_sum($conn, "SELECT IFNULL(SUM(total_price),0) AS tot FROM purchases WHERE shop_id = {$sid}");
                $payment_total  = safe_sum($conn, "SELECT IFNULL(SUM(amount),0) AS paid FROM payment_records WHERE shop_id = {$sid}");
                $due = $purchase_total - $payment_total;
                echo "<tr>";
                echo "<td>{$i}</td>";
                echo "<td class='text-start'><a href='?type=payment&id={$sid}'>".htmlspecialchars($s['name'])."</a></td>";
                echo "<td>".number_format($purchase_total,2)."</td>";
                echo "<td>".number_format($payment_total,2)."</td>";
                echo "<td class='".($due>0?'text-danger':'text-success')."'>".number_format($due,2)."</td>";
                echo "<td><a class='btn btn-sm btn-outline-primary' href='?type=payment&id={$sid}'>View</a></td>";
                echo "</tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='6'>No shops found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  <!-- ================= PAYMENT WISE - details for a shop ================= -->
  <?php elseif ($type === 'payment' && $id > 0): ?>
    <?php
    // get shop name
    $shopName = $conn->query("SELECT name FROM shops WHERE id = {$id}")->fetch_assoc()['name'] ?? '';

    // fetch payment records (join method name from payment_methods)
    $ptmt = $conn->prepare("
      SELECT pr.id, pr.date, pr.amount, pr.method, pr.description, pm.name AS method_name
      FROM payment_records pr
      LEFT JOIN payment_methods pm ON pr.method = pm.id
      WHERE pr.shop_id = ?
      ORDER BY pr.date DESC, pr.id DESC
    ");
    $ptmt->bind_param("i", $id);
    $ptmt->execute();
    $pres = $ptmt->get_result();
    ?>
    <div class="card p-3 mb-4">
      <h5>Payments for: <?= htmlspecialchars($shopName) ?></h5>
      <div class="mb-2">
        <a href="reports.php?type=payment" class="btn btn-secondary btn-sm">← Back to Payment Summary</a>
        <a href="print_voucher.php?type=payment&id=<?= $id ?>" target="_blank" class="btn btn-sm btn-outline-success ms-2">🖨 Print Voucher</a>
      </div>

      <div class="mb-2">
        <small class="text-muted">Note: category is not stored in payment_records; therefore Category column shows '—'. If you want per-payment category, add a category_id (or purchase_id) to payment_records when recording payments.</small>
      </div>

      <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped text-center align-middle">
          <thead class="table-dark">
            <tr>
              <th>SL</th>
              <th>Date</th>
              <!-- <th>Category</th> -->
              <th>Description</th>
              <th>Payment Method</th>
              <th>Tk (৳)</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i=1; $sumPay = 0;
            if ($pres && $pres->num_rows) {
              while ($r = $pres->fetch_assoc()) {
                $sumPay += floatval($r['amount']);
                echo "<tr>";
                echo "<td>{$i}</td>";
                echo "<td>".htmlspecialchars($r['date'])."</td>";
                // category not available in payment_records
                // echo "<td>—</td>";
                echo "<td class='text-start'>".nl2br(htmlspecialchars($r['description'] ?: '—'))."</td>";
                echo "<td>".htmlspecialchars($r['method'] ?: '—')."</td>";
                echo "<td>".number_format($r['amount'],2)."</td>";
                echo "</tr>";
                $i++;
              }
            } else {
              echo "<tr><td colspan='5'>No payment records found for this shop.</td></tr>";
            }
            ?>
          </tbody>
          <tfoot class="fw-bold">
            <tr>
              <td colspan="4" class="text-end">Total Payments:</td>
              <td>৳<?= number_format($sumPay,2) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <?php $ptmt->close(); ?>

  <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const sel = document.getElementById('reportType');
  if (sel) {
    sel.addEventListener('change', function(){
      const v = this.value;
      if (!v) return;
      // navigate to top-level of that type
      window.location = 'reports.php?type=' + encodeURIComponent(v);
    });
  }

  // hide top dropdown card when a type is present
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('type')) {
    const card = document.querySelector('.card.p-3.mb-3');
    if (card) card.style.display = 'none';
  }
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
