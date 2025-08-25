<?php
include "check_session.php";
include "conn.php";

if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productID = $_POST['productID'];
    $product_name = $_POST['product_name'];
    $origin = $_POST['origin'];
    $price = $_POST['price'];
    $detail = $_POST['detail'];

    // ตรวจสอบว่า productID ซ้ำหรือไม่
    $sql = "SELECT productID FROM product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        header("Location: addProduct_form.php?error=" . urlencode("รหัสสินค้านี้มีอยู่แล้ว"));
        exit();
    }

    // จัดการการอัปโหลดรูปภาพ
    $image = $_FILES['image']['name'];
    $target_dir = "gallery_products/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $sql = "INSERT INTO product (productID, product_name, origin, price, details, image) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdss", $productID, $product_name, $origin, $price, $detail, $image);

        if ($stmt->execute()) {
            header("Location: product_list.php?msg=" . urlencode("เพิ่มสินค้าสำเร็จ"));
            exit();
        } else {
            header("Location: addProduct_form.php?error=" . urlencode("เกิดข้อผิดพลาด: " . $conn->error));
            exit();
        }
        $stmt->close();
    } else {
        header("Location: addProduct_form.php?error=" . urlencode("เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ"));
        exit();
    }
}

$conn->close();
?>