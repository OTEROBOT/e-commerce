<?php
session_start();
include "check_session.php";
include "conn.php";

// ตรวจสอบสถานะแอดมิน
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: show_profile.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

// ดึงข้อมูลแอดมินจากฐานข้อมูล
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, name, mobile_phone, address, profile_image FROM customer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();
$result->close();

// จัดการอัปเดตข้อมูลและการอัปโหลดภาพ
$success = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile_phone = trim($_POST['mobile_phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    if (empty($username)) $errors[] = "กรุณากรอกชื่อผู้ใช้";
    if (empty($name)) $errors[] = "กรุณากรอกชื่อ";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "กรุณากรอกอีเมลที่ถูกต้อง";
    if (empty($mobile_phone)) $errors[] = "กรุณากรอกเบอร์โทร";

    if (empty($errors)) {
        $check_sql = "SELECT id FROM customer WHERE username = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $username, $user_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
        }
        $check_stmt->close();
    }

    // จัดการการอัปโหลดภาพ
    $profile_image = $admin['profile_image'] ?? null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid() . '_' . basename($_FILES['profile_image']['name']);
        $target_file = $upload_dir . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($image_file_type, $allowed_types)) {
            $errors[] = "เฉพาะไฟล์ JPG, JPEG, PNG, GIF เท่านั้นที่อนุญาต";
        } elseif ($_FILES['profile_image']['size'] > 5000000) { // 5MB
            $errors[] = "ไฟล์ภาพต้องไม่เกิน 5MB";
        } else {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $profile_image = $file_name;
            } else {
                $errors[] = "เกิดข้อผิดพลาดในการอัปโหลดภาพ";
            }
        }
    }

    if (empty($errors)) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE customer SET username = ?, name = ?, email = ?, mobile_phone = ?, address = ?, password = ?, profile_image = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssssssi", $username, $name, $email, $mobile_phone, $address, $hashed_password, $profile_image, $user_id);
        } else {
            $update_sql = "UPDATE customer SET username = ?, name = ?, email = ?, mobile_phone = ?, address = ?, profile_image = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssssi", $username, $name, $email, $mobile_phone, $address, $profile_image, $user_id);
        }

        if ($update_stmt->execute()) {
            $success = "อัปเดตโปรไฟล์สำเร็จ";
            $admin = ['username' => $username, 'name' => $name, 'email' => $email, 'mobile_phone' => $mobile_phone, 'address' => $address, 'profile_image' => $profile_image];
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัปเดต: " . $conn->error;
        }
        $update_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์แอดมิน</title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #4CAF50;
            padding: 22px 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 30px;
            font-weight: 500;
            font-size: 18px;
        }
        .navbar a:hover {
            color: #e0e0e0;
        }
        .profile-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-item {
            margin-bottom: 15px;
        }
        .profile-item label {
            font-weight: bold;
            margin-right: 10px;
        }
        .profile-item span {
            color: #333;
        }
        .profile-image {
            margin-bottom: 15px;
        }
        .profile-image img {
            max-width: 150px;
            border-radius: 50%;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .error {
            background-color: #ffebee;
            color: #d32f2f;
        }
        .edit-btn {
            background-color: #2196f3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .edit-btn:hover {
            background-color: #1976d2;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .modal-content input, .modal-content textarea, .modal-content input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .modal-content label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .modal-content button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .modal-content button:hover {
            background-color: #45a049;
        }
        .cancel-btn {
            background-color: #f44336;
        }
        .cancel-btn:hover {
            background-color: #d32f2f;
        }
        @media screen and (max-width: 600px) {
            .navbar {
                padding: 15px 25px;
            }
            .navbar a {
                margin-right: 15px;
                font-size: 14px;
            }
            .edit-btn {
                display: block;
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
    <script>
        function openEditModal() {
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('edit_username').value = '<?= htmlspecialchars($admin['username']) ?>';
            document.getElementById('edit_name').value = '<?= htmlspecialchars($admin['name']) ?>';
            document.getElementById('edit_email').value = '<?= htmlspecialchars($admin['email']) ?>';
            document.getElementById('edit_mobile_phone').value = '<?= htmlspecialchars($admin['mobile_phone']) ?>';
            document.getElementById('edit_address').value = '<?= htmlspecialchars($admin['address']) ?>';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</head>
<body>
    <div class="navbar">
        <a href="admin_profile.php">โปรไฟล์</a>
        <a href="product_list.php">ลิสสินค้า</a> <!-- เพิ่มลิงก์ไปหน้า ลิสสินค้า -->
        <a href="showmember.php">จัดการสมาชิก</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div class="profile-container">
        <?php if (isset($_GET['error'])): ?>
            <div class="error"><?= htmlspecialchars(urldecode($_GET['error'])) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php unset($success); ?>
        <?php endif; ?>

        <h2>โปรไฟล์แอดมิน</h2>
        <h3>ยินดีต้อนรับคุณ <?= htmlspecialchars($admin['name']) ?></h3>
        <?php if ($admin): ?>
            <?php if ($admin['profile_image']): ?>
                <div class="profile-image">
                    <img src="uploads/<?= htmlspecialchars($admin['profile_image']) ?>" alt="Profile Image">
                </div>
            <?php endif; ?>
            <div class="profile-item">
                <label>ชื่อผู้ใช้:</label>
                <span><?= htmlspecialchars($admin['username']) ?></span>
            </div>
            <div class="profile-item">
                <label>ชื่อ - นามสกุล:</label>
                <span><?= htmlspecialchars($admin['name']) ?></span>
            </div>
            <div class="profile-item">
                <label>อีเมล:</label>
                <span><?= htmlspecialchars($admin['email']) ?></span>
            </div>
            <div class="profile-item">
                <label>เบอร์โทรศัพท์:</label>
                <span><?= htmlspecialchars($admin['mobile_phone']) ?></span>
            </div>
            <div class="profile-item">
                <label>ที่อยู่:</label>
                <span><?= nl2br(htmlspecialchars($admin['address'])) ?></span>
            </div>
            <div style="margin-top: 20px;">
                <a href="#" class="edit-btn" onclick="openEditModal()">แก้ไขข้อมูล</a>
            </div>
        <?php else: ?>
            <p>ไม่พบข้อมูลโปรไฟล์</p>
        <?php endif; ?>

        <div id="editModal" class="modal">
            <div class="modal-content">
                <h3>แก้ไขข้อมูลโปรไฟล์</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" value="<?= $user_id ?>">
                    <label>ชื่อผู้ใช้ (แก้ไขชื่อผู้ใช้ของคุณ)</label>
                    <input type="text" name="username" id="edit_username" placeholder="ชื่อผู้ใช้" required>
                    <label>ชื่อ - นามสกุล (แก้ไขชื่อและนามสกุลของคุณ)</label>
                    <input type="text" name="name" id="edit_name" placeholder="ชื่อ - นามสกุล" required>
                    <label>อีเมล (แก้ไขอีเมลของคุณ)</label>
                    <input type="email" name="email" id="edit_email" placeholder="อีเมล" required>
                    <label>เบอร์โทรศัพท์ (แก้ไขเบอร์โทรของคุณ)</label>
                    <input type="text" name="mobile_phone" id="edit_mobile_phone" placeholder="เบอร์โทรศัพท์" required>
                    <label>ที่อยู่ (แก้ไขที่อยู่ของคุณ)</label>
                    <textarea name="address" id="edit_address" placeholder="ที่อยู่"></textarea>
                    <label>รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยนรหัสผ่าน)</label>
                    <input type="password" name="password" placeholder="รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)">
                    <label>ภาพโปรไฟล์ (อัปโหลดภาพใหม่)</label>
                    <input type="file" name="profile_image" accept="image/*">
                    <button type="submit" name="update_profile">บันทึก</button>
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">ยกเลิก</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>