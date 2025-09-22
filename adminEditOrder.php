<?php
// adminEditOrder.php
include "check_session.php";
include "conn.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: show_profile.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$success = '';
$error = '';

// ถ้ามีการส่งฟอร์มแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_price = $_POST['total_price'];
    $order_date = $_POST['order_date'];
    
    $sql = "UPDATE orders SET total_price=?, order_date=? WHERE order_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dsi", $total_price, $order_date, $order_id);
    
    if ($stmt->execute()) {
        $success = "แก้ไขคำสั่งซื้อเรียบร้อยแล้ว";
    } else {
        $error = "เกิดข้อผิดพลาดในการแก้ไข: " . $conn->error;
    }
}

// ดึงข้อมูลคำสั่งซื้อมาแสดง
$sql = "SELECT o.*, c.username, c.name, c.email 
        FROM orders o 
        JOIN customer c ON o.customer_id = c.id 
        WHERE o.order_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("ไม่พบคำสั่งซื้อที่เลือก");
}

// Note: Removed order_items functionality as table doesn't exist in database
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขคำสั่งซื้อ #<?php echo $order_id; ?> | แดshboard</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

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

        /* Breadcrumb */
        .breadcrumb-custom {
            background: white;
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
        }

        .breadcrumb-custom a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-custom a:hover {
            color: var(--secondary-color);
        }

        /* Order Info Card */
        .order-info-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .order-info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-info);
        }

        .order-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .order-id-badge {
            background: var(--gradient-primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .order-status {
            background: var(--gradient-success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Customer Info */
        .customer-card {
            background: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .customer-info h6 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .customer-detail {
            color: #6c757d;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        /* Edit Form */
        .edit-form-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .edit-form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-warning);
        }

        .form-section-title {
            color: var(--dark-color);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            gap: 0.5rem;
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

        .btn-secondary-custom {
            background: #6c757d;
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);
        }

        .btn-secondary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.6);
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

        /* Order Items */
        .items-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .items-card-header {
            background: var(--gradient-info);
            color: white;
            padding: 1.5rem;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .items-list {
            padding: 0;
        }

        .item-row {
            padding: 1.5rem;
            border-bottom: 1px solid #f1f3f4;
            transition: var(--transition);
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-row:hover {
            background: #f8f9fa;
        }

        .item-name {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .item-details {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 700;
            color: var(--success-color);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-custom { padding: 1rem; }
            .page-header { padding: 3rem 0 2rem; }
            .page-title { font-size: 2rem; }
            .main-content { padding: 0 1rem 2rem; }
            .edit-form-card { padding: 2rem; }
            .order-info-card { padding: 1.5rem; }
            
            .order-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .btn-custom {
                width: 100%;
                justify-content: center;
            }
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

        /* Validation States */
        .form-control.is-invalid {
            border-color: #e17055;
            box-shadow: 0 0 0 0.25rem rgba(225, 112, 85, 0.25);
        }
        
        .form-control.is-valid {
            border-color: #00b894;
            box-shadow: 0 0 0 0.25rem rgba(0, 184, 148, 0.25);
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
                <i class="fas fa-edit me-2"></i>
                แก้ไขคำสั่งซื้อ
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
                        <a class="nav-link-custom" href="showmember.php">
                            <i class="fas fa-users me-1"></i>จัดการสมาชิก
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom active" href="adminOrders.php">
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
                <i class="fas fa-edit me-3"></i>
                แก้ไขคำสั่งซื้อ
            </h1>
            <p class="page-subtitle" data-aos="fade-up" data-aos-delay="200">
                จัดการและแก้ไขข้อมูลคำสั่งซื้อ #<?php echo $order_id; ?>
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Breadcrumb -->
            <div class="breadcrumb-custom" data-aos="fade-up">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="admin_profile.php"><i class="fas fa-home me-1"></i>แดชบอร์ด</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="adminOrders.php"><i class="fas fa-shopping-cart me-1"></i>คำสั่งซื้อ</a>
                        </li>
                        <li class="breadcrumb-item active">แก้ไขคำสั่งซื้อ #<?php echo $order_id; ?></li>
                    </ol>
                </nav>
            </div>

            <!-- Alert Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success-custom alert-custom" data-aos="fade-up">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger-custom alert-custom" data-aos="fade-up">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Order Info -->
                <div class="col-lg-8">
                    <!-- Order Header -->
                    <div class="order-info-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="order-header">
                            <div class="order-id-badge">
                                <i class="fas fa-receipt me-2"></i>
                                คำสั่งซื้อ #<?php echo $order_id; ?>
                            </div>
                            <div class="order-status">
                                <i class="fas fa-clock me-1"></i>
                                กำลังดำเนินการ
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="customer-detail">
                                    <i class="fas fa-calendar-alt me-2 text-info"></i>
                                    <strong>วันที่สั่งซื้อ:</strong>
                                </div>
                                <p class="mb-2"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="customer-detail">
                                    <i class="fas fa-baht-sign me-2 text-success"></i>
                                    <strong>ราคารวมปัจจุบัน:</strong>
                                </div>
                                <p class="mb-2 text-success fw-bold fs-5">฿<?php echo number_format($order['total_price'], 2); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <div class="edit-form-card" data-aos="fade-up" data-aos-delay="200">
                        <h3 class="form-section-title">
                            <i class="fas fa-edit text-warning"></i>
                            แก้ไขข้อมูลคำสั่งซื้อ
                        </h3>

                        <form method="POST" id="editOrderForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-calendar-alt text-info"></i>
                                        วันที่และเวลาสั่งซื้อ
                                    </label>
                                    <input type="datetime-local" 
                                           name="order_date" 
                                           class="form-control"
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($order['order_date'])); ?>" 
                                           required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-baht-sign text-success"></i>
                                        ราคารวม (บาท)
                                    </label>
                                    <input type="number" 
                                           step="0.01" 
                                           min="0"
                                           name="total_price" 
                                           class="form-control"
                                           value="<?php echo $order['total_price']; ?>" 
                                           required
                                           id="totalPriceInput">
                                </div>
                            </div>

                            <div class="d-flex gap-3 flex-wrap">
                                <button type="submit" class="btn btn-success-custom btn-custom" id="saveBtn">
                                    <i class="fas fa-save"></i>
                                    บันทึกการแก้ไข
                                </button>
                                <a href="adminOrders.php" class="btn btn-secondary-custom btn-custom">
                                    <i class="fas fa-times"></i>
                                    ยกเลิก
                                </a>
                                <button type="button" class="btn btn-danger-custom btn-custom" onclick="resetForm()">
                                    <i class="fas fa-undo"></i>
                                    รีเซ็ต
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Customer Info & Order Items -->
                <div class="col-lg-4">
                    <!-- Customer Info -->
                    <div class="customer-card" data-aos="fade-up" data-aos-delay="300">
                        <h6><i class="fas fa-user me-2 text-primary"></i>ข้อมูลลูกค้า</h6>
                        <div class="customer-detail">
                            <strong>ชื่อ:</strong> <?php echo htmlspecialchars($order['name']); ?>
                        </div>
                        <div class="customer-detail">
                            <strong>ชื่อผู้ใช้:</strong> @<?php echo htmlspecialchars($order['username']); ?>
                        </div>
                        <div class="customer-detail">
                            <strong>อีเมล:</strong> <?php echo htmlspecialchars($order['email']); ?>
                        </div>
                        <div class="customer-detail">
                            <strong>รหัสลูกค้า:</strong> #<?php echo $order['customer_id']; ?>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="customer-card" data-aos="fade-up" data-aos-delay="400">
                        <h6><i class="fas fa-info-circle me-2 text-info"></i>สรุปคำสั่งซื้อ</h6>
                        <div class="customer-detail">
                            <strong>หมายเลขคำสั่งซื้อ:</strong> #<?php echo $order_id; ?>
                        </div>
                        <div class="customer-detail">
                            <strong>สถานะ:</strong> <span class="badge bg-success">กำลังดำเนินการ</span>
                        </div>
                        <div class="customer-detail">
                            <strong>วันที่สร้าง:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?>
                        </div>
                        <div class="customer-detail">
                            <strong>ยอดรวม:</strong> <span class="text-success fw-bold">฿<?php echo number_format($order['total_price'], 2); ?></span>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="customer-card" data-aos="fade-up" data-aos-delay="500">
                        <h6><i class="fas fa-bolt me-2 text-warning"></i>การดำเนินการด่วน</h6>
                        <div class="d-grid gap-2">
                            <a href="adminViewOrder.php?order_id=<?php echo $order_id; ?>" 
                               class="btn btn-info-custom btn-custom btn-sm">
                                <i class="fas fa-eye me-1"></i>ดูรายละเอียดเต็ม
                            </a>
                            <button onclick="printOrder()" class="btn btn-secondary-custom btn-custom btn-sm">
                                <i class="fas fa-print me-1"></i>พิมพ์คำสั่งซื้อ
                            </button>
                            <button onclick="confirmDelete()" class="btn btn-danger-custom btn-custom btn-sm">
                                <i class="fas fa-trash me-1"></i>ลบคำสั่งซื้อ
                            </button>
                        </div>
                    </div>
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

        // Form validation and submission
        document.getElementById('editOrderForm').addEventListener('submit', function(e) {
            const saveBtn = document.getElementById('saveBtn');
            const originalText = saveBtn.innerHTML;
            
            // Validate form
            const totalPrice = document.querySelector('input[name="total_price"]').value;
            const orderDate = document.querySelector('input[name="order_date"]').value;
            
            if (!totalPrice || totalPrice <= 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาด',
                    text: 'กรุณากรอกราคารวมที่ถูกต้อง'
                });
                return;
            }
            
            if (!orderDate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาด',
                    text: 'กรุณาเลือกวันที่และเวลา'
                });
                return;
            }
            
            // Show loading state
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="loading"></span> กำลังบันทึก...';
            
            // Re-enable button after delay (in case of errors)
            setTimeout(() => {
                if (saveBtn.disabled) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;
                }
            }, 10000);
        });

        // Reset form function
        function resetForm() {
            Swal.fire({
                title: 'ยืนยันการรีเซ็ต',
                text: 'คุณต้องการยกเลิกการเปลี่ยนแปลงทั้งหมดหรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e17055',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'รีเซ็ต',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('editOrderForm').reset();
                    // Reset to original values
                    document.querySelector('input[name="order_date"]').value = '<?php echo date('Y-m-d\TH:i', strtotime($order['order_date'])); ?>';
                    document.querySelector('input[name="total_price"]').value = '<?php echo $order['total_price']; ?>';
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'รีเซ็ตแล้ว',
                        text: 'ฟอร์มได้ถูกรีเซ็ตเป็นค่าเดิมแล้ว',
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            });
        }

        // Print order function
        function printOrder() {
            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>คำสั่งซื้อ #<?php echo $order_id; ?></title>
                    <meta charset="UTF-8">
                    <style>
                        body { font-family: 'Sarabun', sans-serif; margin: 20px; line-height: 1.6; }
                        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                        .header h1 { color: #333; margin-bottom: 5px; }
                        .info-section { margin-bottom: 20px; }
                        .info-section h3 { color: #666; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
                        .info-row { margin-bottom: 10px; }
                        .info-row strong { color: #333; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                        th { background-color: #f5f5f5; font-weight: bold; }
                        .total { font-size: 1.2em; font-weight: bold; color: #28a745; }
                        .footer { margin-top: 30px; text-align: center; font-size: 0.9em; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>คำสั่งซื้อ #<?php echo $order_id; ?></h1>
                        <p>วันที่พิมพ์: ${new Date().toLocaleDateString('th-TH')} ${new Date().toLocaleTimeString('th-TH')}</p>
                    </div>
                    
                    <div class="info-section">
                        <h3>ข้อมูลคำสั่งซื้อ</h3>
                        <div class="info-row"><strong>หมายเลขคำสั่งซื้อ:</strong> #<?php echo $order_id; ?></div>
                        <div class="info-row"><strong>วันที่สั่งซื้อ:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></div>
                        <div class="info-row"><strong>ยอดรวม:</strong> <span class="total">฿<?php echo number_format($order['total_price'], 2); ?></span></div>
                    </div>
                    
                    <div class="info-section">
                        <h3>ข้อมูลลูกค้า</h3>
                        <div class="info-row"><strong>ชื่อ:</strong> <?php echo htmlspecialchars($order['name']); ?></div>
                        <div class="info-row"><strong>ชื่อผู้ใช้:</strong> @<?php echo htmlspecialchars($order['username']); ?></div>
                        <div class="info-row"><strong>อีเมล:</strong> <?php echo htmlspecialchars($order['email']); ?></div>
                    </div>
                    
                    <div class="info-section">
                        <h3>หมายเหตุ</h3>
                        <p>รายละเอียดสินค้าจะต้องดูจากระบบจัดการสินค้าแยกต่างหาก</p>
                    </div>
                    
                    <div class="footer">
                        <p>ระบบจัดการคำสั่งซื้อ - Admin Panel</p>
                    </div>
                </body>
                </html>
            `;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(printContent);
            printWindow.document.close();
            printWindow.print();
        }

        // Confirm delete function
        function confirmDelete() {
            Swal.fire({
                title: 'ยืนยันการลบคำสั่งซื้อ',
                html: `คุณต้องการลบคำสั่งซื้อ <strong>#<?php echo $order_id; ?></strong><br>ของลูกค้า <strong>"<?php echo addslashes($order['name']); ?>"</strong> หรือไม่?<br><small class="text-muted">การดำเนินการนี้ไม่สามารถย้อนกลับได้</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e17055',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-1"></i>ลบคำสั่งซื้อ',
                cancelButtonText: '<i class="fas fa-times me-1"></i>ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังลบคำสั่งซื้อ...',
                        html: '<div class="loading mx-auto"></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });

                    setTimeout(() => {
                        window.location.href = 'adminDeleteOrder.php?order_id=<?php echo $order_id; ?>';
                    }, 1000);
                }
            });
        }

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

        // Price validation
        document.getElementById('totalPriceInput').addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value <= 0 || isNaN(value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S for save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('editOrderForm').submit();
            }
            
            // Ctrl/Cmd + P for print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                printOrder();
            }

            // Escape for cancel/back
            if (e.key === 'Escape') {
                window.location.href = 'adminOrders.php';
            }
        });

        // Success/Error notifications from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('success')) {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: decodeURIComponent(urlParams.get('success')),
                confirmButtonColor: '#00b894',
                timer: 3000,
                timerProgressBar: true
            });
        }

        if (urlParams.get('error')) {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: decodeURIComponent(urlParams.get('error')),
                confirmButtonColor: '#e17055'
            });
        }

        // Add page transition effects for navigation
        document.querySelectorAll('.nav-link-custom').forEach(link => {
            if (link.getAttribute('href') !== '#' && !link.getAttribute('href').includes('logout')) {
                link.addEventListener('click', function(e) {
                    if (this.classList.contains('active')) return;
                    
                    e.preventDefault();
                    
                    // Create loading overlay
                    const overlay = document.createElement('div');
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(102, 126, 234, 0.9);
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
    </script>
</body>
</html>

<?php $conn->close(); ?>