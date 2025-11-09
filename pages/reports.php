<?php
// pages/reports.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container">
  <h3 class="mb-3">Reports</h3>

  <div class="card p-3 mb-3">
    <form method="get" id="reportForm" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Report Type</label>
        <select name="type" id="report_type" class="form-select" required>
          <option value="">-- Select Report Type --</option>
          <option value="shop">Shop wise</option>
          <option value="category">Category wise</option>
          <option value="subcategory">Subcategory wise</option>
        </select>
      </div>

      <div class="col-md-4" id="filterShopWrap" style="display:none;">
        <label class="form-label">Select Shop (optional)</label>
        <select name="shop_id" class="form-select">
          <option value="">All Shops</option>
          <?php
          $sres = $conn->query("SELECT id, name FROM shops ORDER BY name");
          while ($s = $sres->fetch_assoc()) {
            echo "<option value='{$s['id']}'>" . htmlspecialchars($s['name']) . "</option>";
          }
          ?>
        </select>
      </div>

      <div class="col-md-4">
        <button class="btn btn-primary">View Report</button>
      </div>
    </form>
  </div>

  <?php
  if (!empty($_GET['type'])) {
    $type = $_GET['type'];

    if ($type === 'shop') {
      echo "<h5>Shop Wise Report</h5>";
      $sql = "SELECT IFNULL(s.name, 'Unknown') AS name, SUM(p.total_price) AS total, SUM(p.paid_amount) AS paid, SUM(p.total_price - p.paid_amount) AS balance
              FROM purchases p
              LEFT JOIN shops s ON p.shop_id = s.id";
      if (!empty($_GET['shop_id'])) {
        $sid = (int)$_GET['shop_id'];
        $sql .= " WHERE p.shop_id = {$sid}";
      }
      $sql .= " GROUP BY p.shop_id ORDER BY balance DESC, total DESC";
      $res = $conn->query($sql);

      echo "<div class='card p-3'><table class='table table-striped'><thead><tr><th>Shop</th><th>Total</th><th>Paid</th><th>Balance</th></tr></thead><tbody>";
      while ($r = $res->fetch_assoc()) {
        echo "<tr>
                <td>".htmlspecialchars($r['name'])."</td>
                <td>".number_format($r['total'],2)."</td>
                <td>".number_format($r['paid'],2)."</td>
                <td>".number_format($r['balance'],2)."</td>
              </tr>";
      }
      echo "</tbody></table></div>";

    } elseif ($type === 'category') {
      echo "<h5>Category Wise Report</h5>";
      $sql = "SELECT IFNULL(c.name, 'Unknown') AS name, SUM(p.total_price) AS total, SUM(p.paid_amount) AS paid, SUM(p.total_price - p.paid_amount) AS balance
              FROM purchases p
              LEFT JOIN categories c ON p.category_id = c.id
              GROUP BY p.category_id ORDER BY total DESC";
      $res = $conn->query($sql);

      echo "<div class='card p-3'><table class='table table-striped'><thead><tr><th>Category</th><th>Total</th><th>Paid</th><th>Balance</th></tr></thead><tbody>";
      while ($r = $res->fetch_assoc()) {
        echo "<tr>
                <td>".htmlspecialchars($r['name'])."</td>
                <td>".number_format($r['total'],2)."</td>
                <td>".number_format($r['paid'],2)."</td>
                <td>".number_format($r['balance'],2)."</td>
              </tr>";
      }
      echo "</tbody></table></div>";

    } elseif ($type === 'subcategory') {
      echo "<h5>Subcategory Wise Report</h5>";
      $sql = "SELECT IFNULL(sc.name, 'Unknown') AS name, SUM(p.total_price) AS total, SUM(p.paid_amount) AS paid, SUM(p.total_price - p.paid_amount) AS balance
              FROM purchases p
              LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
              GROUP BY p.subcategory_id ORDER BY total DESC";
      $res = $conn->query($sql);

      echo "<div class='card p-3'><table class='table table-striped'><thead><tr><th>Subcategory</th><th>Total</th><th>Paid</th><th>Balance</th></tr></thead><tbody>";
      while ($r = $res->fetch_assoc()) {
        echo "<tr>
                <td>".htmlspecialchars($r['name'])."</td>
                <td>".number_format($r['total'],2)."</td>
                <td>".number_format($r['paid'],2)."</td>
                <td>".number_format($r['balance'],2)."</td>
              </tr>";
      }
      echo "</tbody></table></div>";
    }
  }
  ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const typeSel = document.getElementById('report_type');
  const shopWrap = document.getElementById('filterShopWrap');

  function toggleShop() {
    if (typeSel.value === 'shop') shopWrap.style.display = '';
    else shopWrap.style.display = 'none';
  }
  typeSel.addEventListener('change', toggleShop);
  toggleShop();
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
