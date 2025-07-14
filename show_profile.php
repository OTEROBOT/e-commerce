<?php
include "check_session.php";
include "conn.php";

// ดึงข้อมูลผู้ใช้
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM customer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ตรวจสอบชื่อรูป ถ้าไม่มีให้ใช้ default.png
$profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'default.png';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลสมาชิก</title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .profile-card {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .profile-card img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
            margin-bottom: 15px;
        }

        h2 {
            color: #333333;
            margin-bottom: 10px;
        }

        .profile-info {
            text-align: left;
            margin-top: 10px;
        }

        .profile-info p {
            font-size: 16px;
            margin: 10px 0;
            color: #444;
            line-height: 1.4;
        }

        .profile-info strong {
            display: inline-block;
            width: 120px;
            color: #666;
        }

        .upload-link {
            font-size: 14px;
            margin-top: 5px;
            display: inline-block;
        }

        .logout-btn {
            display: block;
            width: 100%;
            margin-top: 25px;
            padding: 12px;
            background-color: #f44336;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .logout-btn:hover {
            background-color: #d32f2f;
        }

        @media screen and (max-width: 500px) {
            .profile-card {
                padding: 25px;
                border-radius: 15px;
            }
        }

        .change-btn {
    display: inline-block;
    margin-top: 10px;
    padding: 10px 18px;
    background-color: #e0f2f1;
    color: #00796b;
    text-decoration: none;
    font-size: 14px;
    border-radius: 20px;
    border: 1px solid #b2dfdb;
    transition: all 0.2s ease;
}

.change-btn:hover {
    background-color: #b2dfdb;
    color: #004d40;
}

    </style>
</head>
<body>

<div class="profile-card">
    <img src="profile_images/<?= htmlspecialchars($profile_image) ?>" alt="รูปโปรไฟล์">
    <h2>สวัสดีคุณ <?= htmlspecialchars($user['name']) ?></h2>
    
    <a href="upload_profile.php" class="change-btn">เปลี่ยนรูปโปรไฟล์</a>
    
    <div class="profile-info">
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($user['mobile_phone']) ?></p>
        <p><strong>ที่อยู่:</strong> <?= nl2br(htmlspecialchars($user['address'])) ?></p>
    </div>

    <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
</div>

</body>
</html>
