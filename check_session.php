<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "conn.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['sess_id']) || $_SESSION['sess_id'] !== session_id()) {
    header("Location: {$base_url}/login_form.php?error=" . urlencode("กรุณาเข้าสู่ระบบก่อน"));
    exit();
}

try {
    if (!isset($_SESSION['is_admin'])) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT is_admin FROM customer WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $_SESSION['is_admin'] = $row['is_admin'];
        } else {
            throw new Exception("ไม่พบผู้ใช้ในระบบ");
        }
        $stmt->close();
    }

    if (!$_SESSION['is_admin']) {
        header("Location: {$base_url}/login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
        exit();
    }
} catch (Exception $e) {
    session_unset();
    session_destroy();
    header("Location: {$base_url}/login_form.php?error=" . urlencode("เซสชันไม่ถูกต้อง: " . $e->getMessage()));
    exit();
}
?>