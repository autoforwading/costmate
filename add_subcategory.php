<?php
include 'includes/header.php';

$categories = $conn->query("SELECT * FROM categories");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addSubcategory'])) {
  $cat = $_POST['category_id'];
  $name = $_POST['name'];
  $stmt = $conn->prepare("INSERT INTO subcategories (category_id, name) VALUES (?, ?)");
  $stmt->bind_param("is", $cat, $name);
  $stmt->execute();
  echo "<div class='alert alert-success text-center'>Subcategory added successfully!</div>";
}

if (isset($_POST['editSubcategory']) && !empty($_POST['id'])) {
  $id = $_POST['id'];
  $name = mysqli_real_escape_string($conn,$_POST['name']);
  $category_id = mysqli_real_escape_string($conn,$_POST['category_id']);
  

  $run = mysqli_query($conn, "UPDATE subcategories SET name = '$name', category_id = '$category_id' WHERE id = '$id' ");

  if ($run) {
      echo "<div class='alert alert-success text-center'>Category updated successfully!</div>";
  } else {
      echo "<div class='alert alert-danger text-center'>Error updating category.</div>";
  }
}

if (isset($_GET['delSubcategory'])) {
  $id = $_GET['delSubcategory'];

  $run = mysqli_query($conn, "DELETE FROM subcategories WHERE id = '$id' ");

  if (isset($id)) {
    header("location: add_subcategory.php");
      // echo "<div class='alert alert-success text-center'>Shop Deleted successfully!$id</div>";
  } else {
      echo "<div class='alert alert-danger text-center'>Error Deleting category: $id</div>";
  }
}
?>

<div class="container">
  <h3 class="mb-4">Add Subcategory</h3>
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSubcategory">
    Add Subcategory
  </button>

  <!-- Modal -->
  <div class="modal fade" id="addSubcategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Add Subcategory</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form method="POST" action="add_subcategory.php">
            <div class="mb-3">
              <label class="form-label">Select Category</label>
              <select name="category_id" class="form-select" required>
                <option value="">Choose...</option>
                <?php while ($row = $categories->fetch_assoc()) {
                  echo "<option value='{$row['id']}'>{$row['name']}</option>";
                } ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Subcategory Name</label>
              <input type="text" name="name" required class="form-control">
            </div>
            <!-- <button type="submit" class="btn btn-primary">Save</button> -->
            <button type="submit" name="addSubcategory" class="btn btn-primary" style="float: right;">Save</button>
          </form>
        </div>
        <div class="modal-footer">
          <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button> -->
        </div>
      </div>
    </div>
  </div>

  <div class="my-4">
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>S.No</th>
                    <th>Subcategory Name</th>
                    <th>Category Name</th>
                    <th colspan="2">Actions</th>
                </tr>
            </thead>
            <tbody>
              <?php
                $sl = 0;
                $run = mysqli_query($conn, "SELECT sc.id, sc.name AS subcat_name, c.name AS cat_name FROM subcategories sc JOIN categories c ON sc.category_id = c.id");
                while ($row = mysqli_fetch_assoc($run)) {
                  $id = $row['id']; 
                  $subcat_name = $row['subcat_name']; 
                  $cat_name = $row['cat_name']; 
                  $sl++;
                  echo "
                    <tr>
                        <td>$sl</td>
                        <td>$subcat_name</td>
                        <td>$cat_name</td>
                        <td style=\"text-align: center;\">
                            <button class=\"btn btn-sm btn-primary\" data-toggle=\"modal\" data-target=\"#editSubcategory{$id}\">Edit</button>
                        </td>
                        <td style=\"text-align: center;\"> 
                          <a href=\"add_subcategory.php?delSubcategory=$id\" 
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

  <!-- edit Cagegories -->
   <?php
    $run = mysqli_query($conn, "SELECT * FROM subcategories");
    while ($row = mysqli_fetch_assoc($run)) {
      $id = $row['id']; $name = $row['name']; 
      $prevcategory_id = $row['category_id'];
      echo "
        <!-- edit shop modal -->
        <!-- Modal -->
        <div class=\"modal fade\" id=\"editSubcategory{$id}\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"exampleModalLabel\" aria-hidden=\"true\">
          <div class=\"modal-dialog\" role=\"document\">
            <div class=\"modal-content\">
              <div class=\"modal-header\">
                <h5 class=\"modal-title\" id=\"exampleModalLabel\">Edit Category</h5>
                <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                  <span aria-hidden=\"true\">&times;</span>
                </button>
              </div>
              <div class=\"modal-body\">
                <form method=\"POST\" action=\"add_subcategory.php\">
                  <input type=\"hidden\" name=\"id\" value=\"$id\" >
                  <div class=\"mb-3\">
                    <label class=\"form-label\">Select Shop</label>
                    <select name=\"category_id\" class=\"form-control\" required>";
                      if (!empty($prevcategory_id)) {
                        echo"<option value=\"$prevcategory_id\" selected>"; 
                        echo mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM categories WHERE id = '$prevcategory_id' "))["name"];
                        echo"</option>";
                      }else{echo"<option value=\"\" disabled selected>-- Select --</option>";}
                      
                        $subcatRun = mysqli_query($conn, "SELECT * FROM categories");
                        while ($subcatRow = mysqli_fetch_assoc($subcatRun)) {
                          $category_id = $subcatRow['id'];
                          if ($category_id == $prevcategory_id) continue;
                          $categoryname = $subcatRow['name'];
                          echo "<option value=\"$category_id\">$categoryname</option>";
                        }echo"
                      
                    </select>
                    <!-- <input type=\"text\" name=\"name\" required class=\"form-control\"> -->
                  </div>
                  <div class=\"mb-3\">
                    <label class=\"form-label\">Category Name</label>
                    <input type=\"text\" name=\"name\" value=\"$name\" required class=\"form-control\">
                  </div>
                  <div class=\"mb-3\">
                    <label class=\"form-label\">Phone</label>
                    <input type=\"text\" name=\"phone\" value=\"Shop\" class=\"form-control\">
                  </div>
                  <button type=\"submit\" name=\"editSubcategory\" class=\"btn btn-primary\" style=\"float: right;\">Save</button>
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
