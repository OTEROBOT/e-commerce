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

    // จัดการการอัปโหลดรูปภาพ (ถ้ามี)
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target_dir = "gallery_products/";
        $target_file = $target_dir . basename($image);
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            header("Location: product_list.php?error=" . urlencode("เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ"));
            exit();
        }
    }

    // อัปเดตข้อมูลในฐานข้อมูล
    if ($image) {
        $sql = "UPDATE product SET product_name = ?, origin = ?, price = ?, details = ?, image = ? WHERE productID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsss", $product_name, $origin, $price, $detail, $image, $productID);
    } else {
        $sql = "UPDATE product SET product_name = ?, origin = ?, price = ?, details = ? WHERE productID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdss", $product_name, $origin, $price, $detail, $productID);
    }

    if ($stmt->execute()) {
        header("Location: product_list.php?msg=" . urlencode("แก้ไขสินค้าสำเร็จ"));
    } else {
        header("Location: product_list.php?error=" . urlencode("เกิดข้อผิดพลาด: " . $conn->error));
    }

    $stmt->close();
}

$conn->close();
?>