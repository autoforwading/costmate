<?php
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addCategory'])) {
  $shopid = $_POST['shopid'];
  $name = $_POST['name'];
  $stmt = $conn->prepare("INSERT INTO categories (shopid, name) VALUES (?, ?)");
  $stmt->bind_param("is", $shopid, $name);
  $stmt->execute();
  echo "<div class='alert alert-success text-center'>Category added successfully!</div>";
}

if (isset($_POST['editCategory']) && !empty($_POST['id'])) {
  $id = $_POST['id'];
  $name = mysqli_real_escape_string($conn,$_POST['name']);
  $shopid = mysqli_real_escape_string($conn,$_POST['shopid']);
  

  $run = mysqli_query($conn, "UPDATE categories SET name = '$name', shopid = '$shopid' WHERE id = '$id' ");

  if ($run) {
      echo "<div class='alert alert-success text-center'>Category updated successfully!</div>";
  } else {
      echo "<div class='alert alert-danger text-center'>Error updating category.</div>";
  }
}

if (isset($_GET['delCategory'])) {
  $id = $_GET['delCategory'];

  $run = mysqli_query($conn, "DELETE FROM categories WHERE id = '$id' ");

  if (isset($id)) {
    header("location: add_category.php");
      // echo "<div class='alert alert-success text-center'>Shop Deleted successfully!$id</div>";
  } else {
      echo "<div class='alert alert-danger text-center'>Error Deleting category: $id</div>";
  }
}
?>

<div class="container">
  <h3 class="mb-4">Manage Category</h3>
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCategory">
    Add Category
  </button>

  <!-- Modal -->
  <div class="modal fade" id="addCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Add Shop</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="POST" action="add_category.php">
            <div class="mb-3">
              <label class="form-label">Select Shop</label>
              <select name="shopid" class="form-control" required>
                <option value="" disabled selected>-- Select --</option>
                <?php
                  $shopRun = mysqli_query($conn, "SELECT * FROM shops");
                  while ($shopRow = mysqli_fetch_assoc($shopRun)) {
                    $shopId = $shopRow['id'];
                    $shopName = $shopRow['name'];
                    echo "<option value=\"$shopId\">$shopName</option>";
                  }
                ?>
              </select>
              <!-- <input type="text" name="name" required class="form-control"> -->
            </div>

            <div class="mb-3">
              <label class="form-label">Category Name</label>
              <input type="text" name="name" required class="form-control">
            </div>
            <!-- <button type="submit" class="btn btn-primary">Save</button> -->
            <button type="submit" name="addCategory" class="btn btn-primary" style="float: right;">Save</button>
          </form>
        </div>
        <div class="modal-footer">
          <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button> -->
        </div>
      </div>
    </div>
  </div>


  <div class="table-responsive my-4">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-dark">
        <tr>
            <th>Sl</th>
            <th>Category Name</th>
            <th>Shop</th>
            <th>Address</th>
            <th colspan="2" style="text-align: center;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $sl = 0;
          $run = mysqli_query($conn, "SELECT * FROM categories");
          while ($row = mysqli_fetch_assoc($run)) {
            $id = $row['id']; $name = $row['name']; 
            $shopid = $row['shopid']; 
            $shopname = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM shops WHERE id = '$shopid' "))["name"];
            $sl++;
            echo "
              <tr>
                  <td>$sl</td>
                  <td>$name</td>
                  <td>$shopname</td>
                  <td style=\"text-align: center;\">
                      <button class=\"btn btn-sm btn-primary\" data-toggle=\"modal\" data-target=\"#editCategory{$id}\">Edit</button>
                  </td>
                  <td style=\"text-align: center;\"> 
                    <a href=\"add_category.php?delCategory=$id\" 
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


  <!-- edit Cagegories -->
   <?php
    $run = mysqli_query($conn, "SELECT * FROM categories");
    while ($row = mysqli_fetch_assoc($run)) {
      $id = $row['id']; $name = $row['name']; 
      $prevshopid = $row['shopid']; 
      echo "
        <!-- edit shop modal -->
        <!-- Modal -->
        <div class=\"modal fade\" id=\"editCategory{$id}\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"exampleModalLabel\" aria-hidden=\"true\">
          <div class=\"modal-dialog\" role=\"document\">
            <div class=\"modal-content\">
              <div class=\"modal-header\">
                <h5 class=\"modal-title\" id=\"exampleModalLabel\">Edit Category</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                  <span aria-hidden=\"true\">&times;</span>
                </button>
              </div>
              <div class=\"modal-body\">
                <form method=\"POST\" action=\"add_category.php\">
                  <input type=\"hidden\" name=\"id\" value=\"$id\" >
                  <div class=\"mb-3\">
                    <label class=\"form-label\">Select Shop</label>
                    <select name=\"shopid\" class=\"form-control\" required>";
                      if (!empty($prevshopid)) {
                        echo"<option value=\"$prevshopid\" selected>"; 
                        echo mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM shops WHERE id = '$prevshopid' "))["name"];
                        echo"</option>";
                      }else{echo"<option value=\"\" disabled selected>-- Select --</option>";}
                      
                        $shopRun = mysqli_query($conn, "SELECT * FROM shops");
                        while ($shopRow = mysqli_fetch_assoc($shopRun)) {
                          $shopId = $shopRow['id'];
                          $shopName = $shopRow['name'];
                          echo "<option value=\"$shopId\">$shopName</option>";
                        }echo"
                      
                    </select>
                    <!-- <input type=\"text\" name=\"name\" required class=\"form-control\"> -->
                  </div>
                  <div class=\"mb-3\">
                    <label class=\"form-label\">Category Name</label>
                    <input type=\"text\" name=\"name\" value=\"$name\" required class=\"form-control\">
                  </div>
                  <!-- div class=\"mb-3\">
                    <label class=\"form-label\">Phone</label>
                    <input type=\"text\" name=\"phone\" value=\"Shop\" class=\"form-control\">
                  </div -->
                  <button type=\"submit\" name=\"editCategory\" class=\"btn btn-primary\" style=\"float: right;\">Save</button>
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

  <!-- <form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">Category Name</label>
      <input type="text" name="name" required class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
  </form> -->
</div>

<?php include 'includes/footer.php'; ?>
