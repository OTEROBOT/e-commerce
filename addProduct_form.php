<?php
include "check_session.php";
// ตรวจสอบว่าเป็น Admin หรือไม่ ถ้าไม่ใช่ให้ redirect ไปหน้า login
if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body { background-color: #e6f3e6; font-family: Arial, sans-serif; }
        .container { width: 50%; margin: 50px auto; background-color: #f0f8f0; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .form-group { margin-bottom: 15px; }
        label { color: #4a704a; font-weight: bold; }
        input[type="text"], select, textarea, input[type="number"] { width: 100%; padding: 8px; border: 1px solid #b3d9b3; border-radius: 5px; background-color: #e0f0e0; }
        input[type="submit"] { background-color: #a3c9a3; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover { background-color: #8fb88f; }
        select option { background-color: #d9e6d9; }
    </style>
    <script>
        // ฟังก์ชันสร้างรหัสสินค้าอัตโนมัติเป็นค่าเริ่มต้น
        function generateProductID() {
            const now = new Date();
            const year = now.getFullYear().toString().slice(-2); // ใช้ 2 ตัวท้ายของปี (เช่น 25)
            const month = String(now.getMonth() + 1).padStart(2, '0'); // เดือน 01-12
            const day = String(now.getDate()).padStart(2, '0'); // วันที่ 01-31
            const hour = String(now.getHours()).padStart(2, '0'); // ชั่วโมง 00-23
            const minute = String(now.getMinutes()).padStart(2, '0'); // นาที 00-59
            const randomNum = Math.floor(Math.random() * 100).toString().padStart(2, '0'); // สุ่ม 00-99
            const productID = `PRD${year}${month}${day}${hour}${minute}${randomNum}`; // รูปแบบ: PRDYYMMDDHHMMXX
            document.getElementById('productID').value = productID;
        }

        // ฟังก์ชันรีเซ็ตฟอร์มเมื่อโหลดหน้าใหม่
        window.onload = function() {
            generateProductID(); // ตั้งค่ารหัสสินค้าเริ่มต้น
            document.getElementById('productForm').reset(); // รีเซ็ตฟอร์มทั้งหมด
        };
    </script>
</head>
<body>
    <div class="container">
        <h2 style="color: #6b8e6b;">เพิ่มสินค้า</h2>
        <form action="add_product.php" method="post" enctype="multipart/form-data" id="productForm">
            <div class="form-group">
                <label for="productID">รหัสสินค้า</label>
                <input type="text" name="productID" id="productID" required>
            </div>
            <div class="form-group">
                <label for="product_name">ชื่อสินค้า</label>
                <input type="text" name="product_name" required>
            </div>
            <div class="form-group">
                <label for="origin">แหล่งที่มา</label>
                <select name="origin" required>
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
                <input type="file" name="image" required>
            </div>
            <input type="submit" value="บันทึก">
        </form>
    </div>
</body>
</html>