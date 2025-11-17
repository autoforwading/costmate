<?php
include 'includes/header.php';

$categories = $conn->query("SELECT * FROM categories");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $cat = $_POST['category_id'];
  $name = $_POST['name'];
  $stmt = $conn->prepare("INSERT INTO subcategories (category_id, name) VALUES (?, ?)");
  $stmt->bind_param("is", $cat, $name);
  $stmt->execute();
  echo "<div class='alert alert-success text-center'>Subcategory added successfully!</div>";
}
?>

<div class="container">
  <h3 class="mb-4">Add Subcategory</h3>
  <form method="POST" class="card p-4 shadow-sm">
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
    <button type="submit" class="btn btn-primary">Save</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
