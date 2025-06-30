<?php
$base_url = 'http://localhost/ote'; // ใช้สำหรับการ redirect หรือลิงก์อื่นๆ
$host = "localhost";
$user = "root";
$pw = "";
$dbname = "ote";

// สร้างการเชื่อมต่อ
$conn = new mysqli($host, $user, $pw, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
