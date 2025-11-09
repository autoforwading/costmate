<?php
include '../config/db.php';
include '../includes/header.php';
include '../includes/navbar.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'];
  $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
  $stmt->bind_param("s", $name);
  $stmt->execute();
  echo "<div class='alert alert-success text-center'>Category added successfully!</div>";
}
?>

<div class="container">
  <h3 class="mb-4">Add Category</h3>
  <form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">Category Name</label>
      <input type="text" name="name" required class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
