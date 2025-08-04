<?php
include "check_session.php";
// ตรวจสอบว่าเป็น Admin หรือไม่ ถ้าไม่ใช่ให้ redirect ไปหน้า login
if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

include 'conn.php';

$id = $_GET['id'] ?? '';

$sql = "SELECT * FROM product WHERE productID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            background-color: #e6f3e6;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .navbar {
            background-color: #a3c9a3;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
        }
        .navbar a:hover {
            color: #f0f0f0;
        }

        .container {
            width: 60%;
            margin: 30px auto;
            background-color: #f0f8f0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .product-detail {
            text-align: center;
        }
        .product-detail img {
            max-width: 300px;
            border-radius: 10px;
            border: 2px solid #8fb88f;
            background: white;
            padding: 5px;
        }
        h2 {
            color: #6b8e6b;
        }
        p {
            color: #4a704a;
        }
        .back-button {
            margin-top: 20px;
            text-align: center;
        }
        .back-button a {
            display: inline-block;
            background-color: #8fb88f;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .back-button a:hover {
            background-color: #6b8e6b;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="product_list.php">← กลับหน้ารายการสินค้า</a>
        <div style="color: white;">ระบบจัดการสินค้า</div>
    </div>

    <div class="container">
        <h2>รายละเอียดสินค้า</h2>
        <div class="product-detail">
            <?php if (!empty($row['image']) && file_exists("gallery_products/" . $row['image'])): ?>
                <img src="gallery_products/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
            <?php else: ?>
                <div style="width: 300px; height: 300px; background-color: #d9e6d9; line-height: 300px; color: #4a704a; border-radius: 10px; margin: auto;">
                    ไม่มีภาพสินค้า
                </div>
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
            <p><strong>รหัสสินค้า:</strong> <?php echo htmlspecialchars($row['productID']); ?></p>
            <p><strong>แหล่งที่มา:</strong> <?php echo htmlspecialchars($row['origin']); ?></p>
            <p><strong>ราคา:</strong> <?php echo number_format($row['price'], 2); ?> บาท</p>
            <p><strong>รายละเอียด:</strong> <?php echo htmlspecialchars($row['details']); ?></p>
        </div>
        <div class="back-button">
            <a href="product_list.php">← กลับไปหน้ารายการสินค้า</a>
        </div>
    </div>

    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>
