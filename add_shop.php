<?php
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'];
  $phone = $_POST['phone'];
  $address = $_POST['address'];

  $stmt = $conn->prepare("INSERT INTO shops (name, phone, address) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $name, $phone, $address);
  $stmt->execute();
  echo "<div class='alert alert-success text-center'>Shop added successfully!</div>";
}
?>

<div class="container">
  <h3 class="mb-4">Add Shop</h3>
  <form method="POST" class="card p-4 shadow-sm">
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
    <button type="submit" class="btn btn-primary">Save</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
