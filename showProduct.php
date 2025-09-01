<?php
include "conn.php";

// รับค่าค้นหาจาก GET
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : "";

if (!empty($keyword)) {
    $sql = "SELECT productID, product_name, origin, price, image 
            FROM product 
            WHERE product_name LIKE ?";
    $stmt = $conn->prepare($sql);
    $search = "%" . $keyword . "%";
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT productID, product_name, origin, price, image FROM product";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
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
        .text-brown {
            color: #8B4513;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.95);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        .container h1 {
            color: white;
        }
        .no-products {
            color: white;
        }
    </style>
</head>
<body>
    <!-- แถบนำทาง -->
    <nav class="navbar">
        <a href="showProduct.php">รายการสินค้า</a>
        <a href="show_profile.php">โปรไฟล์</a>
        <a href="cart.php">ตะกร้าสินค้า</a>
        <a href="myOrders.php">การสั่งซื้อ</a>
        <a href="logout.php">ออกจากระบบ</a>
    </nav>

    <div class="container py-5">
        <h1 class="mb-4 text-center">รายการสินค้า</h1>

        <!-- ฟอร์มค้นหา -->
        <form method="GET" action="showProduct.php" class="mb-4">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" 
                       placeholder="🔍 ค้นหาสินค้า..." 
                       value="<?php echo htmlspecialchars($keyword); ?>">
                <button type="submit" class="btn btn-primary">ค้นหา</button>
                <?php if (!empty($keyword)) { ?>
                    <a href="showProduct.php" class="btn btn-secondary">ล้าง</a>
                <?php } ?>
            </div>
        </form>

        <div class="row g-4">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $image_path = !empty($row['image']) && file_exists("gallery_products/" . $row['image']) 
                        ? "gallery_products/" . htmlspecialchars($row['image']) 
                        : "gallery_products/default.png";

                    // ย่อชื่อสินค้า
                    $short_name = (mb_strlen($row['product_name'], 'UTF-8') > 20) 
                        ? mb_substr($row['product_name'], 0, 20, 'UTF-8') . "..." 
                        : $row['product_name'];
            ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo $image_path; ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-brown text-truncate" 
                            title="<?php echo htmlspecialchars($row['product_name']); ?>">
                            <?php echo htmlspecialchars($short_name); ?>
                        </h5>
                        <p class="fw-bold text-success mb-2">
                            ฿<?php echo number_format($row['price'], 2); ?>
                        </p>
                        <div class="mt-auto">
                            <a href="cart.php?action=add&id=<?php echo $row['productID']; ?>" 
                               class="btn btn-primary w-100">
                               🛒 หยิบใส่ตะกร้า
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<p class='text-center no-products'>ไม่พบสินค้า</p>";
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
