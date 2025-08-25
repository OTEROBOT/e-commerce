<?php
include "conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productID = $_POST['productID'];
    $product_name = $_POST['product_name'];
    $origin = !empty($_POST['origin']) ? $_POST['origin'] : null;
    $price = $_POST['price'];
    $category = $_POST['category'];
    $details = !empty($_POST['details']) ? $_POST['details'] : null;
    $image = '';

    // ดึงรูปภาพเดิม
    $sql = "SELECT image FROM product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $image = $row['image'];
    $stmt->close();

    // จัดการการอัปโหลดรูปภาพใหม่
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "gallery_products/";
        $image = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ'); window.location='product_list.php';</script>";
            exit;
        }
    }

    // อัปเดตข้อมูล
    $sql = "UPDATE product SET product_name = ?, origin = ?, price = ?, image = ?, category = ?, details = ? WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<script>alert('Prepare failed: " . $conn->error . "'); window.location='product_list.php';</script>";
        exit;
    }
    $stmt->bind_param("ssdsdss", $product_name, $origin, $price, $image, $category, $details, $productID);

    if ($stmt->execute()) {
        echo "<script>alert('แก้ไขสินค้าสำเร็จ'); window.location='product_list.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด: " . $stmt->error . "'); window.location='product_list.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>