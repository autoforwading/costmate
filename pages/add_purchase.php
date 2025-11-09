<?php
// pages/add_purchase.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

// Handle adding a new payment method inline (so you don't need a separate page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_method') {
    $mname = trim($_POST['method_name']);
    if ($mname !== '') {
        $stmt = $conn->prepare("INSERT INTO payment_methods (name) VALUES (?)");
        $stmt->bind_param('s', $mname);
        $stmt->execute();
        $stmt->close();
        echo "<div class='container'><div class='alert alert-success'>Payment method added.</div></div>";
    }
}

// Handle purchase save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_purchase') {
    // collect and sanitize
    $shop_id = !empty($_POST['shop_id']) ? (int)$_POST['shop_id'] : null;
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $subcategory_id = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
    $description = trim($_POST['description'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 1);
    $unit_price = number_format((float)($_POST['unit_price'] ?? 0), 2, '.', '');
    $total_price = number_format($quantity * (float)$unit_price, 2, '.', '');
    $paid_amount = number_format((float)($_POST['paid_amount'] ?? 0), 2, '.', '');
    $payment_method_id = !empty($_POST['payment_method_id']) ? (int)$_POST['payment_method_id'] : null;
    $payment_desc = trim($_POST['payment_desc'] ?? '');
    $purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : date('Y-m-d');

    // insert with prepared statement
    $stmt = $conn->prepare("INSERT INTO purchases (shop_id, category_id, subcategory_id, description, quantity, unit_price, total_price, paid_amount, payment_method_id, payment_desc, purchase_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iiisiddisss', $shop_id, $category_id, $subcategory_id, $description, $quantity, $unit_price, $total_price, $paid_amount, $payment_method_id, $payment_desc, $purchase_date);
    $ok = $stmt->execute();
    if ($ok) {
        echo "<div class='container'><div class='alert alert-success'>Purchase saved successfully.</div></div>";
    } else {
        echo "<div class='container'><div class='alert alert-danger'>Error: " . htmlspecialchars($stmt->error) . "</div></div>";
    }
    $stmt->close();
}

// fetch dropdown data
$shops = $conn->query("SELECT id, name FROM shops ORDER BY name");
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name");
$subcategories = $conn->query("SELECT id, name, category_id FROM subcategories ORDER BY name");
$methods = $conn->query("SELECT id, name FROM payment_methods ORDER BY name");
?>

<div class="container">
  <h3 class="mb-3">Add Purchase</h3>

  <div class="card mb-3 p-3">
    <form method="post" id="purchaseForm">
      <input type="hidden" name="action" value="save_purchase">

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Shop</label>
          <select name="shop_id" class="form-select" required>
            <option value="">-- Select Shop --</option>
            <?php while ($r = $shops->fetch_assoc()): ?>
              <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Category</label>
          <select name="category_id" id="category_select" class="form-select" required>
            <option value="">-- Select Category --</option>
            <?php while ($r = $categories->fetch_assoc()): ?>
              <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Subcategory</label>
          <select name="subcategory_id" id="subcategory_select" class="form-select">
            <option value="">-- Optional --</option>
            <?php
            // we'll list all but filter by JS if you want category-specific; keeping simple list
            while ($s = $subcategories->fetch_assoc()):
            ?>
              <option value="<?= $s['id'] ?>" data-cat="<?= $s['category_id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>

      <div class="mt-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"></textarea>
      </div>

      <div class="row g-3 mt-2">
        <div class="col-md-3">
          <label class="form-label">Quantity</label>
          <input type="number" name="quantity" id="quantity" class="form-control" min="1" value="1" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Unit Price</label>
          <input type="number" name="unit_price" id="unit_price" class="form-control" step="0.01" value="0.00" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Total Price</label>
          <input type="text" name="total_price" id="total_price" class="form-control" readonly value="0.00">
        </div>

        <div class="col-md-3">
          <label class="form-label">Paid Amount</label>
          <input type="number" name="paid_amount" id="paid_amount" class="form-control" step="0.01" value="0.00" required>
        </div>
      </div>

      <div class="row g-3 mt-3">
        <div class="col-md-6">
          <label class="form-label">Payment Method</label>
          <div class="input-group">
            <select name="payment_method_id" id="payment_method_id" class="form-select" required>
              <option value="">-- Select Method --</option>
              <?php
              // reset pointer if needed by re-querying:
              $methods = $conn->query("SELECT id, name FROM payment_methods ORDER BY name");
              while ($m = $methods->fetch_assoc()):
              ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
              <?php endwhile; ?>
            </select>
            <button class="btn btn-outline-secondary" type="button" id="openAddMethodBtn">Add Method</button>
          </div>
          <small class="text-muted">If your method not present, click "Add Method" to add quickly.</small>
        </div>

        <div class="col-md-6">
          <label class="form-label">Payment Description</label>
          <input type="text" name="payment_desc" id="payment_desc" class="form-control">
        </div>
      </div>

      <div class="row g-3 mt-3">
        <div class="col-md-4">
          <label class="form-label">Purchase Date</label>
          <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>

      <div class="mt-3">
        <button class="btn btn-primary">Save Purchase</button>
      </div>
    </form>
  </div>

  <!-- Inline Add Payment Method Modal (simple) -->
  <div class="card p-3">
    <h6>Add Payment Method (quick)</h6>
    <form method="post" id="addMethodForm">
      <input type="hidden" name="action" value="add_method">
      <div class="row g-2 align-items-center">
        <div class="col-md-8">
          <input type="text" name="method_name" id="method_name" placeholder="e.g., bKash / Nagad / DBBL" class="form-control" required>
        </div>
        <div class="col-md-4">
          <button class="btn btn-outline-success" type="submit">Add Method</button>
        </div>
      </div>
    </form>
  </div>

</div>

<script>
// Calculate total price
(function(){
  const qty = document.getElementById('quantity');
  const unit = document.getElementById('unit_price');
  const total = document.getElementById('total_price');

  function calc(){
    const q = parseFloat(qty.value) || 0;
    const u = parseFloat(unit.value) || 0;
    total.value = (q * u).toFixed(2);
  }
  qty.addEventListener('input', calc);
  unit.addEventListener('input', calc);
  calc();

  // quick add method form: on submit, POST and reload page to show new method
  const addMethodForm = document.getElementById('addMethodForm');
  addMethodForm.addEventListener('submit', function(e){
    // allow normal submit (server handles and shows success), then redirect back to ensure dropdown updated
    // but to avoid losing user input, do an AJAX submit and then append new option to select
    e.preventDefault();
    const name = document.getElementById('method_name').value.trim();
    if(!name){ alert('Enter method name'); return; }

    // AJAX POST
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function(){
      if(xhr.readyState===4 && xhr.status===200){
        // try to parse response; simply reload page to update dropdown
        location.reload();
      }
    };
    xhr.send('action=add_method&method_name=' + encodeURIComponent(name));
  });

  // optional: filter subcategories based on selected category (client-side)
  const catSel = document.getElementById('category_select');
  const subSel = document.getElementById('subcategory_select');
  catSel.addEventListener('change', function(){
    const cat = this.value;
    for (const opt of subSel.options) {
      const dataCat = opt.getAttribute('data-cat');
      if(!dataCat) { opt.style.display = ''; continue; } // keep placeholder
      opt.style.display = (dataCat === cat || cat === '') ? '' : 'none';
    }
  });

})();
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
