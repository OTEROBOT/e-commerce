<?php
include "check_session.php";
// ตรวจสอบว่าเป็น Admin หรือไม่ ถ้าไม่ใช่ให้ redirect ไปหน้า login
if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

include 'conn.php';
$sql = "SELECT * FROM product";
$result = mysqli_query($conn, $sql);

// ตรวจสอบข้อผิดพลาดจากการ query
if (!$result) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            background-color: #e6f3e6;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
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
        h2 {
            color: #6b8e6b;
            text-align: center;
            margin-bottom: 20px;
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
        .debug-info {
            color: #d32f2f;
            font-weight: bold;
            margin: 5px 0;
            background-color: #ffebee;
            padding: 5px;
            border-radius: 3px;
            font-size: 12px;
        }
        a {
            color: #8fb88f;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            color: #6b8e6b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>รายการสินค้า</h2>
        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<table class='product-table'>";
            echo "<thead><tr><th>รูปภาพ</th><th>รหัสสินค้า</th><th>ชื่อสินค้า</th><th>แหล่งที่มา</th><th>ราคา (บาท)</th><th>การจัดการ</th></tr></thead>";
            echo "<tbody>";
            $result->data_seek(0); // รีเซ็ต pointer
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>";
                $imagePath = "./gallery_products/" . htmlspecialchars(trim($row['image'])); // ใช้ trim เพื่อลบช่องว่าง
                if (!empty($row['image']) && file_exists($imagePath)) {
                    echo "<img src='" . $imagePath . "' alt='" . htmlspecialchars($row['product_name']) . "'>";
                } else {
                    echo "<div class='no-image'>ไม่มีภาพ</div>";
                    echo "<div class='debug-info'>Image Path: " . $imagePath . "</div>";
                    echo "<div class='debug-info'>Image Value: " . htmlspecialchars($row['image'] ?? 'ไม่มี') . "</div>";
                }
                echo "</td>";
                echo "<td>" . htmlspecialchars($row['productID'] ?? 'N/A') . "</td>";
                if (empty($row['productID'])) {
                    echo "<div class='debug-info'>รหัสสินค้าไม่พบ</div>";
                }
                echo "<td>" . htmlspecialchars($row['product_name'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($row['origin'] ?? 'N/A') . "</td>";
                echo "<td>" . number_format($row['price'] ?? 0, 2) . "</td>";
                echo "<td><a href='show_product.php?id=" . ($row['productID'] ?? '') . "'>ดูรายละเอียด</a></td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p style='text-align: center; color: #4a704a;'>ไม่มีสินค้าในรายการ</p>";
        }
        ?>
        <?php mysqli_close($conn); ?>
    </div>
</body>
</html>