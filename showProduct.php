<?php
include "conn.php";
// ดึงข้อมูลจากตาราง product
$sql = "SELECT productID, product_name, origin, price, details AS detail, image FROM product";
$result = $conn->query($sql);
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
        <a href="logout.php">ออกจากระบบ</a>
    </nav>

    <div class="container py-5">
        <h1 class="mb-4 text-center">รายการสินค้า</h1>
        <div class="row g-4">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $image_path = !empty($row['image']) && file_exists("gallery_products/" . $row['image']) 
                        ? "gallery_products/" . htmlspecialchars($row['image']) 
                        : "gallery_products/default.png";
            ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo $image_path; ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-brown">
                            <?php echo htmlspecialchars($row['product_name']); ?>
                        </h5>
                        <p class="card-text small mb-2"><?php echo htmlspecialchars($row['detail']); ?></p>
                        <p class="card-text mb-1">
                            <span class="fw-bold">แหล่งผลิต:</span> <?php echo htmlspecialchars($row['origin']); ?>
                        </p>
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
                echo "<p class='text-center no-products'>ยังไม่มีสินค้า</p>";
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>