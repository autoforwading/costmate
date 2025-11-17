<?php
include 'includes/header.php';

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

$alertMessage = '';
$alertClass = '';

// --- Handle form submission for payment ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_method'], $_POST['pay_date'], $_POST['shop_id'])) {
    $shop_id = intval($_POST['shop_id']);
    $method_id = intval($_POST['pay_method']);
    $desc = trim($_POST['pay_description'] ?? '');
    $pay_date = $_POST['pay_date'];
    $amount = floatval($_POST['amount']);

    // --- Get method name from payment_methods table ---
    $method_name = '';
    $methodStmt = $conn->prepare("SELECT name FROM payment_methods WHERE id = ?");
    if ($methodStmt) {
        $methodStmt->bind_param("i", $method_id);
        $methodStmt->execute();
        $methodStmt->bind_result($method_name);
        $methodStmt->fetch();
        $methodStmt->close();
    }

    if ($amount <= 0) {
        $alertMessage = "Invalid payment amount.";
        $alertClass = "alert-warning";
    } else {
        $stmt = $conn->prepare("INSERT INTO payment_records (shop_id, amount, method, description, date) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("idsss", $shop_id, $amount, $method_name, $desc, $pay_date);
            if ($stmt->execute()) {
                $alertMessage = "Payment recorded successfully!";
                $alertClass = "alert-success";
            } else {
                $alertMessage = "Error saving payment record.";
                $alertClass = "alert-danger";
            }
            $stmt->close();
        } else {
            $alertMessage = "Database error: " . $conn->error;
            $alertClass = "alert-danger";
        }
    }
}

// --- Fetch all shops ---
$shopsRes = $conn->query("SELECT id, name FROM shops ORDER BY name ASC");

// --- Shop selected ---
$view_shop_id = isset($_GET['shop_id']) ? intval($_GET['shop_id']) : 0;
$purchases = null;
$totals = ['total'=>0.0, 'paid'=>0.0, 'due'=>0.0];
$payments = [];

if ($view_shop_id > 0) {
    // Total purchases
    $stmt = $conn->prepare("SELECT SUM(total_price) AS total FROM purchases WHERE shop_id = ?");
    $stmt->bind_param("i", $view_shop_id);
    $stmt->execute();
    $stmt->bind_result($total_purchase);
    $stmt->fetch();
    $stmt->close();
    $total_purchase = $total_purchase ?: 0;

    // Total payments
    $stmt = $conn->prepare("SELECT SUM(amount) AS total FROM payment_records WHERE shop_id = ?");
    $stmt->bind_param("i", $view_shop_id);
    $stmt->execute();
    $stmt->bind_result($total_payment);
    $stmt->fetch();
    $stmt->close();
    $total_payment = $total_payment ?: 0;

    $due = $total_purchase - $total_payment;
    $totals = ['total'=>$total_purchase, 'paid'=>$total_payment, 'due'=>$due];

    // Purchases list (JOIN categories for category name)
    $stmt = $conn->prepare("
        SELECT p.id, p.purchase_date, p.quantity, p.unit_price, p.total_price, 
               c.name AS category_name
        FROM purchases p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.shop_id = ?
        ORDER BY p.purchase_date DESC, p.id DESC
    ");
    $stmt->bind_param("i", $view_shop_id);
    $stmt->execute();
    $purchases = $stmt->get_result();

    // Payment records list
    $stmt = $conn->prepare("SELECT * FROM payment_records WHERE shop_id = ? ORDER BY date DESC, id DESC");
    $stmt->bind_param("i", $view_shop_id);
    $stmt->execute();
    $payments = $stmt->get_result();
    $stmt->close();
}

// --- Fetch payment methods ---
$methods = $conn->query("SELECT id, name FROM payment_methods ORDER BY name ASC");
?>

<div class="container mt-4">
    <h3>Shop Payments</h3>

    <?php if ($alertMessage): ?>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($alertMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>


    <?php if ($view_shop_id == 0): ?>
    <!-- 🔹 All Shops Table only when NO shop is selected -->
    <div class="card p-3 mb-4">
        <h5>All Shops</h5>
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>SL</th>
                        <th>Shop Name</th>
                        <th>Total Purchases (৳)</th>
                        <th>Total Payments (৳)</th>
                        <th>Due (৳)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($shopsRes && $shopsRes->num_rows > 0):
                        $i = 1;
                        while ($s = $shopsRes->fetch_assoc()):
                            $sid = $s['id'];
                            $p = $conn->query("SELECT SUM(total_price) AS total FROM purchases WHERE shop_id=$sid")->fetch_assoc()['total'] ?: 0;
                            $pay = $conn->query("SELECT SUM(amount) AS total FROM payment_records WHERE shop_id=$sid")->fetch_assoc()['total'] ?: 0;
                            $due = $p - $pay;
                            $active = ($view_shop_id == $sid) ? 'table-success' : '';
                            echo "<tr class='$active' style='cursor:pointer' onclick=\"window.location='?shop_id=$sid'\">";
                            echo "<td>$i</td>";
                            echo "<td>".htmlspecialchars($s['name'])."</td>";
                            echo "<td>".number_format($p,2)."</td>";
                            echo "<td>".number_format($pay,2)."</td>";
                            echo "<td class='".($due>0?'text-danger':'text-success')."'>".number_format($due,2)."</td>";
                            echo "</tr>";
                            $i++;
                        endwhile;
                    else:
                        echo '<tr><td colspan="5">No shops found.</td></tr>';
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>


    <?php if ($view_shop_id > 0): ?>
        <div class="card p-3 mb-4">
            <h5>
                Purchases for Shop:
                <?php
                $sname = $conn->query("SELECT name FROM shops WHERE id = $view_shop_id")->fetch_assoc()['name'] ?? '';
                echo htmlspecialchars($sname);
                ?>
            </h5>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit Price (৳)</th>
                            <th>Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($purchases && $purchases->num_rows > 0):
                            $i = 1;
                            while ($row = $purchases->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= htmlspecialchars($row['purchase_date']); ?></td>
                                <td><?= htmlspecialchars($row['category_name'] ?: '—'); ?></td>
                                <td><?= htmlspecialchars($row['quantity']); ?></td>
                                <td><?= number_format($row['unit_price'], 2); ?></td>
                                <td><?= number_format($row['total_price'], 2); ?></td>
                            </tr>
                        <?php endwhile;
                        else:
                            echo '<tr><td colspan="6">No purchases found.</td></tr>';
                        endif;
                        ?>
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="5" class="text-end">Total:</td>
                            <td>৳<?= number_format($totals['total'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#payModal"
                    <?= ($totals['due'] <= 0) ? 'disabled' : ''; ?>>
                    Pay (৳<?= number_format(max($totals['due'], 0), 2); ?>)
                </button>
            </div>
        </div>

        <!-- Payment Records Table -->
        <div class="card p-3 mb-4">
            <h5>Payment Records</h5>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>SL</th>
                            <th>Amount (৳)</th>
                            <th>Method</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_payment_sum = 0;
                        if ($payments && $payments->num_rows > 0):
                            $i = 1;
                            while ($row = $payments->fetch_assoc()):
                                $total_payment_sum += $row['amount'];
                        ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td><?= number_format($row['amount'], 2); ?></td>
                                <td><?= htmlspecialchars($row['method'] ?: '—'); ?></td>
                                <td><?= htmlspecialchars($row['description'] ?: '—'); ?></td>
                                <td><?= htmlspecialchars($row['date']); ?></td>
                                <td><?= htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php endwhile;
                        else:
                            echo '<tr><td colspan="6">No payment records found.</td></tr>';
                        endif;
                        ?>
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="5" class="text-end">Total Payments:</td>
                            <td>৳<?= number_format($total_payment_sum, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Payment Modal -->
        <div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <input type="hidden" name="shop_id" value="<?= $view_shop_id ?>">
                <div class="modal-header">
                  <h5 class="modal-title">Add Payment</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <select name="pay_method" class="form-select" required>
                      <option value="">-- Select Method --</option>
                      <?php
                      if ($methods && $methods->num_rows > 0) {
                          while ($m = $methods->fetch_assoc()) {
                              echo "<option value='{$m['id']}'>" . htmlspecialchars($m['name']) . "</option>";
                          }
                      }
                      ?>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" class="form-control" step="0.01" max="<?= $totals['due'] ?>" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="pay_description" class="form-control" placeholder="Optional note"></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="pay_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button class="btn btn-success" type="submit">Confirm Payment</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
    <?php endif; ?>
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

})();
</script>

<?php include 'includes/footer.php'; ?>
