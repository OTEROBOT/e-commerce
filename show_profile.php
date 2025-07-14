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

// อัปเดตข้อมูลเมื่อกด submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile_phone = trim($_POST['mobile_phone']);
    $address = trim($_POST['address']);
    
    // ตรวจสอบความถูกต้องของข้อมูล
    $errors = [];
    if (empty($name)) $errors[] = "กรุณากรอกชื่อ";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "กรุณากรอกอีเมลที่ถูกต้อง";
    if (empty($mobile_phone)) $errors[] = "กรุณากรอกเบอร์โทร";
    
    if (empty($errors)) {
        $update_sql = "UPDATE customer SET name = ?, email = ?, mobile_phone = ?, address = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $name, $email, $mobile_phone, $address, $user_id);
        
        if ($update_stmt->execute()) {
            // อัปเดตข้อมูลใน session
            $_SESSION['user_name'] = $name;
            // รีเฟรชหน้าเพื่อแสดงข้อมูลใหม่
            header("Location: show_profile.php");
            exit();
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลโปรไฟล์สมาชิก</title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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

        .edit-form {
            text-align: left;
            margin-top: 20px;
        }

        .edit-form input, .edit-form textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Sarabun', sans-serif;
        }

        .edit-form textarea {
            resize: vertical;
            min-height: 100px;
        }

        .error-message {
            color: #f44336;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }

        .change-btn, .edit-btn, .cancel-btn, .submit-btn {
            display: inline-block;
            margin: 10px 5px 0 5px;
            padding: 10px 18px;
            text-decoration: none;
            font-size: 14px;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        .change-btn {
            background-color: #e0f2f1;
            color: #00796b;
            border: 1px solid #b2dfdb;
        }

        .change-btn:hover {
            background-color: #b2dfdb;
            color: #004d40;
        }

        .edit-btn {
            background-color: #2196f3;
            color: white;
            border: none;
        }

        .edit-btn:hover {
            background-color: #1976d2;
        }

        .cancel-btn {
            background-color: #f44336;
            color: white;
            border: none;
        }

        .cancel-btn:hover {
            background-color: #d32f2f;
        }

        .submit-btn {
            background-color: #4caf50;
            color: white;
            border: none;
        }

        .submit-btn:hover {
            background-color: #45a049;
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

        .change-btn-container {
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="profile-card">
    <img src="profile_images/<?= htmlspecialchars($profile_image) ?>" alt="รูปโปรไฟล์">
    <h2>ยินดีต้อนรับคุณ <?= htmlspecialchars($user['name']) ?></h2>
    
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['edit']) && $_GET['edit'] === 'true'): ?>
        <form method="POST" class="edit-form">
            <div class="change-btn-container">
                <a href="upload_profile.php" class="change-btn">เปลี่ยนรูปโปรไฟล์</a>
            </div>
            <p>
                <strong>ชื่อ:</strong><br>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </p>
            <p>
                <strong>Email:</strong><br>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </p>
            <p>
                <strong>เบอร์โทร:</strong><br>
                <input type="text" name="mobile_phone" value="<?= htmlspecialchars($user['mobile_phone']) ?>" required>
            </p>
            <p>
                <strong>ที่อยู่:</strong><br>
                <textarea name="address"><?= htmlspecialchars($user['address']) ?></textarea>
            </p>
            <button type="submit" class="submit-btn">บันทึก</button>
            <a href="show_profile.php" class="cancel-btn">ยกเลิก</a>
        </form>
    <?php else: ?>
        <div class="profile-info">
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($user['mobile_phone']) ?></p>
            <p><strong>ที่อยู่:</strong> <?= nl2br(htmlspecialchars($user['address'])) ?></p>
        </div>
        <a href="show_profile.php?edit=true" class="edit-btn">แก้ไขข้อมูล</a>
        <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
    </div>
    <?php endif; ?>

</div>

</body>
</html>