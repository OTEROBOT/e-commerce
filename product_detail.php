<?php
include "conn.php";

// ดึงสินค้า
$sql = "SELECT productID, product_name, origin, price, image, category, details FROM product";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>หน้าร้านค้า</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96c93d);
      background-size: 400%;
      animation: colorShift 15s ease infinite;
      min-height: 100vh;
    }
    @keyframes colorShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    .navbar {
      background-color: #4CAF50;
      padding: 15px 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .navbar a {
      color: white;
      text-decoration: none;
      font-weight: 500;
      font-size: 18px;
      margin-right: 20px;
    }
    .navbar a:hover {
      color: #e0e0e0;
    }
    .product-card {
      background: #fff;
      border-radius: 10px;
      padding: 15px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      transition: transform 0.2s;
      cursor: pointer;
    }
    .product-card:hover {
      transform: scale(1.05);
    }
    .product-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <a href="show_Profile.php">โปรไฟล์</a>
    <a href="product_list.php">จัดการสินค้า</a>
    <a href="showProduct.php">หน้าร้านค้า</a>
    <a href="logout.php">ออกจากระบบ</a>
  </nav>

  <div class="container py-4">
    <h1 class="text-center text-white mb-4">หน้าร้านค้า</h1>

    <!-- Search & Filter -->
    <div class="row mb-4">
      <div class="col-md-6">
        <input type="text" id="searchInput" class="form-control" placeholder="ค้นหาสินค้า...">
      </div>
      <div class="col-md-6">
        <select id="categoryFilter" class="form-select">
          <option value="">-- เลือกหมวดหมู่ --</option>
          <option value="Shirts">Shirts</option>
          <option value="Shoes">Shoes</option>
          <option value="Accessories">Accessories</option>
          <option value="Jackets">Jackets</option>
          <option value="Hats">Hats</option>
          <option value="Pants">Pants</option>
        </select>
      </div>
    </div>

    <!-- Product Grid -->
    <div class="row g-3" id="productGrid"></div>

    <!-- Load More Button -->
    <div class="text-center mt-4">
      <button id="loadMoreBtn" class="btn btn-primary">โหลดเพิ่มเติม</button>
    </div>
  </div>

  <script>
    const products = <?php echo json_encode($products); ?>;
    let itemsPerPage = 8;
    let currentPage = 1;

    function renderProducts() {
      const search = document.getElementById('searchInput').value.toLowerCase();
      const category = document.getElementById('categoryFilter').value;
      const grid = document.getElementById('productGrid');
      grid.innerHTML = "";

      let filtered = products.filter(p => {
        let matchSearch = p.product_name.toLowerCase().includes(search);
        let matchCategory = category ? p.category === category : true;
        return matchSearch && matchCategory;
      });

      let start = 0;
      let end = itemsPerPage * currentPage;
      let visible = filtered.slice(start, end);

      visible.forEach(p => {
        let col = document.createElement('div');
        col.className = "col-md-3";
        col.innerHTML = `
          <div class="product-card" onclick="window.location.href='product_detail.php?id=${p.productID}'">
            <img src="gallery_products/${p.image ? p.image : 'default.png'}" alt="">
            <h5 class="mt-2">${p.product_name}</h5>
            <p>ราคา: ${p.price} บาท</p>
          </div>
        `;
        grid.appendChild(col);
      });

      document.getElementById('loadMoreBtn').style.display = 
        end < filtered.length ? 'inline-block' : 'none';
    }

    document.getElementById('searchInput').addEventListener('input', () => {
      currentPage = 1;
      renderProducts();
    });

    document.getElementById('categoryFilter').addEventListener('change', () => {
      currentPage = 1;
      renderProducts();
    });

    document.getElementById('loadMoreBtn').addEventListener('click', () => {
      currentPage++;
      renderProducts();
    });

    // Initial render
    renderProducts();
  </script>
</body>
</html>
