<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "conn.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['sess_id']) || $_SESSION['sess_id'] !== session_id()) {
    header("Location: login_form.php?error=" . urlencode("กรุณาเข้าสู่ระบบก่อน"));
    exit();
}

if (!isset($_SESSION['is_admin'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT is_admin FROM customer WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['is_admin'] = $row['is_admin'];
    } else {
        session_unset();
        session_destroy();
        header("Location: login_form.php?error=" . urlencode("เซสชันไม่ถูกต้อง"));
        exit();
    }
    $stmt->close();
    $result->close();
}
?>