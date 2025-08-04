<?php
include "check_session.php";
// ตรวจสอบว่าเป็น Admin หรือไม่ ถ้าไม่ใช่ให้ redirect ไปหน้า login
if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

include 'conn.php';
$id = $_GET['id'];
$sql = "SELECT * FROM product WHERE productID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
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
        }
        .container {
            width: 60%;
            margin: 50px auto;
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
        }
        h2 {
            color: #6b8e6b;
        }
        p {
            color: #4a704a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>รายละเอียดสินค้า</h2>
        <div class="product-detail">
            <img src="gallery_products/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>">
            <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
            <p><strong>รหัสสินค้า:</strong> <?php echo htmlspecialchars($row['productID']); ?></p>
            <p><strong>แหล่งที่มา:</strong> <?php echo htmlspecialchars($row['origin']); ?></p>
            <p><strong>ราคา:</strong> <?php echo number_format($row['price'], 2); ?> บาท</p>
            <p><strong>รายละเอียด:</strong> <?php echo htmlspecialchars($row['details']); ?></p>
        </div>
    </div>
    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>