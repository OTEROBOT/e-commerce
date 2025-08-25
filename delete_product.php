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

// ดึงข้อมูลรูปภาพเพื่อลบไฟล์
$sql = "SELECT image FROM product WHERE productID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $productID);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if ($product && !empty($product['image']) && file_exists("gallery_products/" . $product['image'])) {
    unlink("gallery_products/" . $product['image']);
}

// ลบสินค้าจากฐานข้อมูล
$sql = "DELETE FROM product WHERE productID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $productID);

if ($stmt->execute()) {
    header("Location: product_list.php?msg=" . urlencode("ลบสินค้าสำเร็จ"));
} else {
    header("Location: product_list.php?error=" . urlencode("เกิดข้อผิดพลาด: " . $conn->error));
}

$stmt->close();
$conn->close();
?>