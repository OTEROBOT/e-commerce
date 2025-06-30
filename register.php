<?php
// 1. เชื่อมต่อฐานข้อมูล
include "conn.php";

// 2. รับค่าจากฟอร์ม
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่าน
$name = $_POST['name'];
$email = $_POST['email'];
$mobile_phone = $_POST['mobile_phone'];
$address = $_POST['address'];

// 3. เตรียมคำสั่ง SQL
$sql = "INSERT INTO customer (username, password, name, email, mobile_phone, address) VALUES (?, ?, ?, ?, ?, ?)";
$stmt_obj = $conn->prepare($sql);
$stmt_obj->bind_param("ssssss", $username, $password, $name, $email, $mobile_phone, $address);

// 4. ตรวจสอบการประมวลผลคำสั่ง SQL
if ($stmt_obj->execute()) {
    echo "✅ สมัครสมาชิกเรียบร้อยแล้ว!";
} else {
    echo "❌ เกิดข้อผิดพลาดในการสมัคร: " . $stmt_obj->error;
}

// 5. ปิดการเชื่อมต่อ
$stmt_obj->close();
$conn->close();
?>
