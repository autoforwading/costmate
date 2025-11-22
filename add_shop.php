<?php
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addShop'])) {
  $name = $_POST['name'];
  $phone = $_POST['phone'];
  $address = $_POST['address'];

  $stmt = $conn->prepare("INSERT INTO shops (name, phone, address) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $name, $phone, $address);
  $stmt->execute();
  echo "<div class='alert alert-success text-center'>Shop added successfully!</div>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editShop'])) {
  $id = $_POST['id'];
  $name = mysqli_real_escape_string($conn,$_POST['name']);
  $phone = mysqli_real_escape_string($conn,$_POST['phone']);
  $address = mysqli_real_escape_string($conn,$_POST['address']);

  $run = mysqli_query($conn, "UPDATE shops SET name = '$name', phone = '$phone', address = '$address' WHERE id = '$id' ");

  if ($run) {
      echo "<div class='alert alert-success text-center'>Shop updated successfully!</div>";
  } else {
      echo "<div class='alert alert-danger text-center'>Error updating shop.</div>";
  }
}

if (isset($_GET['delShop'])) {
  $id = $_GET['delShop'];

  $run = mysqli_query($conn, "DELETE FROM shops WHERE id = '$id' ");

  if (isset($id)) {
    header("location: add_shop.php");
      // echo "<div class='alert alert-success text-center'>Shop Deleted successfully!$id</div>";
  } else {
      echo "<div class='alert alert-danger text-center'>Error Deleting shop: $id</div>";
  }
}
?>

<div class="container">
  <h3 class="mb-4">Shop Manage</h3>
  <!-- Button trigger modal -->
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addShop">
    Add Shop
  </button>

  <!-- Modal -->
  <div class="modal fade" id="addShop" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Add Shop</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="POST" action="add_shop.php">
            <div class="mb-3">
              <label class="form-label">Shop Name</label>
              <input type="text" name="name" required class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control">
            </div>
            <div class="mb-3">
              <label class="form-label">Address</label>
              <textarea name="address" class="form-control"></textarea>
            </div>
            <button type="submit" name="addShop" class="btn btn-primary" style="float: right;">Save</button>
          </form>
        </div>
        <div class="modal-footer">
          <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button> -->
        </div>
      </div>
    </div>
  </div>



  <!-- shop list -->
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->

  <div class="my-4">
      <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle">
              <thead class="table-dark">
                  <tr>
                      <th>Sl</th>
                      <th>Shop Name</th>
                      <th>Number</th>
                      <th>Address</th>
                      <th colspan="2" style="text-align: center;">Actions</th>
                  </tr>
              </thead>
              <tbody>
                <?php
                  $sl = 0;
                  $run = mysqli_query($conn, "SELECT * FROM shops");
                  while ($row = mysqli_fetch_assoc($run)) {
                    $id = $row['id']; $name = $row['name']; $address = $row['address']; 
                    $phone = $row['phone']; $sl++;
                    echo "
                      <tr>
                          <td>$sl</td>
                          <td>$name</td>
                          <td>$phone</td>
                          <td>$address</td>
                          <td style=\"text-align: center;\">
                              <button class=\"btn btn-sm btn-primary\" data-toggle=\"modal\" data-target=\"#editShop{$id}\">Edit</button>
                          </td>
                          <td style=\"text-align: center;\"> 
                            <a href=\"add_shop.php?delShop=$id\" 
                            class=\"btn btn-sm btn-danger\" 
                            onclick=\"return confirm('Please confirm deletion');\"> 
                              Delete 
                            </a> 
                          </td>
                      </tr>
                    ";
                  }
                ?>
              </tbody>
          </table>
      </div>
  </div>




  

  <?php
    $run = mysqli_query($conn, "SELECT * FROM shops");
    while ($row = mysqli_fetch_assoc($run)) {
      $id = $row['id']; $name = $row['name']; $address = $row['address']; 
      $phone = $row['phone'];
      echo "
        <!-- edit shop modal -->
        <!-- Modal -->
        <div class=\"modal fade\" id=\"editShop{$id}\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"exampleModalLabel\" aria-hidden=\"true\">
          <div class=\"modal-dialog\" role=\"document\">
            <div class=\"modal-content\">
              <div class=\"modal-header\">
                <h5 class=\"modal-title\" id=\"exampleModalLabel\">Edit Shop</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                  <span aria-hidden=\"true\">&times;</span>
                </button>
              </div>
              <div class=\"modal-body\">
                <form method=\"POST\" action=\"add_shop.php\">
                  <input type=\"hidden\" name=\"id\" value=\"$id\" >
                  <div class=\"mb-3\">
                    <label class=\"form-label\">Shop Name</label>
                    <input type=\"text\" name=\"name\" value=\"$name\" required class=\"form-control\">
                  </div>
                  <div class=\"mb-3\">
                    <label class=\"form-label\">Phone</label>
                    <input type=\"text\" name=\"phone\" value=\"$phone\" class=\"form-control\">
                  </div>
                  <div class=\"mb-3\">
                    <label class=\"form-label\">Address</label>
                    <textarea name=\"address\" class=\"form-control\">$address</textarea>
                  </div>
                  <button type=\"submit\" name=\"editShop\" class=\"btn btn-primary\" style=\"float: right;\">Save</button>
                </form>
              </div>
              <div class=\"modal-footer\">
                <!-- <button type=\"button\" class=\"btn btn-secondary\" data-dismiss=\"modal\">Close</button>
                <button type=\"button\" class=\"btn btn-primary\">Save changes</button> -->
              </div>
            </div>
          </div>
        </div>
      ";
    }
  ?>

          
</div>

<?php include 'includes/footer.php'; ?>
