<?php
include "conn.php"; // เชื่อมฐานข้อมูล
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แสดงข้อมูลสมาชิก</title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f4f4f4;
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 90%;
            margin: auto;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: center;
            padding: 12px;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<h2>ข้อมูลสมาชิกทั้งหมด</h2>

<table>
    <tr>
        <th>ลำดับ</th> <!-- เปลี่ยนจาก ID เป็น ลำดับ -->
        <th>Username</th>
        <th>ชื่อ - นามสกุล</th>
        <th>Email</th>
        <th>เบอร์โทรศัพท์</th>
        <th>ที่อยู่</th>
    </tr>

<?php
$sql = "SELECT * FROM customer ORDER BY id ASC";
$result = $conn->query($sql);

$no = 1; // เริ่มลำดับที่ 1

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $no++ . "</td>
                <td>" . $row['username'] . "</td>
                <td>" . $row['name'] . "</td>
                <td>" . $row['email'] . "</td>
                <td>" . $row['mobile_phone'] . "</td>
                <td>" . $row['address'] . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='6'>ไม่มีข้อมูลสมาชิก</td></tr>";
}

$conn->close();
?>
</table>


</body>
</html>
