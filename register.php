<?php
include "conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password_plain = trim($_POST['password'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile_phone = trim($_POST['mobile_phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $is_admin = isset($_POST['is_admin']) && $_POST['is_admin'] == '1' ? 1 : 0; // เพิ่มตัวเลือก is_admin

    // ตรวจสอบข้อมูลขั้นพื้นฐาน
    $errors = [];
    if (empty($username)) $errors[] = "กรุณากรอกชื่อผู้ใช้";
    if (empty($password_plain) || strlen($password_plain) < 6) $errors[] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    if (empty($name)) $errors[] = "กรุณากรอกชื่อ";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "กรุณากรอกอีเมลที่ถูกต้อง";
    if (empty($mobile_phone)) $errors[] = "กรุณากรอกเบอร์โทร";

    if (empty($errors)) {
        $password = password_hash($password_plain, PASSWORD_DEFAULT);

        // ตรวจสอบซ้ำ
        $check_stmt = $conn->prepare("SELECT id FROM customer WHERE email = ? OR username = ?");
        if ($check_stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $check_stmt->bind_param("ss", $email, $username);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            header("Location: register_form.php?msg=" . urlencode("ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว"));
            exit;
        }
        $check_stmt->close();

        // เพิ่มข้อมูลใหม่
        $stmt = $conn->prepare("INSERT INTO customer (username, password, name, email, mobile_phone, address, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssssssi", $username, $password, $name, $email, $mobile_phone, $address, $is_admin);

        if ($stmt->execute()) {
            header("Location: register_form.php?msg=" . urlencode("✅ สมัครสมาชิกเรียบร้อยแล้ว!"));
        } else {
            header("Location: register_form.php?msg=" . urlencode("❌ เกิดข้อผิดพลาด: " . $stmt->error));
        }
        $stmt->close();
    } else {
        // ส่งข้อผิดพลาดกลับไปยังฟอร์ม
        $error_msg = urlencode(implode(", ", $errors));
        header("Location: register_form.php?msg=" . $error_msg);
        exit;
    }
}

$conn->close();
?>