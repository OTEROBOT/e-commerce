<?php
include "check_session.php";
if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}
include 'conn.php';
$sql = "SELECT * FROM product";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>รายการสินค้า</title>
    <style>
        body {
            background-color: #e6f3e6;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .navbar {
            background-color: #4CAF50;
            padding: 22px 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 30px;
            font-weight: 500;
            font-size: 18px;
        }
        .navbar a:hover {
            color: #e0e0e0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            background-color: #f0f8f0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .top-btn {
            text-align: right;
            margin-bottom: 20px;
        }
        .top-btn a {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .top-btn a:hover {
            background-color: #45a049;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #e0f0e0;
        }
        .product-table th, .product-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #b3d9b3;
        }
        .product-table th {
            background-color: #a3c9a3;
            color: white;
            font-weight: bold;
        }
        .product-table tr:hover {
            background-color: #d9e6d9;
        }
        .product-table img {
            max-width: 80px;
            height: auto;
            border-radius: 8px;
            border: 2px solid #8fb88f;
            padding: 2px;
            background-color: white;
            display: block;
        }
        .product-table .no-image {
            max-width: 80px;
            height: 80px;
            background-color: #d9e6d9;
            text-align: center;
            line-height: 80px;
            color: #4a704a;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="admin_profile.php">โปรไฟล์</a>
        <a href="product_list.php">ลิสสินค้า</a>
        <a href="showmember.php">จัดการสมาชิก</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div class="container">
        <div class="top-btn">
            <a href="addProduct_form.php">+ เพิ่มสินค้าใหม่</a>
        </div>

        <h2 style="text-align: center; color: #6b8e6b;">รายการสินค้า</h2>

        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<table class='product-table'>";
            echo "<thead><tr><th>รูปภาพ</th><th>รหัสสินค้า</th><th>ชื่อสินค้า</th><th>แหล่งที่มา</th><th>ราคา (บาท)</th><th>การจัดการ</th></tr></thead>";
            echo "<tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                // รูปภาพ
                $imageFile = trim($row['image'] ?? '');
                $imagePath = "./gallery_products/" . $imageFile;
                if (!empty($imageFile) && file_exists($imagePath)) {
                    echo "<td><img src='" . htmlspecialchars($imagePath) . "' alt='" . htmlspecialchars($row['product_name']) . "'></td>";
                } else {
                    echo "<td><div class='no-image'>ไม่มีภาพ</div></td>";
                }

                // รหัสสินค้า
                echo "<td>" . htmlspecialchars($row['productID'] ?? 'N/A') . "</td>";
                // ชื่อสินค้า
                echo "<td>" . htmlspecialchars($row['product_name'] ?? 'N/A') . "</td>";
                // แหล่งที่มา
                echo "<td>" . htmlspecialchars($row['origin'] ?? 'N/A') . "</td>";
                // ราคา
                echo "<td>" . number_format($row['price'] ?? 0, 2) . "</td>";
                // ลิงก์ดูรายละเอียด
                echo "<td><a href='show_product.php?id=" . htmlspecialchars($row['productID'] ?? '') . "'>ดูรายละเอียด</a></td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p style='text-align: center; color: #4a704a;'>ไม่มีสินค้าในรายการ</p>";
        }

        mysqli_close($conn);
        ?>
    </div>
</body>
</html>
