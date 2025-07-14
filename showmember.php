<?php
include "check_session.php";
include "conn.php";

// ตรวจสอบสถานะแอดมิน (ถ้าต้องการ redundancy)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: show_profile.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

// เพิ่มสมาชิกใหม่
$success = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile_phone = trim($_POST['mobile_phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);
    $is_admin = isset($_POST['is_admin']) && $_POST['is_admin'] == '1' ? 1 : 0;
    
    if (empty($username)) $errors[] = "กรุณากรอกชื่อผู้ใช้";
    if (empty($name)) $errors[] = "กรุณากรอกชื่อ";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "กรุณากรอกอีเมลที่ถูกต้อง";
    if (empty($mobile_phone)) $errors[] = "กรุณากรอกเบอร์โทร";
    if (empty($password) || strlen($password) < 6) $errors[] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    
    $profile_image = null;
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
        $check_sql = "SELECT id FROM customer WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
        }
        $check_stmt->close();
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_sql = "INSERT INTO customer (username, name, email, mobile_phone, address, password, is_admin, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssssssis", $username, $name, $email, $mobile_phone, $address, $hashed_password, $is_admin, $profile_image);
        if ($insert_stmt->execute()) {
            $success = "เพิ่มสมาชิกสำเร็จ";
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการเพิ่มสมาชิก: " . $conn->error;
        }
        $insert_stmt->close();
    }
}

// แก้ไขสมาชิก
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_member'])) {
    $edit_id = $_POST['edit_id'];
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile_phone = trim($_POST['mobile_phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);
    
    $errors = [];
    if (empty($username)) $errors[] = "กรุณากรอกชื่อผู้ใช้";
    if (empty($name)) $errors[] = "กรุณากรอกชื่อ";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "กรุณากรอกอีเมลที่ถูกต้อง";
    if (empty($mobile_phone)) $errors[] = "กรุณากรอกเบอร์โทร";
    
    $profile_image = null;
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
        $check_sql = "SELECT id FROM customer WHERE username = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $username, $edit_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
        }
        $check_stmt->close();
    }
    
    if (empty($errors)) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            if ($profile_image) {
                $update_sql = "UPDATE customer SET username = ?, name = ?, email = ?, mobile_phone = ?, address = ?, password = ?, profile_image = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sssssssi", $username, $name, $email, $mobile_phone, $address, $hashed_password, $profile_image, $edit_id);
            } else {
                $update_sql = "UPDATE customer SET username = ?, name = ?, email = ?, mobile_phone = ?, address = ?, password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ssssssi", $username, $name, $email, $mobile_phone, $address, $hashed_password, $edit_id);
            }
        } else {
            if ($profile_image) {
                $update_sql = "UPDATE customer SET username = ?, name = ?, email = ?, mobile_phone = ?, address = ?, profile_image = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ssssssi", $username, $name, $email, $mobile_phone, $address, $profile_image, $edit_id);
            } else {
                $update_sql = "UPDATE customer SET username = ?, name = ?, email = ?, mobile_phone = ?, address = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sssssi", $username, $name, $email, $mobile_phone, $address, $edit_id);
            }
        }
        
        if ($update_stmt->execute()) {
            $success = "แก้ไขข้อมูลสมาชิกสำเร็จ";
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล: " . $conn->error;
        }
        $update_stmt->close();
    }
}

// ลบสมาชิก
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $check_admin_sql = "SELECT is_admin FROM customer WHERE id = ?";
    $check_admin_stmt = $conn->prepare($check_admin_sql);
    $check_admin_stmt->bind_param("i", $delete_id);
    $check_admin_stmt->execute();
    $admin_check = $check_admin_stmt->get_result()->fetch_assoc();
    $check_admin_stmt->close();
    
    if ($admin_check && $admin_check['is_admin'] == 1) {
        $errors[] = "ไม่สามารถลบผู้ใช้ที่เป็นแอดมินได้";
    } else {
        $delete_sql = "DELETE FROM customer WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $delete_id);
        if ($delete_stmt->execute()) {
            $success = "ลบสมาชิกสำเร็จ";
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการลบสมาชิก: " . $conn->error;
        }
        $delete_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสมาชิก</title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
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
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
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
            color: #c62828;
        }
        .add-form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .add-form h3 {
            margin-top: 0;
            color: #4CAF50;
        }
        .add-form input, .add-form textarea, .add-form input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .profile-upload {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-upload img {
            max-width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .profile-upload .upload-btn {
            display: inline-block;
            width: 50px;
            height: 50px;
            background-color: #e0f2f1;
            border: 2px solid #00796b;
            border-radius: 50%;
            color: #00796b;
            font-size: 20px;
            line-height: 46px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .profile-upload .upload-btn:hover {
            background-color: #b2dfdb;
        }
        .add-form label {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }
        .add-form label input[type="checkbox"] {
            margin-right: 10px;
            margin-left: 0;
        }
        .add-form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .add-form button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 25px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #e0f2e9;
            color: #2e7d32;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .profile-image img {
            max-width: 100px;
            height: 100px;
            border-radius: 50%;
            vertical-align: middle;
        }
        .action-btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 15px;
            font-size: 13px;
            display: inline-block;
        }
        td:last-child {
            text-align: left;
        }
        .edit-btn {
            background-color: #2196f3;
            color: white;
        }
        .edit-btn:hover {
            background-color: #1976d2;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        .delete-btn:hover {
            background-color: #d32f2f;
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
        .modal-content .profile-upload {
            margin-bottom: 20px;
        }
        .modal-content .profile-upload img {
            max-width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .modal-content .profile-upload .upload-btn {
            display: inline-block;
            width: 50px;
            height: 50px;
            background-color: #e0f2f1;
            border: 2px solid #00796b;
            border-radius: 50%;
            color: #00796b;
            font-size: 20px;
            line-height: 46px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .modal-content .profile-upload .upload-btn:hover {
            background-color: #b2dfdb;
        }
        .modal-content input, .modal-content textarea, .modal-content input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none; /* ซ่อน input file เดิม */
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
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 10px;
            }
            table, .add-form {
                width: 100%;
            }
            th, td {
                padding: 10px;
                font-size: 14px;
            }
            .navbar {
                padding: 15px 25px;
            }
            .navbar a {
                margin-right: 15px;
                font-size: 14px;
            }
            .action-btn {
                margin-right: 5px;
            }
            .add-form label {
                flex-direction: column;
                align-items: flex-start;
            }
            .add-form label input[type="checkbox"] {
                margin-right: 0;
            }
            td:last-child {
                text-align: left;
            }
            .profile-image img {
                max-width: 60px;
                height: 60px;
            }
            .profile-upload img {
                max-width: 60px;
                height: 60px;
            }
            .profile-upload .upload-btn {
                width: 40px;
                height: 40px;
                line-height: 36px;
                font-size: 16px;
            }
            .modal-content .profile-upload img {
                max-width: 60px;
                height: 60px;
            }
            .modal-content .profile-upload .upload-btn {
                width: 40px;
                height: 40px;
                line-height: 36px;
                font-size: 16px;
            }
        }
    </style>
    <script>
        function openEditModal(id, username, name, email, mobile_phone, address, profile_image) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_mobile_phone').value = mobile_phone;
            document.getElementById('edit_address').value = address;
            const preview = document.getElementById('profilePreview');
            if (profile_image) {
                preview.src = 'uploads/' + profile_image;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
            document.getElementById('editModal').style.display = 'flex';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        function confirmDelete(id) {
            if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสมาชิกนี้?')) {
                window.location.href = 'showmember.php?delete_id=' + id;
            }
        }
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</head>
<body>
    <div class="navbar">
        <a href="admin_profile.php">โปรไฟล์</a>
        <a href="showmember.php" style="color: #e0e0e0;">จัดการสมาชิก</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php unset($success); ?>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php $errors = []; ?>
        <?php endif; ?>

        <div class="add-form">
            <h3>เพิ่มสมาชิกใหม่</h3>
            <div class="profile-upload">
                <img id="addProfilePreview" src="" alt="Preview" style="display: none;">
                <label class="upload-btn" for="add_profile_image">+</label>
                <input type="file" id="add_profile_image" name="profile_image" accept="image/*" onchange="previewImage(this, 'addProfilePreview')">
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
                <input type="text" name="name" placeholder="ชื่อ - นามสกุล" required>
                <input type="email" name="email" placeholder="อีเมล" required>
                <input type="text" name="mobile_phone" placeholder="เบอร์โทรศัพท์" required>
                <textarea name="address" placeholder="ที่อยู่"></textarea>
                <input type="password" name="password" placeholder="รหัสผ่าน" required>
                <label>เป็นแอดมิน<input type="checkbox" name="is_admin" value="1"></label>
                <button type="submit" name="add_member">เพิ่มสมาชิก</button>
            </form>
        </div>

        <table>
            <tr>
                <th>ลำดับ</th>
                <th>รูปโปรไฟล์</th>
                <th>ชื่อผู้ใช้</th>
                <th>ชื่อ - นามสกุล</th>
                <th>อีเมล</th>
                <th>เบอร์โทรศัพท์</th>
                <th>ที่อยู่</th>
                <th>การจัดการ</th>
            </tr>
            <?php
            $sql = "SELECT * FROM customer ORDER BY id ASC";
            $result = $conn->query($sql);
            if ($result === false) {
                echo "<tr><td colspan='8'>เกิดข้อผิดพลาดในการดึงข้อมูล: " . $conn->error . "</td></tr>";
            } else {
                $no = 1;
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $no++ . "</td>
                                <td class='profile-image'>";
                        if ($row['profile_image']) {
                            echo "<img src='uploads/" . htmlspecialchars($row['profile_image']) . "' alt='Profile Image'>";
                        }
                        echo "</td>
                                <td>" . htmlspecialchars($row['username']) . "</td>
                                <td>" . htmlspecialchars($row['name']) . "</td>
                                <td>" . htmlspecialchars($row['email']) . "</td>
                                <td>" . htmlspecialchars($row['mobile_phone']) . "</td>
                                <td>" . nl2br(htmlspecialchars($row['address'])) . "</td>
                                <td>
                                    <a href='#' class='action-btn edit-btn' onclick='openEditModal(" . $row['id'] . ", \"" . htmlspecialchars(addslashes($row['username'])) . "\", \"" . htmlspecialchars(addslashes($row['name'])) . "\", \"" . htmlspecialchars(addslashes($row['email'])) . "\", \"" . htmlspecialchars(addslashes($row['mobile_phone'])) . "\", \"" . htmlspecialchars(addslashes($row['address'])) . "\", \"" . htmlspecialchars($row['profile_image']) . "\")'>แก้ไข</a>
                                    <a href='#' class='action-btn delete-btn' onclick='confirmDelete(" . $row['id'] . ")'>ลบ</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>ไม่มีข้อมูลสมาชิก</td></tr>";
                }
            }
            $conn->close();
            ?>
        </table>

        <div id="editModal" class="modal">
            <div class="modal-content">
                <div class="profile-upload">
                    <img id="profilePreview" src="" alt="Profile Preview">
                    <label class="upload-btn" for="edit_profile_image">+</label>
                    <input type="file" id="edit_profile_image" name="profile_image" accept="image/*" onchange="previewImage(this, 'profilePreview')">
                </div>
                <h3>แก้ไขข้อมูลสมาชิก</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <label>ชื่อผู้ใช้ (แก้ไขชื่อผู้ใช้):</label>
                    <input type="text" name="username" id="edit_username" placeholder="ชื่อผู้ใช้" required>
                    <label>ชื่อ - นามสกุล (แก้ไขชื่อและนามสกุล):</label>
                    <input type="text" name="name" id="edit_name" placeholder="ชื่อ - นามสกุล" required>
                    <label>อีเมล (แก้ไขอีเมล):</label>
                    <input type="email" name="email" id="edit_email" placeholder="อีเมล" required>
                    <label>เบอร์โทรศัพท์ (แก้ไขเบอร์โทร):</label>
                    <input type="text" name="mobile_phone" id="edit_mobile_phone" placeholder="เบอร์โทรศัพท์" required>
                    <label>ที่อยู่ (แก้ไขที่อยู่):</label>
                    <textarea name="address" id="edit_address" placeholder="ที่อยู่"></textarea>
                    <label>รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยนรหัสผ่าน):</label>
                    <input type="password" name="password" placeholder="รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)">
                    <button type="submit" name="edit_member">บันทึก</button>
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">ยกเลิก</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>