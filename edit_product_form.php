<?php
include "check_session.php";
include "conn.php";

if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

if (!isset($_GET['productID'])) {
    header("Location: product_list.php?error=" . urlencode("ไม่พบรหัสสินค้า"));
    exit();
}

$productID = $_GET['productID'];
$sql = "SELECT productID, product_name, origin, price, details, image FROM product WHERE productID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $productID);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: product_list.php?error=" . urlencode("ไม่พบสินค้า"));
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า</title>
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
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="admin_profile.php">โปรไฟล์</a>
        <a href="product_list.php">ลิสสินค้า</a>
        <a href="showmember.php">จัดการสมาชิก</a>
        <a href="logout.php">ออกจากระบบ</a>
    </nav>

    <div class="container">
        <h2>แก้ไขสินค้า</h2>
        <form action="edit_product.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="productID" value="<?php echo htmlspecialchars($product['productID']); ?>">
            <div class="form-group">
                <label for="product_name">ชื่อสินค้า</label>
                <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="origin">แหล่งที่มา</label>
                <select name="origin" required>
                    <option value="">-- เลือกประเทศ --</option>
                    <option value="Thailand" <?php echo $product['origin'] === 'Thailand' ? 'selected' : ''; ?>>Thailand</option>
                    <option value="Ethiopia" <?php echo $product['origin'] === 'Ethiopia' ? 'selected' : ''; ?>>Ethiopia</option>
                    <option value="Columbia" <?php echo $product['origin'] === 'Columbia' ? 'selected' : ''; ?>>Columbia</option>
                    <option value="Brazil" <?php echo $product['origin'] === 'Brazil' ? 'selected' : ''; ?>>Brazil</option>
                    <option value="Vietnam" <?php echo $product['origin'] === 'Vietnam' ? 'selected' : ''; ?>>Vietnam</option>
                    <option value="India" <?php echo $product['origin'] === 'India' ? 'selected' : ''; ?>>India</option>
                    <option value="Kenya" <?php echo $product['origin'] === 'Kenya' ? 'selected' : ''; ?>>Kenya</option>
                    <option value="Indonesia" <?php echo $product['origin'] === 'Indonesia' ? 'selected' : ''; ?>>Indonesia</option>
                    <option value="Mexico" <?php echo $product['origin'] === 'Mexico' ? 'selected' : ''; ?>>Mexico</option>
                    <option value="Peru" <?php echo $product['origin'] === 'Peru' ? 'selected' : ''; ?>>Peru</option>
                </select>
            </div>
            <div class="form-group">
                <label for="price">ราคา (บาท)</label>
                <input type="number" name="price" min="0" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="detail">รายละเอียด</label>
                <textarea name="detail" rows="4" placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับสินค้า"><?php echo htmlspecialchars($product['details']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="image">อัปโหลดรูปภาพใหม่ (ถ้าต้องการเปลี่ยน)</label>
                <input type="file" name="image" accept="image/*">
                <p>รูปภาพปัจจุบัน: <img src="gallery_products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;"></p>
            </div>
            <input type="submit" value="บันทึก">
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>