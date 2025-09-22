<?php
// adminDeleteOrder.php
include "check_session.php";
include "conn.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: show_profile.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id > 0) {
    $sql = "DELETE FROM orders WHERE order_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        header("Location: adminOrders.php?success=" . urlencode("ลบคำสั่งซื้อเรียบร้อยแล้ว"));
        exit();
    } else {
        header("Location: adminOrders.php?error=" . urlencode("ไม่สามารถลบคำสั่งซื้อได้"));
        exit();
    }
} else {
    header("Location: adminOrders.php?error=" . urlencode("ไม่พบคำสั่งซื้อ"));
    exit();
}
