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
  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background-color: #e8f0fe;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .profile-card {
      background-color: #ffffff;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 500px;
    }
    h2 {
      margin-bottom: 20px;
      text-align: center;
    }
    .profile-info p {
      margin: 10px 0;
      font-size: 16px;
    }
    .logout {
      display: block;
      margin-top: 25px;
      text-align: center;
      text-decoration: none;
      color: #ffffff;
      background-color: #f44336;
      padding: 10px;
      border-radius: 8px;
    }
    .logout:hover {
      background-color: #d32f2f;
    }
  </style>
</head>
<body>

<div class="profile-card">
  <h2>สวัสดีคุณ <?= htmlspecialchars($user['name']) ?></h2>
  <div class="profile-info">
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($user['mobile_phone']) ?></p>
    <p><strong>ที่อยู่:</strong> <?= nl2br(htmlspecialchars($user['address'])) ?></p>
  </div>
  <a href="logout.php" class="logout">ออกจากระบบ</a>
</div>

</body>
</html>
