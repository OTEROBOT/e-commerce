<?php
include "check_session.php";
// ตรวจสอบว่าเป็น Admin หรือไม่ ถ้าไม่ใช่ให้ redirect ไปหน้า login
if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productID = $_POST['productID'];
    $product_name = $_POST['product_name'];
    $origin = $_POST['origin'];
    $detail = $_POST['detail'];
    $price = $_POST['price'];
    $target_dir = "gallery_products/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    
    // ตรวจสอบและอัปโหลดไฟล์ภาพ
    $image = '';
    if (!empty($_FILES["image"]["name"])) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = basename($_FILES["image"]["name"]);
        } else {
            $image = ''; // ถ้าอัปโหลดล้มเหลว ให้บันทึกว่าง
            echo "Error uploading image: " . $_FILES["image"]["error"] . "<br>";
        }
    }

    // ใช้ prepared statement เพื่อป้องกัน SQL Injection
    $sql = "INSERT INTO product (productID, product_name, origin, details, price, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdsd", $productID, $product_name, $origin, $detail, $price, $image);
    if ($stmt->execute()) {
        header("Location: product_list.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $stmt->close();
    $conn->close();
}
?>