<?php
include 'includes/header.php';

// summary totals
$totalExpense = $conn->query("SELECT SUM(total_price) AS total FROM purchases")->fetch_assoc()['total'] ?? 0;
$totalPaid = $conn->query("SELECT SUM(amount) AS total FROM payment_records")->fetch_assoc()['total'] ?? 0;
$totalDue = $totalExpense - $totalPaid;
$totalShops = $conn->query("SELECT COUNT(*) AS total FROM shops")->fetch_assoc()['total'] ?? 0;
?>

<div class="container">
  <h3 class="mb-4">Dashboard</h3>

  <!-- Summary Cards -->
  <div class="row g-3">
    <div class="col-md-3">
      <div class="card shadow-sm border-0 p-3 text-center">
        <h5>Total Expense</h5>
        <h3 class="text-danger">৳<?php echo number_format($totalExpense, 2); ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm border-0 p-3 text-center">
        <h5>Total Paid</h5>
        <h3 class="text-success">৳<?php echo number_format($totalPaid, 2); ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm border-0 p-3 text-center">
        <h5>Total Due</h5>
        <h3 class="text-warning">৳<?php echo number_format($totalDue, 2); ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm border-0 p-3 text-center">
        <h5>Total Shops</h5>
        <h3><?php echo $totalShops; ?></h3>
      </div>
    </div>
  </div>

  <!-- General Purchase Report Section -->
  <div class="mt-5">
    <h4>📊 General Purchase Report</h4>
    <table class="table table-bordered table-striped mt-3">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Shop</th>
          <th>Product</th>
          <th>Description</th>
          <th>Unit Price</th>
          <th>Total Price</th>
          <th>Purchase Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT 
                p.id,
                s.name AS shop_name,
                sc.name AS product_name,
                c.name AS category_name,
                p.unit_price,
                p.total_price,
                p.quantity,
                p.description,
                pm.name AS payment_method,
                p.purchase_date
            FROM purchases p
            LEFT JOIN shops s ON p.shop_id = s.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
            LEFT JOIN payment_methods pm ON p.payment_method_id = pm.id
            ORDER BY p.id DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $i = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$i}</td>
                        <td>{$row['shop_name']}</td>
                        <td>{$row['category_name']}</td>
                        <td>{$row['description']}</td>
                        <td>৳{$row['unit_price']}</td>
                        <td>৳{$row['total_price']}</td>
                        <td>{$row['purchase_date']}</td>
                        <td>
                          <button class='btn btn-sm btn-primary' 
                                  data-bs-toggle='modal' 
                                  data-bs-target='#viewModal{$row['id']}'>
                            View
                          </button>
                        </td>
                      </tr>";

                // Modal for full details
                echo "
                <div class='modal fade' id='viewModal{$row['id']}' tabindex='-1'>
                  <div class='modal-dialog'>
                    <div class='modal-content'>
                      <div class='modal-header'>
                        <h5 class='modal-title'>Purchase Details</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                      </div>
                      <div class='modal-body'>
                        <p><strong>Shop:</strong> {$row['shop_name']}</p>
                        <p><strong>Category:</strong> {$row['category_name']}</p>
                        <p><strong>Subcategory:</strong> {$row['product_name']}</p>
                        <p><strong>Quantity:</strong> {$row['quantity']}</p>
                        <p><strong>Unit Price:</strong> ৳{$row['unit_price']}</p>
                        <p><strong>Total Price:</strong> ৳{$row['total_price']}</p>
                        <!--p><strong>Paid Amount:</strong> ৳ </p-->
                        <!--p><strong>Due:</strong> ৳ </p-->
                        <!--p><strong>Payment Method:</strong>  </p-->
                        <!--p><strong>Payment Description:</strong> </p-->
                        <p><strong>Purchase Date:</strong> {$row['purchase_date']}</p>
                        <p><strong>Description:</strong> {$row['description']}</p>
                      </div>
                      <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                      </div>
                    </div>
                  </div>
                </div>";
                $i++;
            }
        } else {
            echo "<tr><td colspan='9' class='text-center'>No purchase records found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
