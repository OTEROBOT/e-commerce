<?php
include "check_session.php";
include "conn.php";

// ตรวจสอบว่ามี user_id ในเซสชันหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้
$sql = "SELECT * FROM customer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ถ้าไม่พบข้อมูลผู้ใช้
if (!$user) {
    die("ไม่พบข้อมูลผู้ใช้ในระบบ.");
}

// ถ้าไม่มีรูปให้ใช้ default
$profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'default.png';

// ตัวแปรสำหรับเก็บข้อผิดพลาด
$errors = [];

// อัปเดตข้อมูลเมื่อกด submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile_phone = trim($_POST['mobile_phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // ตรวจสอบความถูกต้อง
    if ($name === '') $errors[] = "กรุณากรอกชื่อ";
    if ($username === '') $errors[] = "กรุณากรอกชื่อผู้ใช้";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "กรุณากรอกอีเมลที่ถูกต้อง";
    if ($mobile_phone === '') $errors[] = "กรุณากรอกเบอร์โทร";

    // ตรวจสอบชื่อผู้ใช้ซ้ำ
    if ($username !== $user['username']) {
        $check_username_sql = "SELECT id FROM customer WHERE username = ? AND id != ?";
        $check_stmt = $conn->prepare($check_username_sql);
        $check_stmt->bind_param("si", $username, $user_id);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();
        if ($check_res->num_rows > 0) {
            $errors[] = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
        }
    }

    // ตรวจรหัสผ่านใหม่
    $hashed_password = null;
    if ($password !== '') {
        if (strlen($password) < 6) {
            $errors[] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    // ถ้าไม่มีข้อผิดพลาด อัปเดตข้อมูล
    if (empty($errors)) {
        if ($hashed_password) {
            $update_sql = "UPDATE customer SET name = ?, username = ?, email = ?, mobile_phone = ?, address = ?, password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssssi", $name, $username, $email, $mobile_phone, $address, $hashed_password, $user_id);
        } else {
            $update_sql = "UPDATE customer SET name = ?, username = ?, email = ?, mobile_phone = ?, address = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssssi", $name, $username, $email, $mobile_phone, $address, $user_id);
        }

        if ($update_stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $_SESSION['username'] = $username;
            header("Location: show_profile.php?msg=" . urlencode("อัปเดตข้อมูลสำเร็จ"));
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
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96c93d);
            background-size: 400%;
            animation: colorShift 15s ease infinite;
            min-height: 100vh;
        }

        @keyframes colorShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .navbar {
            background-color: #4CAF50;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 18px;
            margin-right: 20px;
        }

        .navbar a:hover {
            color: #e0e0e0;
        }

        .profile-card {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 30px auto;
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
            margin-bottom: 20px;
        }

        .profile-info p {
            font-size: 16px;
            margin: 10px 0;
            color: #444;
            line-height: 1.4;
            text-align: left;
        }

        .profile-info strong {
            display: inline-block;
            width: 120px;
            color: #666;
        }

        .edit-form input,
        .edit-form textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
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

        .success-message {
            color: #4caf50;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 8px;
        }

        .change-btn,
        .edit-btn,
        .cancel-btn,
        .submit-btn {
            padding: 10px 18px;
            text-decoration: none;
            font-size: 14px;
            border-radius: 20px;
            transition: all 0.2s ease;
            cursor: pointer;
            margin: 5px;
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
    </style>
</head>
<body>
    <!-- แถบนำทาง -->
    <nav class="navbar">
        <a href="showProduct.php">รายการสินค้า</a>
        <a href="show_profile.php">โปรไฟล์</a>
        <a href="logout.php">ออกจากระบบ</a>
    </nav>

    <div class="profile-card">
        <img src="Uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="รูปโปรไฟล์">
        <h2>ยินดีต้อนรับคุณ <?php echo htmlspecialchars($user['name']); ?></h2>

        <?php if (isset($_GET['msg'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['edit']) && $_GET['edit'] === 'true'): ?>
            <!-- โหมดแก้ไข -->
            <form method="POST" class="edit-form">
                <div class="text-center mb-3">
                    <a href="upload_profile.php" class="change-btn">เปลี่ยนรูปโปรไฟล์</a>
                </div>

                <p>
                    <strong>ชื่อ:</strong><br>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </p>
                <p>
                    <strong>ชื่อผู้ใช้:</strong><br>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </p>
                <p>
                    <strong>รหัสผ่านใหม่:</strong><br>
                    <input type="password" name="password" placeholder="กรอกรหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)">
                </p>
                <p>
                    <strong>Email:</strong><br>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </p>
                <p>
                    <strong>เบอร์โทร:</strong><br>
                    <input type="text" name="mobile_phone" value="<?php echo htmlspecialchars($user['mobile_phone']); ?>" required>
                </p>
                <p>
                    <strong>ที่อยู่:</strong><br>
                    <textarea name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </p>

                <button type="submit" class="submit-btn">บันทึก</button>
                <a href="show_profile.php" class="cancel-btn">ยกเลิก</a>
            </form>
        <?php else: ?>
            <!-- โหมดแสดงผล -->
            <div class="profile-info">
                <p><strong>ชื่อ:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>ชื่อผู้ใช้:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>เบอร์โทร:</strong> <?php echo htmlspecialchars($user['mobile_phone']); ?></p>
                <p><strong>ที่อยู่:</strong> <?php echo nl2br(htmlspecialchars($user['address'])); ?></p>
            </div>

            <a href="show_profile.php?edit=true" class="edit-btn">แก้ไขข้อมูล</a>
        <?php endif; ?>

        <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>