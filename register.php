<?php
include "conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile_phone = $_POST['mobile_phone'];
    $address = $_POST['address'];

    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    // ตรวจสอบซ้ำ
    $check_stmt = $conn->prepare("SELECT id FROM customer WHERE email = ? OR username = ?");
    $check_stmt->bind_param("ss", $email, $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // redirect กลับไปพร้อมแจ้งเตือน
        header("Location: register_form.php?msg=ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว");
        exit;
    }

    $check_stmt->close();

    // เพิ่มข้อมูลใหม่
    $stmt = $conn->prepare("INSERT INTO customer (username, password, name, email, mobile_phone, address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $password, $name, $email, $mobile_phone, $address);

    if ($stmt->execute()) {
        header("Location: register_form.php?msg=✅ สมัครสมาชิกเรียบร้อยแล้ว!");
    } else {
        header("Location: register_form.php?msg=❌ เกิดข้อผิดพลาด: " . urlencode($stmt->error));
    }

    $stmt->close();
}

$conn->close();
?>
