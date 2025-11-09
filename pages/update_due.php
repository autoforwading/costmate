<?php
include '../config/db.php';
include '../includes/header.php';
include '../includes/navbar.php';

$message = '';
$alertClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_id'], $_POST['payment'])) {

    $purchase_id = intval($_POST['purchase_id']);
    $payment = floatval($_POST['payment']);

    // Get current purchase details
    $query = $conn->prepare("SELECT total_price, paid_amount FROM purchases WHERE id = ?");
    $query->bind_param("i", $purchase_id);
    $query->execute();
    $result = $query->get_result();

    if ($result && $result->num_rows > 0) {
        $purchase = $result->fetch_assoc();
        $total_price = floatval($purchase['total_price']);
        $paid_amount = floatval($purchase['paid_amount']);
        $new_paid = $paid_amount + $payment;

        if ($new_paid > $total_price) {
            $message = "Payment exceeds total amount!";
            $alertClass = "alert-danger";
        } elseif ($payment <= 0) {
            $message = "Please enter a valid payment amount!";
            $alertClass = "alert-warning";
        } else {
            $update = $conn->prepare("UPDATE purchases SET paid_amount = ? WHERE id = ?");
            $update->bind_param("di", $new_paid, $purchase_id);

            if ($update->execute()) {
                $message = "Payment updated successfully!";
                $alertClass = "alert-success";
            } else {
                $message = "Database update failed!";
                $alertClass = "alert-danger";
            }
        }
    } else {
        $message = "Purchase record not found!";
        $alertClass = "alert-warning";
    }
} else {
    $message = '';
}
?>
<?php include '../includes/footer.php'; ?>