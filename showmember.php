<?php
// showmember.php
// หน้าจัดการสมาชิก (สำหรับแอดมิน)
include "check_session.php";
include "conn.php";

// ตรวจสอบสถานะแอดมิน
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
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_name = uniqid() . '_' . basename($_FILES['profile_image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_image = $file_name;
        } else {
            $errors[] = "ไม่สามารถอัปโหลดรูปภาพได้";
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO customer (username, name, email, mobile_phone, address, password, is_admin, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $username, $name, $email, $mobile_phone, $address, $hashed_password, $is_admin, $profile_image);
        if ($stmt->execute()) {
            $success = "เพิ่มสมาชิกสำเร็จ!";
        } else {
            $errors[] = "เกิดข้อผิดพลาด: " . $conn->error;
        }
        $stmt->close();
    }
}

// ลบสมาชิก
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM customer WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: showmember.php?msg=ลบสมาชิกเรียบร้อยแล้ว");
    } else {
        $errors[] = "เกิดข้อผิดพลาดในการลบ: " . $conn->error;
    }
    $stmt->close();
    exit();
}

// อัปเดตสมาชิก (ผ่าน AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_member'])) {
    header('Content-Type: application/json');
    $edit_id = intval($_POST['edit_id']);
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile_phone = trim($_POST['mobile_phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);
    $is_admin = isset($_POST['is_admin']) && $_POST['is_admin'] == '1' ? 1 : 0;
    $profile_image = null;

    // ตรวจสอบข้อมูล
    if (empty($username)) $errors[] = "กรุณากรอกชื่อผู้ใช้";
    if (empty($name)) $errors[] = "กรุณากรอกชื่อ";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "กรุณากรอกอีเมลที่ถูกต้อง";
    if (empty($mobile_phone)) $errors[] = "กรุณากรอกเบอร์โทร";
    if (!empty($password) && strlen($password) < 6) $errors[] = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";

    // อัปโหลดรูปภาพ
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_name = uniqid() . '_' . basename($_FILES['profile_image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_image = $file_name;
        } else {
            $errors[] = "ไม่สามารถอัปโหลดรูปภาพได้";
        }
    }

    if (empty($errors)) {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE customer SET username=?, name=?, email=?, mobile_phone=?, address=?, password=?, is_admin=?, profile_image=IFNULL(?, profile_image) WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssisi", $username, $name, $email, $mobile_phone, $address, $hashed_password, $is_admin, $profile_image, $edit_id);
        } else {
            $sql = "UPDATE customer SET username=?, name=?, email=?, mobile_phone=?, address=?, is_admin=?, profile_image=IFNULL(?, profile_image) WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssisi", $username, $name, $email, $mobile_phone, $address, $is_admin, $profile_image, $edit_id);
        }

        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => true, 'message' => 'อัปเดตสมาชิกสำเร็จ']);
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัปเดต: " . $conn->error;
            $stmt->close();
            echo json_encode(['success' => false, 'errors' => $errors]);
        }
    } else {
        echo json_encode(['success' => false, 'errors' => $errors]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสมาชิก | แดชบอร์ดแอดมิน</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <!-- Sweet Alert -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.5/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #00b894;
            --danger-color: #e17055;
            --warning-color: #fdcb6e;
            --info-color: #74b9ff;
            --light-color: #f8f9fa;
            --dark-color: #2d3436;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-success: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
            --gradient-danger: linear-gradient(135deg, #e17055 0%, #fd79a8 100%);
            --gradient-warning: linear-gradient(135deg, #fdcb6e 0%, #f39c12 100%);
            --gradient-info: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            --shadow-soft: 0 10px 40px rgba(0,0,0,0.1);
            --shadow-medium: 0 20px 60px rgba(0,0,0,0.15);
            --shadow-strong: 0 30px 80px rgba(0,0,0,0.2);
            --border-radius: 15px;
            --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-animated {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(-45deg, #667eea, #764ba2, #00b894, #74b9ff);
            background-size: 400% 400%;
            animation: gradient 20s ease infinite;
            opacity: 0.05;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Navigation */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding: 0.75rem 1.5rem;
            box-shadow: var(--shadow-soft);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-custom .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
        }

        .nav-link-custom {
            color: var(--dark-color) !important;
            font-weight: 500;
            margin: 0 0.3rem;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: var(--transition);
            text-decoration: none;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .nav-link-custom:hover,
        .nav-link-custom.active {
            background: var(--gradient-primary);
            color: white !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }

        /* Page Header */
        .page-header {
            background: var(--gradient-primary);
            padding: 4rem 0 3rem;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 200" fill="rgba(255,255,255,0.1)"><polygon points="0,0 1000,0 1000,150 0,200"/></svg>');
            background-size: cover;
        }

        .page-title {
            color: white;
            font-size: 3rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.2rem;
            text-align: center;
            font-weight: 400;
        }

        /* Main Content */
        .main-content {
            padding: 0 2rem 3rem;
        }

        /* Stats Cards */
        .stats-row {
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        /* Add Member Form */
        .add-member-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .add-member-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-success);
        }

        .card-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .card-title {
            color: var(--dark-color);
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }

        /* Profile Upload */
        .profile-upload-container {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .profile-upload {
            position: relative;
            display: inline-block;
        }

        .profile-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e9ecef;
            box-shadow: var(--shadow-soft);
            transition: var(--transition);
        }

        .profile-preview:hover {
            transform: scale(1.05);
            border-color: var(--primary-color);
        }

        .upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
            cursor: pointer;
        }

        .upload-overlay:hover {
            opacity: 1;
        }

        .upload-icon {
            color: white;
            font-size: 2rem;
        }

        .upload-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #f8f9fa;
            border: 3px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .upload-placeholder:hover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.1);
        }

        .upload-placeholder i {
            font-size: 2rem;
            color: #6c757d;
        }

        /* Form Elements */
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: var(--transition);
            font-weight: 500;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        /* Buttons */
        .btn-custom {
            border: none;
            border-radius: var(--border-radius);
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .btn-custom::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transition: all 0.6s ease;
            transform: translate(-50%, -50%);
        }

        .btn-custom:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary-custom {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            color: white;
        }

        .btn-success-custom {
            background: var(--gradient-success);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 184, 148, 0.4);
        }

        .btn-success-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 184, 148, 0.6);
            color: white;
        }

        .btn-danger-custom {
            background: var(--gradient-danger);
            color: white;
            box-shadow: 0 4px 15px rgba(225, 112, 85, 0.4);
        }

        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(225, 112, 85, 0.6);
            color: white;
        }

        .btn-info-custom {
            background: var(--gradient-info);
            color: white;
            box-shadow: 0 4px 15px rgba(116, 185, 255, 0.4);
        }

        .btn-info-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(116, 185, 255, 0.6);
            color: white;
        }

        .btn-sm-custom {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }

        /* Member Table */
        .table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .table-custom {
            margin-bottom: 0;
            background: transparent;
        }

        .table-custom thead th {
            background: var(--gradient-primary);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1.5rem 1rem;
            border: none;
            font-size: 0.9rem;
        }

        .table-custom tbody td {
            padding: 1.5rem 1rem;
            border-color: #f1f3f4;
            vertical-align: middle;
            font-weight: 500;
        }

        .table-custom tbody tr {
            transition: var(--transition);
        }

        .table-custom tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }

        .member-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e9ecef;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: var(--transition);
        }

        .member-avatar:hover {
            transform: scale(1.1);
            border-color: var(--primary-color);
        }

        .avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #f8f9fa;
            border: 3px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 1.5rem;
        }

        .member-info h6 {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .member-info small {
            color: #6c757d;
            font-weight: 500;
        }

        .admin-badge {
            background: var(--gradient-warning);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(253, 203, 110, 0.4);
        }

        .user-badge {
            background: #e9ecef;
            color: var(--dark-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Search and Filter */
        .control-panel {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .search-container {
            position: relative;
            flex: 1;
            margin-right: 1rem;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background: #f8f9fa;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
            background: white;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.1rem;
        }

        /* Alerts */
        .alert-custom {
            border: none;
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            font-weight: 600;
            box-shadow: var(--shadow-soft);
            margin-bottom: 2rem;
        }

        .alert-success-custom {
            background: var(--gradient-success);
            color: white;
        }

        .alert-danger-custom {
            background: var(--gradient-danger);
            color: white;
        }

        /* Modal */
        .modal-custom .modal-content {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-strong);
            overflow: hidden;
        }

        .modal-custom .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-bottom: none;
            padding: 2rem;
        }

        .modal-custom .modal-title {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .modal-custom .btn-close {
            filter: invert(1);
        }

        .modal-custom .modal-body {
            padding: 2rem;
        }

        .modal-custom .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #f1f3f4;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-custom { padding: 1rem; }
            .page-header { padding: 3rem 0 2rem; }
            .page-title { font-size: 2rem; }
            .main-content { padding: 0 1rem 2rem; }
            .add-member-card { padding: 2rem; }
            .control-panel { padding: 1.5rem; }
            .search-container { margin-right: 0; margin-bottom: 1rem; }
            .table-container { overflow-x: auto; }
            .action-buttons { flex-direction: column; }
            .btn-custom { width: 100%; justify-content: center; }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Checkbox Toggle */
        .admin-toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .admin-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background: var(--gradient-warning);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animated"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" data-aos="fade-right">
                <i class="fas fa-users-cog me-2"></i>
                จัดการสมาชิก
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link-custom" href="admin_profile.php">
                            <i class="fas fa-user-circle me-1"></i>โปรไฟล์
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom" href="product_list.php">
                            <i class="fas fa-box me-1"></i>จัดการสินค้า
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom active" href="showmember.php">
                            <i class="fas fa-users me-1"></i>จัดการสมาชิก
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom" href="adminOrders.php">
                            <i class="fas fa-shopping-cart me-1"></i>คำสั่งซื้อ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>ออกจากระบบ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="page-title" data-aos="fade-up">
                <i class="fas fa-users-cog me-3"></i>
                จัดการสมาชิก
            </h1>
            <p class="page-subtitle" data-aos="fade-up" data-aos-delay="200">
                เพิ่ม แก้ไข และจัดการสมาชิกในระบบ
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Alert Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success-custom alert-custom" data-aos="fade-up">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert alert-danger-custom alert-custom" data-aos="fade-up">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php foreach ($errors as $e): ?>
                        <div><?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success-custom alert-custom" data-aos="fade-up">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <?php
            $total_members_result = $conn->query("SELECT COUNT(*) as total FROM customer");
            $total_members = $total_members_result->fetch_assoc()['total'];
            $admin_members_result = $conn->query("SELECT COUNT(*) as admin_count FROM customer WHERE is_admin = 1");
            $admin_members = $admin_members_result->fetch_assoc()['admin_count'];
            $regular_members = $total_members - $admin_members;
            ?>
            <div class="row stats-row" data-aos="fade-up" data-aos-delay="100">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-number"><?php echo $total_members; ?></div>
                        <div class="stat-label">สมาชิกทั้งหมด</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
                        <div class="stat-number"><?php echo $admin_members; ?></div>
                        <div class="stat-label">ผู้ดูแล</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user"></i></div>
                        <div class="stat-number"><?php echo $regular_members; ?></div>
                        <div class="stat-label">ผู้ใช้ทั่วไป</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-eye"></i></div>
                        <div class="stat-number" id="visibleMembers"><?php echo $total_members; ?></div>
                        <div class="stat-label">แสดงผล</div>
                    </div>
                </div>
            </div>

            <!-- Add Member Form -->
            <div class="add-member-card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus me-2"></i>เพิ่มสมาชิกใหม่
                    </h3>
                    <p class="card-subtitle">กรอกข้อมูลสมาชิกใหม่ที่ต้องการเพิ่มเข้าสู่ระบบ</p>
                </div>
                <form method="POST" enctype="multipart/form-data" id="addMemberForm">
                    <div class="profile-upload-container">
                        <div class="profile-upload">
                            <div class="upload-placeholder" onclick="document.getElementById('add_profile_image').click()">
                                <i class="fas fa-camera"></i>
                            </div>
                            <img id="addProfilePreview" src="" alt="Preview" class="profile-preview" style="display: none;">
                            <div class="upload-overlay" onclick="document.getElementById('add_profile_image').click()">
                                <i class="fas fa-edit upload-icon"></i>
                            </div>
                            <input type="file" id="add_profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="previewImage(this, 'addProfilePreview')">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-user me-1"></i>ชื่อผู้ใช้</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-id-card me-1"></i>ชื่อ - นามสกุล</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-envelope me-1"></i>อีเมล</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-phone me-1"></i>เบอร์โทรศัพท์</label>
                            <input type="text" class="form-control" name="mobile_phone" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>ที่อยู่</label>
                            <textarea class="form-control" name="address" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-lock me-1"></i>รหัสผ่าน</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-user-cog me-1"></i>สิทธิ์การใช้งาน</label>
                            <div class="d-flex align-items-center mt-2">
                                <label class="admin-toggle me-3">
                                    <input type="checkbox" name="is_admin" value="1">
                                    <span class="toggle-slider"></span>
                                </label>
                                <span>เป็นผู้ดูแลระบบ</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" name="add_member" class="btn btn-success-custom btn-custom">
                            <i class="fas fa-user-plus me-2"></i>เพิ่มสมาชิก
                        </button>
                        <button type="reset" class="btn btn-secondary btn-custom">
                            <i class="fas fa-undo me-2"></i>ล้างข้อมูล
                        </button>
                    </div>
                </form>
            </div>

            <!-- Control Panel -->
            <div class="control-panel" data-aos="fade-up" data-aos-delay="300">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" class="search-input" id="searchInput" placeholder="ค้นหาสมาชิก... (ชื่อ, อีเมล, เบอร์โทร)" onkeyup="searchMembers()">
                        </div>
                    </div>
                    <div class="col-lg-6 text-end">
                        <button class="btn btn-info-custom btn-custom" onclick="exportMembers()" title="Export to CSV">
                            <i class="fas fa-download me-2"></i>Export
                        </button>
                        <button class="btn btn-success-custom btn-custom" onclick="refreshTable()" title="รีเฟรชข้อมูล">
                            <i class="fas fa-sync-alt me-2"></i>รีเฟรช
                        </button>
                        <div class="btn-group" role="group">
                            <button class="btn btn-primary-custom btn-custom active" onclick="filterMembers('all')">
                                <i class="fas fa-users me-1"></i>ทั้งหมด
                            </button>
                            <button class="btn btn-warning btn-custom" onclick="filterMembers('admin')">
                                <i class="fas fa-user-shield me-1"></i>ผู้ดูแล
                            </button>
                            <button class="btn btn-secondary btn-custom" onclick="filterMembers('user')">
                                <i class="fas fa-user me-1"></i>ผู้ใช้
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Members Table -->
            <div class="table-container" data-aos="fade-up" data-aos-delay="400">
                <table class="table table-custom" id="memberTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>ลำดับ</th>
                            <th><i class="fas fa-image me-2"></i>รูปโปรไฟล์</th>
                            <th><i class="fas fa-user me-2"></i>ข้อมูลสมาชิก</th>
                            <th><i class="fas fa-envelope me-2"></i>อีเมล</th>
                            <th><i class="fas fa-phone me-2"></i>เบอร์โทร</th>
                            <th><i class="fas fa-map-marker-alt me-2"></i>ที่อยู่</th>
                            <th><i class="fas fa-user-cog me-2"></i>สิทธิ์</th>
                            <th><i class="fas fa-cogs me-2"></i>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM customer ORDER BY id ASC";
                        $result = $conn->query($sql);
                        $no = 1;
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <tr data-member-type="<?php echo $row['is_admin'] ? 'admin' : 'user'; ?>">
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $no++ ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($row['profile_image']): ?>
                                    <img src="uploads/<?= htmlspecialchars($row['profile_image']) ?>" alt="Profile" class="member-avatar">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="member-info">
                                    <h6><?= htmlspecialchars($row['name']) ?></h6>
                                    <small>@<?= htmlspecialchars($row['username']) ?></small>
                                </div>
                            </td>
                            <td>
                                <i class="fas fa-envelope text-muted me-2"></i>
                                <?= htmlspecialchars($row['email']) ?>
                            </td>
                            <td>
                                <i class="fas fa-phone text-muted me-2"></i>
                                <?= htmlspecialchars($row['mobile_phone']) ?>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['address']) ?>">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    <?= htmlspecialchars($row['address']) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($row['is_admin']): ?>
                                    <span class="admin-badge">
                                        <i class="fas fa-crown me-1"></i>ผู้ดูแล
                                    </span>
                                <?php else: ?>
                                    <span class="user-badge">
                                        <i class="fas fa-user me-1"></i>ผู้ใช้
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button onclick="viewMember(<?= $row['id'] ?>)" class="btn btn-info-custom btn-custom btn-sm-custom" title="ดูรายละเอียด">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="openEditModal(<?= $row['id'] ?>, '<?= addslashes($row['username']) ?>', '<?= addslashes($row['name']) ?>', '<?= addslashes($row['email']) ?>', '<?= addslashes($row['mobile_phone']) ?>', '<?= addslashes($row['address']) ?>', '<?= $row['profile_image'] ?>', <?= $row['is_admin'] ?>)" 
                                            class="btn btn-primary-custom btn-custom btn-sm-custom" title="แก้ไขข้อมูล">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmDelete(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>')" 
                                            class="btn btn-danger-custom btn-custom btn-sm-custom" title="ลบสมาชิก">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Member Modal -->
    <div class="modal fade modal-custom" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit me-2"></i>แก้ไขข้อมูลสมาชิก
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editMemberForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="profile-upload-container">
                            <div class="profile-upload">
                                <div class="upload-placeholder" onclick="document.getElementById('edit_profile_image').click()">
                                    <i class="fas fa-camera"></i>
                                </div>
                                <img id="profilePreview" src="" alt="Profile Preview" class="profile-preview" style="display: none;">
                                <div class="upload-overlay" onclick="document.getElementById('edit_profile_image').click()">
                                    <i class="fas fa-edit upload-icon"></i>
                                </div>
                                <input type="file" id="edit_profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="previewImage(this, 'profilePreview')">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" class="form-control" name="username" id="edit_username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชื่อ - นามสกุล</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">อีเมล</label>
                                <input type="email" class="form-control" name="email" id="edit_email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" name="mobile_phone" id="edit_mobile_phone" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">ที่อยู่</label>
                                <textarea class="form-control" name="address" id="edit_address" rows="3"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)</label>
                                <input type="password" class="form-control" name="password" id="edit_password" placeholder="เว้นว่างไว้หากไม่ต้องการเปลี่ยน">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label"><i class="fas fa-user-cog me-1"></i>สิทธิ์การใช้งาน</label>
                                <div class="d-flex align-items-center mt-2">
                                    <label class="admin-toggle me-3">
                                        <input type="checkbox" name="is_admin" id="edit_is_admin" value="1">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span>เป็นผู้ดูแลระบบ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>ยกเลิก
                        </button>
                        <button type="submit" class="btn btn-success-custom btn-custom">
                            <i class="fas fa-save me-1"></i>บันทึกการเปลี่ยนแปลง
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Member Modal -->
    <div class="modal fade modal-custom" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user me-2"></i>รายละเอียดสมาชิก
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewMemberContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <!-- Sweet Alert -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.5/sweetalert2.min.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true
        });

        // Base URL
        const baseUrl = 'http://localhost/2567/GitHub_OTEROBOT/e-commerce';

        // Preview image function
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const placeholder = input.parentNode.querySelector('.upload-placeholder');
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (placeholder) placeholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        }

        // Search members function
        function searchMembers() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('memberTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let visibleCount = 0;
            for (let i = 0; i < rows.length; i++) {
                const username = rows[i].cells[2].textContent.toLowerCase();
                const name = rows[i].cells[2].textContent.toLowerCase();
                const email = rows[i].cells[3].textContent.toLowerCase();
                const phone = rows[i].cells[4].textContent.toLowerCase();
                const address = rows[i].cells[5].textContent.toLowerCase();
                if (username.includes(input) || name.includes(input) || email.includes(input) || phone.includes(input) || address.includes(input)) {
                    rows[i].style.display = '';
                    visibleCount++;
                } else {
                    rows[i].style.display = 'none';
                }
            }
            document.getElementById('visibleMembers').textContent = visibleCount;
        }

        // Filter members function
        function filterMembers(type) {
            const table = document.getElementById('memberTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let visibleCount = 0;
            for (let i = 0; i < rows.length; i++) {
                const memberType = rows[i].getAttribute('data-member-type');
                if (type === 'all' || memberType === type) {
                    rows[i].style.display = '';
                    visibleCount++;
                } else {
                    rows[i].style.display = 'none';
                }
            }
            document.getElementById('visibleMembers').textContent = visibleCount;
            document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }

        // Open edit modal function
        function openEditModal(id, username, name, email, mobile_phone, address, profile_image, is_admin) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_mobile_phone').value = mobile_phone;
            document.getElementById('edit_address').value = address;
            document.getElementById('edit_is_admin').checked = is_admin == 1;
            const preview = document.getElementById('profilePreview');
            const placeholder = document.querySelector('#editModal .upload-placeholder');
            if (profile_image) {
                preview.src = `${baseUrl}/uploads/${profile_image}`;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            } else {
                preview.style.display = 'none';
                if (placeholder) placeholder.style.display = 'flex';
            }
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }

        // View member function
        function viewMember(memberId) {
            const table = document.getElementById('memberTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                const editButton = rows[i].querySelector(`[onclick*="openEditModal(${memberId},"]`);
                if (editButton) {
                    const cells = rows[i].cells;
                    const profileImg = cells[1].querySelector('img');
                    const memberInfo = cells[2].querySelector('.member-info');
                    const email = cells[3].textContent.replace(/.*fa-envelope.*/, '').trim();
                    const phone = cells[4].textContent.replace(/.*fa-phone.*/, '').trim();
                    const address = cells[5].getAttribute('title') || cells[5].textContent.replace(/.*fa-map-marker-alt.*/, '').trim();
                    const memberType = rows[i].getAttribute('data-member-type');
                    const name = memberInfo.querySelector('h6').textContent;
                    const username = memberInfo.querySelector('small').textContent;
                    const content = `
                        <div class="row">
                            <div class="col-md-4 text-center">
                                ${profileImg ? 
                                    `<img src="${profileImg.src}" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">` :
                                    '<div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px; font-size: 3rem; color: #6c757d;"><i class="fas fa-user"></i></div>'
                                }
                                <div class="mt-3">
                                    ${memberType === 'admin' ? 
                                        '<span class="admin-badge"><i class="fas fa-crown me-1"></i>ผู้ดูแลระบบ</span>' :
                                        '<span class="user-badge"><i class="fas fa-user me-1"></i>ผู้ใช้ทั่วไป</span>'
                                    }
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <strong><i class="fas fa-user me-2 text-primary"></i>ชื่อ-นามสกุล:</strong>
                                    <p class="mb-2">${name}</p>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-at me-2 text-info"></i>ชื่อผู้ใช้:</strong>
                                    <p class="mb-2">${username}</p>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-envelope me-2 text-success"></i>อีเมล:</strong>
                                    <p class="mb-2">${email}</p>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-phone me-2 text-warning"></i>เบอร์โทรศัพท์:</strong>
                                    <p class="mb-2">${phone}</p>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-map-marker-alt me-2 text-danger"></i>ที่อยู่:</strong>
                                    <p class="mb-2">${address}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    document.getElementById('viewMemberContent').innerHTML = content;
                    const modal = new bootstrap.Modal(document.getElementById('viewModal'));
                    modal.show();
                    break;
                }
            }
        }

        // Confirm delete function
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'ยืนยันการลบสมาชิก',
                html: `คุณต้องการลบสมาชิก <strong>"${name}"</strong> หรือไม่?<br><small class="text-muted">การดำเนินการนี้ไม่สามารถย้อนกลับได้</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e17055',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-1"></i>ลบสมาชิก',
                cancelButtonText: '<i class="fas fa-times me-1"></i>ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังลบสมาชิก...',
                        html: '<div class="loading mx-auto"></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                    setTimeout(() => {
                        window.location.href = `${baseUrl}/showmember.php?delete_id=${id}`;
                    }, 1000);
                }
            });
        }

        // Export members to CSV
        function exportMembers() {
            const table = document.getElementById('memberTable');
            const rows = table.querySelectorAll('tbody tr');
            let csvContent = '\ufeff';
            csvContent += 'ลำดับ,ชื่อผู้ใช้,ชื่อ-นามสกุล,อีเมล,เบอร์โทร,ที่อยู่,สิทธิ์\n';
            let rowIndex = 1;
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].style.display === 'none') continue;
                const cells = rows[i].cells;
                const memberInfo = cells[2].querySelector('.member-info');
                const name = memberInfo.querySelector('h6').textContent;
                const username = memberInfo.querySelector('small').textContent.replace('@', '');
                const email = cells[3].textContent.replace(/.*fa-envelope.*/, '').trim();
                const phone = cells[4].textContent.replace(/.*fa-phone.*/, '').trim();
                const address = cells[5].getAttribute('title') || cells[5].textContent.replace(/.*fa-map-marker-alt.*/, '').trim();
                const memberType = rows[i].getAttribute('data-member-type') === 'admin' ? 'ผู้ดูแล' : 'ผู้ใช้';
                csvContent += `"${rowIndex}","${username}","${name}","${email}","${phone}","${address}","${memberType}"\n`;
                rowIndex++;
            }
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `members_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            Swal.fire({
                icon: 'success',
                title: 'ส่งออกข้อมูลสำเร็จ',
                text: 'ไฟล์ CSV ได้ถูกดาวน์โหลดแล้ว',
                timer: 2000,
                timerProgressBar: true
            });
        }

        // Refresh table
        function refreshTable() {
            const refreshBtn = event.target;
            const originalHTML = refreshBtn.innerHTML;
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<span class="loading"></span> รีเฟรช...';
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // Edit member via AJAX
        document.getElementById('editMemberForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> กำลังบันทึก...';
            const formData = new FormData(this);
            formData.append('edit_member', true);
            fetch(`${baseUrl}/showmember.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: data.message,
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        html: data.errors.join('<br>'),
                        confirmButtonColor: '#e17055'
                    });
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้: ' + error.message,
                    confirmButtonColor: '#e17055'
                });
            });
        });

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (!this.id.includes('editMemberForm')) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="loading"></span> กำลังบันทึก...';
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }, 10000);
                }
            });
        });

        // Enhanced form validation
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid') && this.value.trim()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });

        // Email validation
        document.querySelectorAll('input[type="email"]').forEach(input => {
            input.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailRegex.test(this.value)) {
                    this.classList.add('is-invalid');
                } else if (this.value) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });

        // Password strength validation
        document.querySelectorAll('input[type="password"]').forEach(input => {
            input.addEventListener('input', function() {
                if (this.value && this.value.length < 6) {
                    this.classList.add('is-invalid');
                } else if (this.value) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('searchInput');
                if (searchInput.value) {
                    searchInput.value = '';
                    searchMembers();
                }
            }
        });

        // Enhanced table interactions
        document.querySelectorAll('.table-custom tbody tr').forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
                this.style.zIndex = '10';
            });
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.zIndex = '1';
            });
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            const totalRows = document.querySelectorAll('#memberTable tbody tr').length;
            document.getElementById('visibleMembers').textContent = totalRows;
        });

        // Add CSS for form validation and loading
        const style = document.createElement('style');
        style.textContent = `
            .form-control.is-invalid {
                border-color: #e17055;
                box-shadow: 0 0 0 0.25rem rgba(225, 112, 85, 0.25);
            }
            .form-control.is-valid {
                border-color: #00b894;
                box-shadow: 0 0 0 0.25rem rgba(0, 184, 148, 0.25);
            }
            .upload-placeholder:hover {
                background: rgba(102, 126, 234, 0.1);
                border-color: var(--primary-color);
            }
            .member-avatar:hover {
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
            }
            .action-buttons .btn:hover {
                transform: translateY(-1px);
            }
            .stat-card:hover {
                animation: pulse 0.5s ease;
            }
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
<?php $conn->close(); ?>