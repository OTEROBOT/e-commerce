<?php
include "check_session.php";
if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>เพิ่มสินค้า</title>
    <style>
        body { background-color: #e6f3e6; font-family: Arial, sans-serif; margin: 0; }
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
            width: 50%;
            margin: 50px auto;
            background-color: #f0f8f0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            color: #4a704a;
            font-weight: bold;
        }
        input[type="text"],
        select,
        textarea,
        input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #b3d9b3;
            border-radius: 5px;
            background-color: #e0f0e0;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        function generateProductID() {
            const now = new Date();
            const year = now.getFullYear().toString().slice(-2);
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hour = String(now.getHours()).padStart(2, '0');
            const minute = String(now.getMinutes()).padStart(2, '0');
            const randomNum = Math.floor(Math.random() * 100).toString().padStart(2, '0');
            const productID = `PRD${year}${month}${day}${hour}${minute}${randomNum}`;
            document.getElementById('productID').value = productID;
        }

        window.onload = function() {
            generateProductID();
            document.getElementById('productForm').reset();
        };
    </script>
</head>
<body>
    <div class="navbar">
        <a href="admin_profile.php">โปรไฟล์</a>
        <a href="product_list.php">ลิสสินค้า</a>
        <a href="showmember.php">จัดการสมาชิก</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div class="container">
        <h2 style="color: #6b8e6b;">เพิ่มสินค้าใหม่</h2>
        <form action="add_product.php" method="post" enctype="multipart/form-data" id="productForm">
            <div class="form-group">
                <label for="productID">รหัสสินค้า</label>
                <input type="text" name="productID" id="productID" required readonly>
            </div>
            <div class="form-group">
                <label for="product_name">ชื่อสินค้า</label>
                <input type="text" name="product_name" required>
            </div>
            <div class="form-group">
                <label for="origin">แหล่งที่มา</label>
                <select name="origin" required>
                    <option value="">-- เลือกประเทศ --</option>
                    <option value="Thailand">Thailand</option>
                    <option value="Ethiopia">Ethiopia</option>
                    <option value="Columbia">Columbia</option>
                    <option value="Brazil">Brazil</option>
                    <option value="Vietnam">Vietnam</option>
                    <option value="India">India</option>
                    <option value="Kenya">Kenya</option>
                    <option value="Indonesia">Indonesia</option>
                    <option value="Mexico">Mexico</option>
                    <option value="Peru">Peru</option>
                </select>
            </div>
            <div class="form-group">
                <label for="price">ราคา (บาท)</label>
                <input type="number" name="price" min="0" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="detail">รายละเอียด</label>
                <textarea name="detail" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="image">อัปโหลดรูปภาพ</label>
                <input type="file" name="image" accept="image/*" required>
            </div>
            <input type="submit" value="บันทึก">
        </form>
    </div>
</body>
</html>
