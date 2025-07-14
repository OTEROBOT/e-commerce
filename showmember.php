<?php
include "check_session.php";
include "conn.php";

// ตรวจสอบว่าเป็นแอดมินหรือไม่
$user_id = $_SESSION['user_id'];
$check_admin_sql = "SELECT is_admin FROM customer WHERE id = ?";
$check_admin_stmt = $conn->prepare($check_admin_sql);
$check_admin_stmt->bind_param("i", $user_id);
$check_admin_stmt->execute();
$admin_result = $check_admin_stmt->get_result();
$admin_row = $admin_result->fetch_assoc();

if (!$admin_row || $admin_row['is_admin'] != 1) {
    header("Location: show_profile.php?error=คุณไม่มีสิทธิ์เข้าถึงหน้านี้");
    exit();
}

// เพิ่มสมาชิกใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
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
    if (empty($password) || strlen($password) < 6) $errors[] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    
    // ตรวจสอบชื่อผู้ใช้ซ้ำ
    $check_sql = "SELECT id FROM customer WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $errors[] = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_sql = "INSERT INTO customer (username, name, email, mobile_phone, address, password, is_admin) VALUES (?, ?, ?, ?, ?, ?, 0)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssssss", $username, $name, $email, $mobile_phone, $address, $hashed_password);
        if ($insert_stmt->execute()) {
            $success = "เพิ่มสมาชิกสำเร็จ";
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการเพิ่มสมาชิก";
        }
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
    
    // ตรวจสอบชื่อผู้ใช้ซ้ำ
    $check_sql = "SELECT id FROM customer WHERE username = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $username, $edit_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $errors[] = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
    }
    
    if (empty($errors)) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE customer SET username = ?, name = ?, email = ?, mobile_phone = ?, address = ?, password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssssi", $username, $name, $email, $mobile_phone, $address, $hashed_password, $edit_id);
        } else {
            $update_sql = "UPDATE customer SET username = ?, name = ?, email = ?, mobile_phone = ?, address = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssssi", $username, $name, $email, $mobile_phone, $address, $edit_id);
        }
        
        if ($update_stmt->execute()) {
            $success = "แก้ไขข้อมูลสมาชิกสำเร็จ";
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล";
        }
    }
}

// ลบสมาชิก
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // ตรวจสอบว่าไม่ใช่แอดมิน
    $check_admin_sql = "SELECT is_admin FROM customer WHERE id = ?";
    $check_admin_stmt = $conn->prepare($check_admin_sql);
    $check_admin_stmt->bind_param("i", $delete_id);
    $check_admin_stmt->execute();
    $admin_check = $check_admin_stmt->get_result()->fetch_assoc();
    
    if ($admin_check && $admin_check['is_admin'] == 1) {
        $errors[] = "ไม่สามารถลบผู้ใช้ที่เป็นแอดมินได้";
    } else {
        $delete_sql = "DELETE FROM customer WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $delete_id);
        if ($delete_stmt->execute()) {
            $success = "ลบสมาชิกสำเร็จ";
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการลบสมาชิก";
        }
    }
}
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
            margin-bottom: 20px;
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
        .add-form {
            width: 90%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .add-form input, .add-form textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Sarabun', sans-serif;
        }
        .add-form textarea {
            resize: vertical;
            min-height: 80px;
        }
        .add-form button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        .add-form button:hover {
            background-color: #45a049;
        }
        .action-btn {
            padding: 8px 12px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            font-size: 13px;
            margin: 0 3px;
        }
        .edit-btn {
            background-color: #2196f3;
        }
        .edit-btn:hover {
            background-color: #1976d2;
        }
        .delete-btn {
            background-color: #f44336;
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
        }
        .modal-content input, .modal-content textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .modal-content textarea {
            resize: vertical;
            min-height: 80px;
        }
        .modal-content button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
        }
        .modal-content button:hover {
            background-color: #45a049;
        }
        .modal-content .cancel-btn {
            background-color: #f44336;
        }
        .modal-content .cancel-btn:hover {
            background-color: #d32f2f;
        }
        .message {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border-radius: 8px;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .error {
            background-color: #ffebee;
            color: #d32f2f;
        }
        @media screen and (max-width: 600px) {
            table, .add-form {
                width: 100%;
            }
            th, td {
                padding: 8px;
                font-size: 14px;
            }
        }
    </style>
    <script>
        function openEditModal(id, username, name, email, mobile_phone, address) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_mobile_phone').value = mobile_phone;
            document.getElementById('edit_address').value = address;
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
    </script>
</head>
<body>
    <h2>ข้อมูลสมาชิกทั้งหมด</h2>

    <?php if (isset($success)): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="message error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="add-form">
        <h3>เพิ่มสมาชิกใหม่</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
            <input type="text" name="name" placeholder="ชื่อ - นามสกุล" required>
            <input type="email" name="email" placeholder="อีเมล" required>
            <input type="text" name="mobile_phone" placeholder="เบอร์โทรศัพท์" required>
            <textarea name="address" placeholder="ที่อยู่"></textarea>
            <input type="password" name="password" placeholder="รหัสผ่าน" required>
            <button type="submit" name="add_member">เพิ่มสมาชิก</button>
        </form>
    </div>

    <table>
        <tr>
            <th>ลำดับ</th>
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
        $no = 1;

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $no++ . "</td>
                        <td>" . htmlspecialchars($row['username']) . "</td>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>" . htmlspecialchars($row['mobile_phone']) . "</td>
                        <td>" . nl2br(htmlspecialchars($row['address'])) . "</td>
                        <td>
                            <a href='#' class='action-btn edit-btn' onclick='openEditModal(" . $row['id'] . ", \"" . htmlspecialchars($row['username']) . "\", \"" . htmlspecialchars($row['name']) . "\", \"" . htmlspecialchars($row['email']) . "\", \"" . htmlspecialchars($row['mobile_phone']) . "\", \"" . htmlspecialchars($row['address']) . "\")'>แก้ไข</a>
                            <a href='#' class='action-btn delete-btn' onclick='confirmDelete(" . $row['id'] . ")'>ลบ</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>ไม่มีข้อมูลสมาชิก</td></tr>";
        }
        $conn->close();
        ?>
    </table>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>แก้ไขข้อมูลสมาชิก</h3>
            <form method="POST">
                <input type="hidden" name="edit_id" id="edit_id">
                <input type="text" name="username" id="edit_username" placeholder="ชื่อผู้ใช้" required>
                <input type="text" name="name" id="edit_name" placeholder="ชื่อ - นามสกุล" required>
                <input type="email" name="email" id="edit_email" placeholder="อีเมล" required>
                <input type="text" name="mobile_phone" id="edit_mobile_phone" placeholder="เบอร์โทรศัพท์" required>
                <textarea name="address" id="edit_address" placeholder="ที่อยู่"></textarea>
                <input type="password" name="password" placeholder="รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)">
                <button type="submit" name="edit_member">บันทึก</button>
                <button type="button" class="cancel-btn" onclick="closeEditModal()">ยกเลิก</button>
            </form>
        </div>
    </div>
</body>
</html>