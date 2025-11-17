<?php
// includes/navbar.php
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/index.php">🏠 CostMate</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cmNav" aria-controls="cmNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="cmNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/index.php">Dashboard</a></li>
        <!-- <li class="nav-item"><a class="nav-link" href="/add_shop.php">Add Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="/add_category.php">Add Category</a></li>
        <li class="nav-item"><a class="nav-link" href="/add_subcategory.php">Add Subcategory</a></li> -->
        <li class="nav-item"><a class="nav-link" href="/add_purchase.php">Add Purchase</a></li>
        <li class="nav-item"><a class="nav-link" href="/payment.php">Payment</a></li>
        <li class="nav-item"><a class="nav-link" href="/reports.php">Reports</a></li>
      </ul>

      <ul class="navbar-nav ms-auto">
        <!-- <li class="nav-item">
          <a class="btn btn-sm btn-outline-primary me-2" href="/costmate/add_purchase.php">New Purchase</a>
        </li> -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="toolsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Tools</a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="toolsDropdown">
            <li><a class="dropdown-item" href="/add_shop.php">Manage Shops</a></li>
            <li><a class="dropdown-item" href="/add_category.php">Manage Categories</a></li>
            <li><a class="dropdown-item" href="/add_subcategory.php">Manage Subcategories</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/add_purchase.php">Add Purchase</a></li>
            <li><a class="dropdown-item" href="/purchase_list.php">Purchase List</a></li>
            <li><a class="dropdown-item" href="/reports.php">Reports</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>
