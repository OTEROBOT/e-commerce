<?php
session_start();
include "conn.php";

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM customer WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: show_profile.php");
        exit();
    } else {
        header("Location: login_form.php?error=รหัสผ่านไม่ถูกต้อง");
        exit();
    }
} else {
    header("Location: login_form.php?error=ไม่พบชื่อผู้ใช้");
    exit();
}
