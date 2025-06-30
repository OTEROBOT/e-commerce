<?php
// เชื่อมต่อฐานข้อมูล โดยใช้ไฟล์ conn.php ที่เตรียมไว้
include "conn.php";

// ตรวจสอบว่ามีการส่งข้อมูลมาทาง POST เท่านั้น
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // รับค่าจากฟอร์มที่ถูกส่งมา
    $username = $_POST['username'];
    $password_plain = $_POST['password']; // รหัสผ่านที่ยังไม่ได้เข้ารหัส
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile_phone = $_POST['mobile_phone'];
    $address = $_POST['address'];

    // เข้ารหัสรหัสผ่านด้วย password_hash เพื่อความปลอดภัย
    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    // ตรวจสอบว่ามี email หรือ username ซ้ำในระบบหรือไม่
    $check_stmt = $conn->prepare("SELECT id FROM customer WHERE email = ? OR username = ?");
    $check_stmt->bind_param("ss", $email, $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // ถ้ามี email หรือ username ซ้ำ
        echo "❌ อีเมลหรือชื่อผู้ใช้นี้ถูกใช้ไปแล้ว กรุณาใช้อันอื่น";
    } else {
        // ถ้าไม่มีซ้ำ → เตรียม SQL สำหรับเพิ่มข้อมูลลงฐานข้อมูล
        $sql = "INSERT INTO customer (username, password, name, email, mobile_phone, address)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $username, $password, $name, $email, $mobile_phone, $address);

        // ดำเนินการ execute SQL
        if ($stmt->execute()) {
            echo "✅ สมัครสมาชิกเรียบร้อยแล้ว!";
        } else {
            echo "❌ เกิดข้อผิดพลาดในการสมัคร: " . $stmt->error;
        }

        // ปิด statement สำหรับ insert
        $stmt->close();
    }

    // ปิด statement สำหรับตรวจสอบ
    $check_stmt->close();
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
