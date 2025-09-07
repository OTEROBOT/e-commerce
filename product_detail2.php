<?php
include "conn.php";

if (!isset($_GET['id'])) {
    die("❌ ไม่พบรหัสสินค้า");
}

$id = $conn->real_escape_string($_GET['id']); // ปลอดภัย + รองรับ string
$sql = "SELECT productID, product_name, origin, price, image, category, details 
        FROM product WHERE productID = '$id'";
$result = $conn->query($sql);


if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    die("❌ ไม่พบสินค้า");
}

// ดึงสินค้าที่เกี่ยวข้อง (หมวดเดียวกัน แต่ไม่ใช่ตัวเดียวกัน)
$related_sql = "SELECT productID, product_name, image, price 
                FROM product 
                WHERE category='{$product['category']}' 
                  AND productID != '$id' 
                LIMIT 4";
$related_result = $conn->query($related_sql);

$related_products = [];
if ($related_result && $related_result->num_rows > 0) {
    while ($row = $related_result->fetch_assoc()) {
        $related_products[] = $row;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($product['product_name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
  body {
    font-family: 'Sarabun', sans-serif;
    background: linear-gradient(135deg, #ff6b6b, #4ecdc4, #45b7d1, #96c93d);
    background-size: 400% 400%;
    animation: gradientBG 20s ease infinite;
    min-height: 100vh;
  }
  @keyframes gradientBG {
    0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}
  }
  .navbar {
    background-color: #1b5e20 !important;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  }
  .navbar a { color: white !important; font-weight: 600; }
  .navbar a:hover { color: #ffeb3b !important; }
  .product-main {
    background: rgba(255,255,255,0.95);
    border-radius: 20px;
    box-shadow: 0 15px 25px rgba(0,0,0,0.2);
    padding: 30px;
    margin-top: 30px;
    animation: fadeIn 1s ease-in-out;
  }
  @keyframes fadeIn { from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }
  .product-image { border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.3); transition: transform 0.3s; }
  .product-image:hover { transform: scale(1.05); }
  .price { color: #2e7d32; font-size: 1.8rem; font-weight: bold; }
  .btn-buy { background: #ff6f00; color: white; font-size: 1.2rem; padding: 12px 25px; border-radius: 10px; transition: transform 0.2s; }
  .btn-buy:hover { background: #ffa000; transform: scale(1.05); }
  .btn-back { background: #cfd8dc; color: #333; font-size: 1.1rem; }
  .btn-back:hover { background: #b0bec5; }
  .related-products h4 { color: #1b5e20; margin-bottom: 20px; }
  .card-related { transition: transform 0.3s, box-shadow 0.3s; cursor: pointer; }
  .card-related:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
  .card-related img { border-radius: 15px; object-fit: cover; height: 180px; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg px-5 py-3">
  <a class="navbar-brand" href="showProduct.php">หน้าร้านค้า</a>
  <a href="show_Profile.php">โปรไฟล์</a>
  <a href="logout.php">ออกจากระบบ</a>
</nav>

<div class="container product-main">
  <div class="row g-4">
    <div class="col-md-6">
      <img src="gallery_products/<?php echo $product['image'] ?: 'default.png'; ?>" class="img-fluid product-image" alt="">
    </div>
    <div class="col-md-6 d-flex flex-column justify-content-between">
      <div>
        <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
        <p class="text-muted">หมวดหมู่: <?php echo htmlspecialchars($product['category']); ?></p>
        <p><strong>แหล่งผลิต:</strong> <?php echo htmlspecialchars($product['origin']); ?></p>
        <p><?php echo nl2br(htmlspecialchars($product['details'])); ?></p>
        <p class="price">฿<?php echo number_format($product['price'], 2); ?></p>
      </div>
      <div class="d-flex gap-3 mt-3">
        <a href="cart.php?action=add&id=<?php echo $product['productID']; ?>" class="btn btn-buy flex-fill"><i class="fas fa-cart-shopping"></i> หยิบใส่ตะกร้า</a>
        <a href="showProduct.php" class="btn btn-back flex-fill">⬅ กลับไปหน้าสินค้า</a>
      </div>
    </div>
  </div>

  <?php if(count($related_products) > 0): ?>
  <div class="related-products mt-5">
    <h4>สินค้าแนะนำ (หมวด <?php echo htmlspecialchars($product['category']); ?>)</h4>
    <div class="row g-3">
      <?php foreach($related_products as $rp): ?>
      <div class="col-md-3">
        <div class="card card-related" onclick="window.location.href='product_detail2.php?id=<?php echo $rp['productID']; ?>'">
          <img src="gallery_products/<?php echo $rp['image'] ?: 'default.png'; ?>" class="card-img-top" alt="">
          <div class="card-body text-center">
            <h6><?php echo htmlspecialchars($rp['product_name']); ?></h6>
            <p class="text-success">฿<?php echo number_format($rp['price'],2); ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
