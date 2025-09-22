<?php
// addProduct_form.php
// ฟอร์มเพิ่มสินค้า (สำหรับแอดมิน)
include "check_session.php";
if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มสินค้า</title>
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
            max-width: 600px;
            margin: 30px auto;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            color: #4a704a;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"],
        select,
        textarea,
        input[type="number"],
        input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #b3d9b3;
            border-radius: 5px;
            background-color: #e0f0e0;
            font-family: 'Sarabun', sans-serif;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        h2 {
            color: #6b8e6b;
            text-align: center;
            margin-bottom: 20px;
        }

        .error-message {
            color: #f44336;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
    </style>
    <script>
        function checkProductID() {
            const productID = document.getElementById('productID').value;
            const errorMessage = document.getElementById('productIDError');
            if (productID === '') {
                errorMessage.textContent = 'กรุณากรอกรหัสสินค้า';
                errorMessage.style.display = 'block';
                return;
            }

            // ตรวจสอบรหัสสินค้าซ้ำด้วย AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_product_id.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    if (xhr.responseText === 'exists') {
                        errorMessage.textContent = 'รหัสสินค้านี้มีอยู่แล้ว';
                        errorMessage.style.display = 'block';
                        document.getElementById('submitBtn').disabled = true;
                    } else {
                        errorMessage.style.display = 'none';
                        document.getElementById('submitBtn').disabled = false;
                    }
                }
            };
            xhr.send('productID=' + encodeURIComponent(productID));
        }
    </script>
</head>
<body>
    <nav class="navbar">
        <a href="admin_profile.php">โปรไฟล์</a>
        <a href="product_list.php">ลิสสินค้า</a>
        <a href="showmember.php">จัดการสมาชิก</a>
        <a href="logout.php">ออกจากระบบ</a>
    </nav>

    <div class="container">
        <h2>เพิ่มสินค้าใหม่</h2>
        <form action="add_product.php" method="post" enctype="multipart/form-data" id="productForm">
            <div class="form-group">
                <label for="productID">รหัสสินค้า (ต้องไม่ซ้ำ)</label>
                <input type="text" name="productID" id="productID" required placeholder="เช่น PRD001" oninput="checkProductID()">
                <div id="productIDError" class="error-message"></div>
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
                <textarea name="detail" rows="4" placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับสินค้า"></textarea>
            </div>
            <div class="form-group">
                <label for="image">อัปโหลดรูปภาพ</label>
                <input type="file" name="image" accept="image/*" required>
            </div>
            <input type="submit" id="submitBtn" value="บันทึก">
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>