<?php
// adminOrders.php
include "check_session.php";
include "conn.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: show_profile.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

// การค้นหาและตัวกรอง
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// SQL ดึงรายการคำสั่งซื้อ
$sql = "SELECT o.order_id, o.order_date, o.total_price, c.username, c.name 
        FROM orders o
        JOIN customer c ON o.customer_id = c.id
        WHERE 1";

if ($search !== '') {
    $sql .= " AND (c.username LIKE '%$search%' OR c.name LIKE '%$search%' OR o.order_id LIKE '%$search%')";
}

if ($filter === 'today') {
    $sql .= " AND DATE(o.order_date) = CURDATE()";
} elseif ($filter === 'week') {
    $sql .= " AND YEARWEEK(o.order_date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'month') {
    $sql .= " AND MONTH(o.order_date) = MONTH(CURDATE()) AND YEAR(o.order_date) = YEAR(CURDATE())";
}

$sql .= " ORDER BY o.order_date DESC";
$result = $conn->query($sql);

// สถิติ
$total_orders_result = $conn->query("SELECT COUNT(*) as total FROM orders");
$total_orders = $total_orders_result->fetch_assoc()['total'];

$today_orders_result = $conn->query("SELECT COUNT(*) as today FROM orders WHERE DATE(order_date) = CURDATE()");
$today_orders = $today_orders_result->fetch_assoc()['today'];

$total_revenue_result = $conn->query("SELECT SUM(total_price) as revenue FROM orders");
$total_revenue = $total_revenue_result->fetch_assoc()['revenue'] ?: 0;

$pending_orders_result = $conn->query("SELECT COUNT(*) as pending FROM orders o JOIN customer c ON o.customer_id = c.id WHERE 1"); // Assume all orders are pending for demo
$pending_orders = $pending_orders_result->fetch_assoc()['pending'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำสั่งซื้อ | แดชบอร์ดแอดมิน</title>
    
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

        /* Control Panel */
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

        /* Filter Select */
        .form-select {
            border-radius: var(--border-radius);
            border: 2px solid #e9ecef;
            padding: 1rem;
            transition: var(--transition);
            font-weight: 500;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
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

        .btn-warning-custom {
            background: var(--gradient-warning);
            color: white;
            box-shadow: 0 4px 15px rgba(253, 203, 110, 0.4);
        }

        .btn-warning-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(253, 203, 110, 0.6);
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

        /* Orders Table */
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

        .order-id {
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .order-id:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .order-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pending {
            background: var(--gradient-warning);
            color: white;
        }

        .badge-completed {
            background: var(--gradient-success);
            color: white;
        }

        .badge-cancelled {
            background: var(--gradient-danger);
            color: white;
        }

        .customer-info h6 {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .customer-info small {
            color: #6c757d;
            font-weight: 500;
        }

        .price-tag {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--success-color);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-custom { padding: 1rem; }
            .page-header { padding: 3rem 0 2rem; }
            .page-title { font-size: 2rem; }
            .main-content { padding: 0 1rem 2rem; }
            .control-panel { padding: 1.5rem; }
            
            .search-container {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h4 {
            margin-bottom: 1rem;
            color: var(--dark-color);
        }

        .empty-state p {
            font-size: 1.1rem;
            max-width: 400px;
            margin: 0 auto;
        }

        /* Date Display */
        .date-display {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .quick-actions h6 {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* Tooltips */
        .tooltip-inner {
            background: var(--dark-color);
            color: white;
            font-weight: 500;
            border-radius: 8px;
        }

        .tooltip.bs-tooltip-top .tooltip-arrow::before {
            border-top-color: var(--dark-color);
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
                <i class="fas fa-shopping-cart me-2"></i>
                จัดการคำสั่งซื้อ
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
                <i class="fas fa-shopping-cart me-3"></i>
                จัดการคำสั่งซื้อ
            </h1>
            <p class="page-subtitle" data-aos="fade-up" data-aos-delay="200">
                ดู จัดการ และติดตามคำสั่งซื้อทั้งหมดของลูกค้า
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Stats Cards -->
            <div class="row stats-row" data-aos="fade-up" data-aos-delay="100">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-number"><?php echo $total_orders; ?></div>
                        <div class="stat-label">คำสั่งซื้อทั้งหมด</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo $today_orders; ?></div>
                        <div class="stat-label">วันนี้</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-baht-sign"></i>
                        </div>
                        <div class="stat-number">฿<?php echo number_format($total_revenue, 0); ?></div>
                        <div class="stat-label">รายได้รวม</div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-number" id="visibleOrders"><?php echo $result->num_rows; ?></div>
                        <div class="stat-label">แสดงผล</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions" data-aos="fade-up" data-aos-delay="200">
                <h6><i class="fas fa-bolt me-2 text-warning"></i>การดำเนินการด่วน</h6>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-info-custom btn-custom btn-sm" onclick="exportOrders()" title="ส่งออกข้อมูล">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </button>
                    <button class="btn btn-success-custom btn-custom btn-sm" onclick="refreshTable()" title="รีเฟรชข้อมูล">
                        <i class="fas fa-sync-alt me-1"></i>รีเฟรช
                    </button>
                    <button class="btn btn-warning-custom btn-custom btn-sm" onclick="printReport()" title="พิมพ์รายงาน">
                        <i class="fas fa-print me-1"></i>พิมพ์
                    </button>
                </div>
            </div>

            <!-- Control Panel -->
            <div class="control-panel" data-aos="fade-up" data-aos-delay="300">
                <form class="row align-items-center" method="GET" action="">
                    <div class="col-lg-5 mb-2">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   name="search" 
                                   class="search-input" 
                                   id="searchInput"
                                   placeholder="ค้นหาคำสั่งซื้อ... (หมายเลข, ลูกค้า)"
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   onkeyup="searchOrders()">
                        </div>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <select name="filter" class="form-select" onchange="this.form.submit()">
                            <option value="">-- ตัวกรองเวลา --</option>
                            <option value="today" <?php if($filter=='today') echo 'selected'; ?>>
                                <i class="fas fa-calendar-day"></i> วันนี้
                            </option>
                            <option value="week" <?php if($filter=='week') echo 'selected'; ?>>
                                <i class="fas fa-calendar-week"></i> สัปดาห์นี้
                            </option>
                            <option value="month" <?php if($filter=='month') echo 'selected'; ?>>
                                <i class="fas fa-calendar-alt"></i> เดือนนี้
                            </option>
                        </select>
                    </div>
                    <div class="col-lg-2 mb-2">
                        <button type="submit" class="btn btn-primary-custom btn-custom w-100">
                            <i class="fas fa-search me-1"></i>ค้นหา
                        </button>
                    </div>
                    <div class="col-lg-2 mb-2">
                        <a href="adminOrders.php" class="btn btn-secondary btn-custom w-100">
                            <i class="fas fa-undo me-1"></i>ล้างค่า
                        </a>
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            <?php if ($result->num_rows > 0): ?>
                <div class="table-container" data-aos="fade-up" data-aos-delay="400">
                    <table class="table table-custom" id="ordersTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>หมายเลขคำสั่งซื้อ</th>
                                <th><i class="fas fa-user me-2"></i>ลูกค้า</th>
                                <th><i class="fas fa-calendar me-2"></i>วันที่สั่งซื้อ</th>
                                <th><i class="fas fa-baht-sign me-2"></i>ราคารวม</th>
                                <th><i class="fas fa-cogs me-2"></i>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="adminViewOrder.php?order_id=<?php echo $row['order_id']; ?>" 
                                           class="order-id">
                                            <i class="fas fa-receipt me-2"></i>#<?php echo $row['order_id']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="customer-info">
                                            <h6><?php echo htmlspecialchars($row['name']); ?></h6>
                                            <small><i class="fas fa-user me-1"></i>@<?php echo htmlspecialchars($row['username']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-display">
                                            <i class="fas fa-calendar-alt me-2 text-info"></i>
                                            <?php 
                                            $date = new DateTime($row['order_date']);
                                            echo $date->format('d/m/Y H:i');
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="price-tag">
                                            <i class="fas fa-baht-sign me-1"></i><?php echo number_format($row['total_price'], 2); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="adminViewOrder.php?order_id=<?php echo $row['order_id']; ?>" 
                                               class="btn btn-info-custom btn-custom btn-sm-custom" 
                                               title="ดูรายละเอียด"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="adminEditOrder.php?order_id=<?php echo $row['order_id']; ?>" 
                                               class="btn btn-warning-custom btn-custom btn-sm-custom" 
                                               title="แก้ไข"
                                               data-bs-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="confirmDeleteOrder(<?php echo $row['order_id']; ?>, '<?php echo addslashes($row['name']); ?>')" 
                                                    class="btn btn-danger-custom btn-custom btn-sm-custom" 
                                                    title="ลบคำสั่งซื้อ"
                                                    data-bs-toggle="tooltip">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4" data-aos="fade-up" data-aos-delay="500">
                    <a href="admin_profile.php" class="btn btn-secondary btn-custom">
                        <i class="fas fa-arrow-left me-2"></i>กลับไปโปรไฟล์แอดมิน
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container" data-aos="fade-up" data-aos-delay="400">
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h4>ไม่พบคำสั่งซื้อ</h4>
                        <p>ไม่มีคำสั่งซื้อที่ตรงกับเงื่อนไขการค้นหาของคุณ ลองปรับเงื่อนไขการค้นหาหรือตัวกรองใหม่</p>
                        <div class="mt-3">
                            <a href="adminOrders.php" class="btn btn-primary-custom btn-custom">
                                <i class="fas fa-refresh me-2"></i>ดูทั้งหมด
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Set initial visible count
            updateVisibleCount();
        });

        // Search orders function
        function searchOrders() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('ordersTable');
            
            if (!table) return;
            
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = tbody.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const orderIdCell = rows[i].cells[0];
                const customerCell = rows[i].cells[1];
                
                const orderId = orderIdCell.textContent.toLowerCase();
                const customerName = customerCell.textContent.toLowerCase();

                if (orderId.includes(input) || customerName.includes(input)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }

            updateVisibleCount();
        }

        // Update visible count
        function updateVisibleCount() {
            const table = document.getElementById('ordersTable');
            if (!table) return;
            
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = tbody.getElementsByTagName('tr');
            let visibleCount = 0;

            for (let i = 0; i < rows.length; i++) {
                if (rows[i].style.display !== 'none') {
                    visibleCount++;
                }
            }

            const visibleElement = document.getElementById('visibleOrders');
            if (visibleElement) {
                visibleElement.textContent = visibleCount;
            }
        }

        // Confirm delete order function
        function confirmDeleteOrder(orderId, customerName) {
            Swal.fire({
                title: 'ยืนยันการลบคำสั่งซื้อ',
                html: `คุณต้องการลบคำสั่งซื้อ <strong>#${orderId}</strong><br>ของลูกค้า <strong>"${customerName}"</strong> หรือไม่?<br><small class="text-muted">การดำเนินการนี้ไม่สามารถย้อนกลับได้</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e17055',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-1"></i>ลบคำสั่งซื้อ',
                cancelButtonText: '<i class="fas fa-times me-1"></i>ยกเลิก',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-lg',
                    confirmButton: 'rounded-pill',
                    cancelButton: 'rounded-pill'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'กำลังลบคำสั่งซื้อ...',
                        html: '<div class="loading mx-auto"></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });

                    // Redirect to delete script
                    setTimeout(() => {
                        window.location.href = 'adminDeleteOrder.php?order_id=' + orderId;
                    }, 1000);
                }
            });
        }

        // Export orders to CSV
        function exportOrders() {
            const table = document.getElementById('ordersTable');
            if (!table) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่พบข้อมูล',
                    text: 'ไม่มีข้อมูลคำสั่งซื้อให้ส่งออก'
                });
                return;
            }

            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = tbody.getElementsByTagName('tr');
            let csvContent = '\ufeff'; // UTF-8 BOM for proper Thai character support
            
            // Headers
            csvContent += 'หมายเลขคำสั่งซื้อ,ลูกค้า,ชื่อผู้ใช้,วันที่สั่งซื้อ,ราคารวม\n';
            
            // Data rows
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].style.display === 'none') continue;
                
                const cells = rows[i].cells;
                const orderId = cells[0].textContent.replace('#', '').trim();
                const customerInfo = cells[1].querySelector('.customer-info');
                const customerName = customerInfo.querySelector('h6').textContent;
                const username = customerInfo.querySelector('small').textContent.replace('@', '');
                const orderDate = cells[2].textContent.trim();
                const totalPrice = cells[3].textContent.replace('฿', '').trim();
                
                csvContent += `"${orderId}","${customerName}","${username}","${orderDate}","${totalPrice}"\n`;
            }
            
            // Download
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `orders_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show success message
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

        // Print report function
        function printReport() {
            const table = document.getElementById('ordersTable');
            if (!table) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่พบข้อมูล',
                    text: 'ไม่มีข้อมูลคำสั่งซื้อให้พิมพ์'
                });
                return;
            }

            // Create print window
            const printWindow = window.open('', '_blank');
            const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>รายงานคำสั่งซื้อ</title>
                    <meta charset="UTF-8">
                    <style>
                        body { font-family: 'Sarabun', sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .header h1 { color: #333; margin-bottom: 10px; }
                        .header p { color: #666; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                        th { background-color: #f5f5f5; font-weight: bold; }
                        .text-center { text-align: center; }
                        .date { font-size: 0.9em; color: #666; }
                        @media print { .no-print { display: none; } }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>รายงานคำสั่งซื้อ</h1>
                        <p>วันที่พิมพ์: ${new Date().toLocaleDateString('th-TH')} ${new Date().toLocaleTimeString('th-TH')}</p>
                    </div>
                    ${table.outerHTML.replace(/class="[^"]*"/g, '').replace(/<button[^>]*>.*?<\/button>/g, '')}
                    <div class="no-print" style="margin-top: 20px; text-align: center;">
                        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">พิมพ์</button>
                        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">ปิด</button>
                    </div>
                </body>
                </html>
            `;
            
            printWindow.document.write(printContent);
            printWindow.document.close();
        }

        // Enhanced table interactions
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('.table-custom tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.zIndex = '10';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.zIndex = '1';
                });
            });
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F for search focus
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // Escape to clear search
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('searchInput');
                if (searchInput && searchInput.value) {
                    searchInput.value = '';
                    searchOrders();
                }
            }

            // Ctrl/Cmd + P for print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                printReport();
            }

            // Ctrl/Cmd + E for export
            if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
                e.preventDefault();
                exportOrders();
            }
        });

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