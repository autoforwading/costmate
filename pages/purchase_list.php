<?php
include '../config/db.php';
include '../includes/header.php';
include '../includes/navbar.php';

$alertMessage = '';
$alertClass = '';

// Handle due payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_id'], $_POST['payment'])) {
    $purchase_id = intval($_POST['purchase_id']);
    $payment = floatval($_POST['payment']);

    // Get current purchase details
    $stmt = $conn->prepare("SELECT total_price, paid_amount FROM purchases WHERE id = ?");
    $stmt->bind_param("i", $purchase_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $purchase = $result->fetch_assoc();
        $total_price = floatval($purchase['total_price']);
        $paid_amount = floatval($purchase['paid_amount']);
        $new_paid = $paid_amount + $payment;

        if ($new_paid > $total_price) {
            $alertMessage = "Payment exceeds total amount!";
            $alertClass = "alert-danger";
        } elseif ($payment <= 0) {
            $alertMessage = "Please enter a valid payment amount!";
            $alertClass = "alert-warning";
        } else {
            $update = $conn->prepare("UPDATE purchases SET paid_amount = ? WHERE id = ?");
            $update->bind_param("di", $new_paid, $purchase_id);
            if ($update->execute()) {
                $alertMessage = "Payment updated successfully!";
                $alertClass = "alert-success";
            } else {
                $alertMessage = "Database update failed!";
                $alertClass = "alert-danger";
            }
        }
    } else {
        $alertMessage = "Purchase record not found!";
        $alertClass = "alert-warning";
    }
}

// Fetch all purchases
$sql = "SELECT p.*, s.name AS shop_name 
        FROM purchases p 
        LEFT JOIN shops s ON p.shop_id = s.id 
        ORDER BY p.id DESC";
$purchases = $conn->query($sql);
?>

<div class="container mt-4">
    <h3>Purchase List</h3>

    <?php if ($alertMessage): ?>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($alertMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-striped">
            <thead class="table-dark text-center">
                <tr>
                    <th>ID</th>
                    <th>Shop</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($purchases && $purchases->num_rows > 0): ?>
                    <?php while ($row = $purchases->fetch_assoc()): ?>
                        <?php $due = $row['total_price'] - $row['paid_amount']; ?>
                        <tr class="text-center">
                            <td><?= $row['id']; ?></td>
                            <td><?= htmlspecialchars($row['shop_name']); ?></td>
                            <td>৳<?= number_format($row['total_price'], 2); ?></td>
                            <td>৳<?= number_format($row['paid_amount'], 2); ?></td>
                            <td>৳<?= number_format($due, 2); ?></td>
                            <td><?= htmlspecialchars($row['purchase_date']); ?></td>
                            <td>
                                <?php if ($due > 0): ?>
                                    <button 
                                        class="btn btn-sm btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#payDueModal" 
                                        data-id="<?= $row['id']; ?>" 
                                        data-shop="<?= htmlspecialchars($row['shop_name']); ?>"
                                        data-total="<?= $row['total_price']; ?>"
                                        data-paid="<?= $row['paid_amount']; ?>"
                                        data-due="<?= $due; ?>"
                                    >Pay Due</button>
                                <?php else: ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">No purchase records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pay Due Modal -->
<div class="modal fade" id="payDueModal" tabindex="-1" aria-labelledby="payDueModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="payDueModalLabel">Pay Due for <span id="shopName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="purchase_id" id="purchaseId">
                    <p><strong>Total:</strong> ৳<span id="totalAmount"></span></p>
                    <p><strong>Paid:</strong> ৳<span id="paidAmount"></span></p>
                    <p><strong>Due:</strong> ৳<span id="dueAmount"></span></p>
                    <div class="form-group mt-3">
                        <label>Enter Payment Amount:</label>
                        <input type="number" name="payment" class="form-control" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Payment</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const payDueModal = document.getElementById('payDueModal');
    payDueModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        document.getElementById('shopName').textContent = button.getAttribute('data-shop');
        document.getElementById('purchaseId').value = button.getAttribute('data-id');
        document.getElementById('totalAmount').textContent = parseFloat(button.getAttribute('data-total')).toFixed(2);
        document.getElementById('paidAmount').textContent = parseFloat(button.getAttribute('data-paid')).toFixed(2);
        document.getElementById('dueAmount').textContent = parseFloat(button.getAttribute('data-due')).toFixed(2);
    });
</script>

<?php include '../includes/footer.php'; ?>
