<?php
include "check_session.php";
include "conn.php";

if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

// ดึงข้อมูลสินค้าจากตาราง product
$sql = "SELECT productID, product_name, origin, price, details, image FROM product";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการสินค้า (แอดมิน)</title>
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

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #6b8e6b;
            text-align: center;
            margin-bottom: 20px;
        }

        .table {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
        }

        .table th {
            background-color: #4CAF50;
            color: white;
        }

        .table img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .btn-add {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }

        .btn-add:hover {
            background-color: #45a049;
            color: white;
        }

        .btn-edit {
            background-color: #2196f3;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .btn-edit:hover {
            background-color: #1976d2;
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .btn-delete:hover {
            background-color: #d32f2f;
        }

        .success-message {
            color: #4caf50;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="admin_profile.php">โปรไฟล์</a>
        <a href="product_list.php">ลิสสินค้า</a>
        <a href="showmember.php">จัดการสมาชิก</a>
        <a href="adminOrders.php">ดูหน้ารายการสินค้าทั้งหมด</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div class="container">
        <h2>รายการสินค้า</h2>
        <?php if (isset($_GET['msg'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
        <a href="addProduct_form.php" class="btn-add">เพิ่มสินค้าใหม่</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>รหัสสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>แหล่งที่มา</th>
                    <th>ราคา (บาท)</th>
                    <th>รายละเอียด</th>
                    <th>รูปภาพ</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <?php
                        $image_path = !empty($row['image']) && file_exists("gallery_products/" . $row['image'])
                            ? "gallery_products/" . htmlspecialchars($row['image'])
                            : "gallery_products/default.png";
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['productID']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['origin']); ?></td>
                            <td><?php echo number_format($row['price'], 2); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($row['details'])); ?></td>
                            <td><img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>"></td>
                            <td>
                                <a href="edit_product_form.php?productID=<?php echo urlencode($row['productID']); ?>" class="btn btn-edit">แก้ไข</a>
                                <a href="delete_product.php?productID=<?php echo urlencode($row['productID']); ?>" class="btn btn-delete" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?');">ลบ</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">ยังไม่มีสินค้า</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>