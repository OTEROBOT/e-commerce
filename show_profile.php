<?php
include "check_session.php";
include "conn.php";

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM customer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลสมาชิก</title>
</head>
<body>
    <h2>ยินดีต้อนรับคุณ <?php echo htmlspecialchars($user['name']); ?></h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>เบอร์โทร:</strong> <?php echo htmlspecialchars($user['mobile_phone']); ?></p>
    <p><strong>ที่อยู่:</strong> <?php echo nl2br(htmlspecialchars($user['address'])); ?></p>

    <a href="logout.php">ออกจากระบบ</a>
</body>
</html>
