<?php
// admin_profile.php
session_start();
include "check_session.php";
include "conn.php";

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: show_profile.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, name, mobile_phone, address, profile_image FROM customer WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();
$result->close();

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

    $profile_image = $admin['profile_image'] ?? null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "uploads/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        if (!is_writable($upload_dir)) {
            $errors[] = "ไม่สามารถเขียนไฟล์ในโฟลเดอร์ uploads/ ได้ กรุณาตรวจสอบการอนุญาต";
        } else {
            $file_name = uniqid() . '_' . basename($_FILES['profile_image']['name']);
            $target_file = $upload_dir . $file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($image_file_type, $allowed_types)) {
                $errors[] = "เฉพาะไฟล์ JPG, JPEG, PNG, GIF เท่านั้นที่อนุญาต";
            } elseif ($_FILES['profile_image']['size'] > 5000000) {
                $errors[] = "ไฟล์ภาพต้องไม่เกิน 5MB";
            } else {
                if ($profile_image && file_exists($upload_dir . $profile_image)) {
                    unlink($upload_dir . $profile_image);
                }
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                    $profile_image = $file_name;
                } else {
                    $errors[] = "เกิดข้อผิดพลาดในการอัปโหลดภาพ";
                }
            }
        }
    }

    if (empty($errors)) {
        if (empty($address)) {
            $address = 'N/A';
        }
        try {
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
                $_SESSION['username'] = $username;
                $sql = "SELECT username, email, name, mobile_phone, address, profile_image FROM customer WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $admin = $result->fetch_assoc();
                $stmt->close();
            } else {
                $errors[] = "เกิดข้อผิดพลาดในการอัปเดต: " . $update_stmt->error;
            }
            $update_stmt->close();
        } catch (Exception $e) {
            $errors[] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดแอดมิน | โปรไฟล์</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #ec4899;
            --success-color: #22c55e;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #06b6d4;
            --light-color: #f8f9fa;
            --dark-color: #1f2937;
            --gradient-primary: linear-gradient(135deg, #3b82f6 0%, #ec4899 100%);
            --gradient-success: linear-gradient(135deg, #22c55e 0%, #86efac 100%);
            --gradient-danger: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            --shadow-soft: 0 10px 40px rgba(0,0,0,0.1);
            --shadow-medium: 0 20px 60px rgba(0,0,0,0.15);
            --border-radius: 15px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #ec4899 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .bg-animated {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(-45deg, #3b82f6, #ec4899, #00c4cc, #ff6b6b);
            background-size: 400% 400%;
            animation: gradient 12s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite, drift 10s linear infinite;
        }

        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
        @keyframes drift { 0% { transform: translateX(0); } 50% { transform: translateX(20px); } 100% { transform: translateX(0); } }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 2rem;
            box-shadow: var(--shadow-soft);
        }

        .navbar-custom .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            text-decoration: none;
        }

        .nav-link-custom {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem !important;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .nav-link-custom:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white !important;
            transform: translateY(-2px);
        }

        .nav-link-custom.active {
            background: rgba(255, 255, 255, 0.3);
            color: white !important;
        }

        .main-content { padding: 2rem 0; min-height: calc(100vh - 100px); }

        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
            border: 1px solid rgba(102, 126, 234, 0.5);
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            z-index: -1;
            animation: glow 3s infinite;
        }

        @keyframes glow { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .profile-header {
            background: var(--gradient-primary);
            padding: 3rem 2rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="1000,100 1000,0 0,100"/></svg>');
            background-size: cover;
        }

        .profile-avatar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .profile-avatar:hover img { transform: scale(1.05); }

        .profile-avatar .avatar-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .profile-name {
            color: white;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 3px 6px rgba(0, 0, 0, 0.4);
            letter-spacing: 1px;
        }

        .profile-role {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.3rem;
            font-weight: 400;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .profile-body { padding: 2rem; }

        .info-item {
            background: rgba(0, 0, 0, 0.02);
            border-radius: 10px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: rgba(0, 0, 0, 0.05);
            transform: translateX(5px);
        }

        .info-label {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.3rem;
        }

        .info-value {
            font-size: 1.1rem;
            color: var(--dark-color);
            font-weight: 500;
        }

        .btn-gradient {
            background: var(--gradient-primary);
            border: none;
            border-radius: var(--border-radius);
            padding: 0.8rem 2rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .btn-success-gradient {
            background: linear-gradient(135deg, #22c55e, #86efac);
        }

        .btn-success-gradient:hover {
            background: linear-gradient(135deg, #86efac, #22c55e);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .modal-content {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            backdrop-filter: blur(20px);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-bottom: none;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .modal-title { font-weight: 700; }
        .btn-close { filter: invert(1); }

        .form-control, .form-select, .form-control:focus {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.8rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .alert-custom {
            border: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            box-shadow: var(--shadow-soft);
        }

        .alert-success-custom { background: var(--gradient-success); color: white; }
        .alert-danger-custom { background: var(--gradient-danger); color: white; }

        .image-upload-preview {
            max-width: 150px;
            max-height: 150px;
            border-radius: 10px;
            margin-top: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .navbar-custom { padding: 1rem; }
            .profile-header { padding: 2rem 1rem 1.5rem; }
            .profile-name { font-size: 1.8rem; }
            .profile-role { font-size: 1.1rem; }
            .profile-body { padding: 1.5rem; }
        }

        @media (max-width: 576px) {
            .profile-card { margin: 0 1rem; }
            .modal-dialog { margin: 0.5rem; }
            .form-control, .form-select { font-size: 0.9rem; padding: 0.7rem; }
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.8);
            transform: scale(0);
            animation: ripple-animation 0.8s linear;
            pointer-events: none;
        }

        @keyframes ripple-animation {
            to { transform: scale(4); opacity: 0; }
        }

        .form-control.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 0.25rem rgba(239, 68, 68, 0.25);
        }

        .form-control.is-valid {
            border-color: #22c55e;
            box-shadow: 0 0 0 0.25rem rgba(34, 197, 94, 0.25);
        }

        /* Custom SweetAlert2 Styles */
        .swal2-popup {
            font-family: 'Sarabun', sans-serif;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            padding: 20px;
            border: 2px solid rgba(34, 197, 94, 0.3);
        }

        .swal2-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1f2937;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .swal2-content {
            font-size: 1.2rem;
            color: #1f2937;
        }

        .swal2-success-ring {
            border: 4px solid rgba(34, 197, 94, 0.3) !important;
        }

        .swal2-success-line-tip,
        .swal2-success-line-long {
            background-color: #22c55e !important;
        }

        .swal2-confirm {
            background: linear-gradient(135deg, #22c55e, #86efac);
            border: none;
            border-radius: 10px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        }

        .swal2-confirm:hover {
            background: linear-gradient(135deg, #86efac, #22c55e);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(34, 197, 94, 0.5);
        }

        .swal2-icon.swal2-success {
            animation: pulse 1s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .swal2-timer-progress-bar {
            background: linear-gradient(135deg, #22c55e, #86efac);
        }
    </style>
</head>
<body>
    <div class="bg-animated"></div>
    <div class="particles"></div>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" data-aos="fade-right">
                <i class="fas fa-user-shield me-2"></i>แดชบอร์ดแอดมิน
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link-custom active" href="admin_profile.php"><i class="fas fa-user-circle me-1"></i>โปรไฟล์</a></li>
                    <li class="nav-item"><a class="nav-link-custom" href="product_list.php"><i class="fas fa-box me-1"></i>จัดการสินค้า</a></li>
                    <li class="nav-item"><a class="nav-link-custom" href="showmember.php"><i class="fas fa-users me-1"></i>จัดการสมาชิก</a></li>
                    <li class="nav-item"><a class="nav-link-custom" href="adminOrders.php"><i class="fas fa-shopping-cart me-1"></i>คำสั่งซื้อ</a></li>
                    <li class="nav-item"><a class="nav-link-custom" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>ออกจากระบบ</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="main-content">
        <div class="container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger-custom alert-custom" data-aos="fade-up">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success-custom alert-custom" data-aos="fade-up">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="profile-card" data-aos="fade-up" data-aos-duration="1000">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <?php if ($admin && $admin['profile_image']): ?>
                                    <img src="uploads/<?= htmlspecialchars($admin['profile_image']) ?>" alt="Profile Image">
                                <?php else: ?>
                                    <div class="avatar-placeholder"><i class="fas fa-user"></i></div>
                                <?php endif; ?>
                            </div>
                            <h2 class="profile-name" data-aos="fade-up" data-aos-delay="200">
                                <?= $admin ? htmlspecialchars($admin['name']) : 'ไม่พบข้อมูล' ?>
                            </h2>
                            <p class="profile-role" data-aos="fade-up" data-aos-delay="400">
                                <i class="fas fa-crown me-2"></i>ผู้ดูแลระบบ
                            </p>
                            <button class="btn btn-gradient" onclick="openEditModal()" data-aos="fade-up" data-aos-delay="600">
                                <i class="fas fa-edit me-2"></i>แก้ไขโปรไฟล์
                            </button>
                        </div>
                        <div class="profile-body">
                            <?php if ($admin): ?>
                                <div class="row">
                                    <div class="col-md-6" data-aos="fade-right" data-aos-delay="200">
                                        <div class="info-item">
                                            <div class="info-label"><i class="fas fa-user me-2"></i>ชื่อผู้ใช้</div>
                                            <div class="info-value"><?= htmlspecialchars($admin['username']) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" data-aos="fade-left" data-aos-delay="200">
                                        <div class="info-item">
                                            <div class="info-label"><i class="fas fa-envelope me-2"></i>อีเมล</div>
                                            <div class="info-value"><?= htmlspecialchars($admin['email']) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" data-aos="fade-right" data-aos-delay="400">
                                        <div class="info-item">
                                            <div class="info-label"><i class="fas fa-id-card me-2"></i>ชื่อ - นามสกุล</div>
                                            <div class="info-value"><?= htmlspecialchars($admin['name']) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6" data-aos="fade-left" data-aos-delay="400">
                                        <div class="info-item">
                                            <div class="info-label"><i class="fas fa-phone me-2"></i>เบอร์โทรศัพท์</div>
                                            <div class="info-value"><?= htmlspecialchars($admin['mobile_phone']) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-12" data-aos="fade-up" data-aos-delay="600">
                                        <div class="info-item">
                                            <div class="info-label"><i class="fas fa-map-marker-alt me-2"></i>ที่อยู่</div>
                                            <div class="info-value"><?= nl2br(htmlspecialchars($admin['address'])) ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5" data-aos="fade-up">
                                    <i class="fas fa-exclamation-circle text-muted" style="font-size: 4rem;"></i>
                                    <h3 class="mt-3 text-muted">ไม่พบข้อมูลโปรไฟล์</h3>
                                    <p class="text-muted">กรุณาติดต่อผู้ดูแลระบบ</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>แก้ไขข้อมูลโปรไฟล์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="profileForm">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_username" class="form-label"><i class="fas fa-user me-1"></i>ชื่อผู้ใช้</label>
                                    <input type="text" class="form-control" name="username" id="edit_username" value="<?= htmlspecialchars($admin['username'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label"><i class="fas fa-id-card me-1"></i>ชื่อ - นามสกุล</label>
                                    <input type="text" class="form-control" name="name" id="edit_name" value="<?= htmlspecialchars($admin['name'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_email" class="form-label"><i class="fas fa-envelope me-1"></i>อีเมล</label>
                                    <input type="email" class="form-control" name="email" id="edit_email" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_mobile_phone" class="form-label"><i class="fas fa-phone me-1"></i>เบอร์โทรศัพท์</label>
                                    <input type="text" class="form-control" name="mobile_phone" id="edit_mobile_phone" value="<?= htmlspecialchars($admin['mobile_phone'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_address" class="form-label"><i class="fas fa-map-marker-alt me-1"></i>ที่อยู่</label>
                                    <textarea class="form-control" name="address" id="edit_address" rows="3"><?= htmlspecialchars($admin['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label"><i class="fas fa-key me-1"></i>รหัสผ่านใหม่ (ถ้าต้องการเปลี่ยน)</label>
                                    <input type="password" class="form-control" name="password" id="password" placeholder="เว้นว่างไว้หากไม่ต้องการเปลี่ยน">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label"><i class="fas fa-camera me-1"></i>ภาพโปรไฟล์</label>
                                    <input type="file" class="form-control" name="profile_image" id="profile_image" accept="image/*">
                                    <div class="mt-2">
                                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>รองรับไฟล์ JPG, JPEG, PNG, GIF ขนาดไม่เกิน 5MB</small>
                                    </div>
                                    <div id="imagePreview"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>ยกเลิก</button>
                        <button type="submit" name="update_profile" class="btn btn-success-gradient" id="submitBtn"><i class="fas fa-save me-1"></i>บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.5/sweetalert2.min.js"></script>
    <script>
        AOS.init({ duration: 1000, easing: 'ease-in-out', once: true });
        function openEditModal() {
            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            document.getElementById('edit_username').value = '<?= htmlspecialchars($admin['username'] ?? '') ?>';
            document.getElementById('edit_name').value = '<?= htmlspecialchars($admin['name'] ?? '') ?>';
            document.getElementById('edit_email').value = '<?= htmlspecialchars($admin['email'] ?? '') ?>';
            document.getElementById('edit_mobile_phone').value = '<?= htmlspecialchars($admin['mobile_phone'] ?? '') ?>';
            document.getElementById('edit_address').value = '<?= htmlspecialchars($admin['address'] ?? '') ?>';
            document.getElementById('imagePreview').innerHTML = '';
            modal.show();
        }
        document.getElementById('editModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('imagePreview').innerHTML = '';
            document.getElementById('profile_image').value = '';
        });
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewDiv = document.getElementById('imagePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewDiv.innerHTML = `
                        <div class="mt-3">
                            <p class="mb-2"><small class="text-muted">ตัวอย่างภาพที่เลือก:</small></p>
                            <img src="${e.target.result}" class="image-upload-preview" alt="Preview">
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                previewDiv.innerHTML = '';
            }
        });
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> กำลังบันทึก...';
            setTimeout(function() {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }, 10000);
        });
        <?php if (!empty($success)): ?>
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ!',
            text: '<?= htmlspecialchars($success) ?>',
            footer: '<small>ข้อมูลของคุณได้รับการอัปเดตเรียบร้อยแล้ว!</small>',
            showConfirmButton: true,
            confirmButtonText: 'ตกลง',
            customClass: {
                popup: 'swal2-popup',
                title: 'swal2-title',
                content: 'swal2-content',
                confirmButton: 'swal2-confirm',
                icon: 'swal2-icon swal2-success'
            },
            backdrop: `rgba(34, 197, 94, 0.2) url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><circle cx="50" cy="50" r="10" fill="rgba(255,255,255,0.3)" opacity="0.5"><animate attributeName="r" values="10;20;10" dur="2s" repeatCount="indefinite"/></circle></svg>') center center / 100px 100px`,
            timer: 4000,
            timerProgressBar: true,
            showClass: {
                popup: 'animate__animated animate__bounceIn'
            },
            hideClass: {
                popup: 'animate__animated animate__bounceOut'
            }
        });
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด!',
            html: '<?php foreach ($errors as $error): ?><div><?= htmlspecialchars($error) ?></div><?php endforeach; ?>',
            confirmButtonText: 'ตกลง',
            customClass: {
                popup: 'swal2-popup',
                title: 'swal2-title',
                content: 'swal2-content',
                confirmButton: 'swal2-confirm'
            },
            backdrop: `rgba(239, 68, 68, 0.2)`,
            showClass: {
                popup: 'animate__animated animate__shakeX'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOut'
            }
        });
        <?php endif; ?>
        document.querySelectorAll('.info-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(10px) scale(1.02)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0) scale(1)';
            });
        });
        document.querySelectorAll('.btn-gradient, .btn-success-gradient').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.background = 'rgba(255, 255, 255, 0.8)';
                ripple.classList.add('ripple');
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 800);
            });
        });
        function createParticles() {
            const particlesContainer = document.querySelector('.particles');
            for (let i = 0; i < 10; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                const size = Math.random() * 10 + 5;
                const top = Math.random() * 100;
                const left = Math.random() * 100;
                const delay = Math.random() * 5;
                const duration = 5 + Math.random() * 5;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.top = top + '%';
                particle.style.left = left + '%';
                particle.style.animationDelay = delay + 's';
                particle.style.animationDuration = duration + 's';
                particlesContainer.appendChild(particle);
            }
        }
        createParticles();
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.15)';
                navbar.style.backdropFilter = 'blur(25px)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.1)';
                navbar.style.backdropFilter = 'blur(20px)';
            }
        });
        document.querySelectorAll('.nav-link-custom').forEach(link => {
            if (link.getAttribute('href') !== '#' && !link.getAttribute('href').includes('logout')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const overlay = document.createElement('div');
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(59, 130, 246, 0.9);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        z-index: 9999;
                        backdrop-filter: blur(10px);
                    `;
                    overlay.innerHTML = `
                        <div style="text-align: center; color: white;">
                            <div class="loading" style="width: 40px; height: 40px; margin: 0 auto 20px;"></div>
                            <h3>กำลังโหลด...</h3>
                        </div>
                    `;
                    document.body.appendChild(overlay);
                    setTimeout(() => {
                        window.location.href = this.getAttribute('href');
                    }, 500);
                });
            }
        });
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
    </script>
</body>
</html>