<?php
session_start();
include "conn.php";

// ล้างเซสชันเก่าก่อนเริ่มใหม่
session_unset();
session_destroy();
session_start();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: login_form.php?error=" . urlencode("กรุณากรอกชื่อผู้ใช้และรหัสผ่าน"));
    exit();
}

$sql = "SELECT id, username, password, is_admin FROM customer WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        $_SESSION['sess_id'] = session_id();
        $stmt->close();
        $result->close();
        header("Location: show_profile.php");
        exit();
    } else {
        $stmt->close();
        $result->close();
        header("Location: login_form.php?error=" . urlencode("รหัสผ่านไม่ถูกต้อง"));
        exit();
    }
} else {
    $stmt->close();
    $result->close();
    header("Location: login_form.php?error=" . urlencode("ไม่พบชื่อผู้ใช้"));
    exit();
}
?>