<?php
// product_list.php
// หน้ารายการสินค้า (สำหรับแอดมิน)
include "check_session.php";
include "conn.php";

if (!$_SESSION['is_admin']) {
    header("Location: login_form.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

// ดึงข้อมูลสินค้าจากตาราง product
$sql = "SELECT productID, product_name, origin, price, details, image FROM product";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า | แดชบอร์ดแอดมิน</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1.5rem;
            box-shadow: var(--shadow-soft);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-custom .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
        }

        .navbar-nav {
            gap: 0.5rem;
            align-items: center;
        }

        .nav-link-custom {
            color: var(--dark-color) !important;
            font-weight: 600;
            padding: 0.75rem 1.25rem !important;
            border-radius: var(--border-radius);
            transition: var(--transition);
            text-decoration: none;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 120px;
            justify-content: center;
            border: 2px solid transparent;
        }

        .nav-link-custom:hover,
        .nav-link-custom.active {
            background: var(--gradient-primary);
            color: white !important;
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }

        .nav-link-custom i {
            font-size: 1.1rem;
        }

        .nav-link-custom span {
            font-size: 0.95rem;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
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

        /* Buttons */
        .btn-custom {
            border: none;
            border-radius: var(--border-radius);
            padding: 0.75rem 1.5rem;
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
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        /* Product Table */
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

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: var(--transition);
        }

        .product-image:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
        }

        .product-name {
            font-weight: 700;
            color: var(--dark-color);
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }

        .product-id {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .price-badge {
            background: var(--gradient-success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(0, 184, 148, 0.3);
        }

        .origin-badge {
            background: #e9ecef;
            color: var(--dark-color);
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .details-text {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #6c757d;
            font-size: 0.95rem;
        }

        .details-text:hover {
            white-space: normal;
            overflow: visible;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
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

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        .empty-state h3 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        /* Loading Animation */
        .loading-spinner {
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .page-title { font-size: 2.5rem; }
            .nav-link-custom { min-width: 100px; }
        }

        @media (max-width: 992px) {
            .navbar-nav {
                padding: 1rem;
                background: white;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-soft);
                margin-top: 0.5rem;
            }
            .nav-link-custom {
                margin: 0.25rem 0;
                width: 100%;
                justify-content: flex-start;
            }
        }

        @media (max-width: 768px) {
            .navbar-custom { padding: 0.5rem 1rem; }
            .page-header { padding: 3rem 0 2rem; }
            .page-title { font-size: 2rem; }
            .main-content { padding: 0 1rem 2rem; }
            .control-panel { padding: 1.5rem; }
            .search-container { margin-right: 0; margin-bottom: 1rem; }
            .table-container { overflow-x: auto; }
            .action-buttons { flex-direction: column; }
            .btn-custom { width: 100%; justify-content: center; }
        }

        @media (max-width: 576px) {
            .page-title { font-size: 1.75rem; }
            .stat-card { padding: 1.5rem; }
            .stat-number { font-size: 2rem; }
            .table-custom thead th { padding: 1rem 0.5rem; font-size: 0.8rem; }
            .table-custom tbody td { padding: 1rem 0.5rem; }
            .product-image { width: 60px; height: 60px; }
            .navbar-brand { font-size: 1.25rem; }
        }

        /* Filter Tabs */
        .filter-tabs {
            margin-bottom: 2rem;
        }

        .filter-tab {
            background: transparent;
            border: 2px solid #e9ecef;
            color: #6c757d;
            border-radius: var(--border-radius);
            padding: 0.75rem 1.5rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            transition: var(--transition);
            cursor: pointer;
            font-weight: 600;
        }

        .filter-tab.active,
        .filter-tab:hover {
            background: var(--gradient-primary);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }
    </style>
</head>
<body>
    <div class="bg-animated"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-cog me-2"></i>แดชบอร์ดแอดมิน
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="admin_profile.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>โปรไฟล์แอดมิน</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom active" href="product_list.php">
                            <i class="fas fa-box"></i>
                            <span>จัดการสินค้า</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="adminOrders.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>คำสั่งซื้อ</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="addProduct_form.php">
                            <i class="fas fa-plus-circle"></i>
                            <span>เพิ่มสินค้า</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>ออกจากระบบ</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1 class="page-title" data-aos="fade-up">จัดการสินค้า</h1>
            <p class="page-subtitle" data-aos="fade-up" data-aos-delay="100">
                ดูและจัดการรายการสินค้าทั้งหมดในระบบ
            </p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Stats Cards -->
            <div class="row stats-row">
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="stat-card" data-aos="fade-up">
                        <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                        <div class="stat-number"><?php echo $result->num_rows; ?></div>
                        <div class="stat-label">สินค้าทั้งหมด</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="stat-icon"><i class="fas fa-eye"></i></div>
                        <div class="stat-number" id="visibleProducts"><?php echo $result->num_rows; ?></div>
                        <div class="stat-label">สินค้าที่แสดง</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="stat-icon"><i class="fas fa-search"></i></div>
                        <div class="stat-number" id="searchResults">0</div>
                        <div class="stat-label">ผลการค้นหา</div>
                    </div>
                </div>
            </div>

            <!-- Control Panel -->
            <div class="control-panel" data-aos="fade-up">
                <div class="d-flex flex-wrap align-items-center mb-3">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchInput" class="search-input" 
                               placeholder="ค้นหาสินค้า..." oninput="searchProducts()">
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <button class="btn btn-primary-custom btn-custom" onclick="refreshTable()">
                            <i class="fas fa-sync me-2"></i>รีเฟรช
                        </button>
                        <a href="addProduct_form.php" class="btn btn-success-custom btn-custom">
                            <i class="fas fa-plus me-2"></i>เพิ่มสินค้า
                        </a>
                        <button class="btn btn-info-custom btn-custom" onclick="exportData()">
                            <i class="fas fa-download me-2"></i>ส่งออก
                        </button>
                    </div>
                </div>
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterProducts('all')">ทั้งหมด</button>
                    <?php
                    // ดึงแหล่งที่มาที่ไม่ซ้ำกัน
                    $origin_sql = "SELECT DISTINCT origin FROM product WHERE origin IS NOT NULL AND origin != ''";
                    $origin_result = $conn->query($origin_sql);
                    while ($origin_row = $origin_result->fetch_assoc()):
                    ?>
                        <button class="filter-tab" 
                                onclick="filterProducts('<?php echo htmlspecialchars($origin_row['origin']); ?>')">
                            <?php echo htmlspecialchars($origin_row['origin']); ?>
                        </button>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Product Table -->
            <div class="table-container" data-aos="fade-up" data-aos-delay="100">
                <table id="productTable" class="table table-custom">
                    <thead>
                        <tr>
                            <th>รหัสสินค้า</th>
                            <th>รูปภาพ</th>
                            <th>ชื่อสินค้า</th>
                            <th>แหล่งที่มา</th>
                            <th>ราคา</th>
                            <th>รายละเอียด</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php
                                $image_path = !empty($row['image']) && file_exists("gallery_products/" . $row['image'])
                                    ? "gallery_products/" . htmlspecialchars($row['image'])
                                    : "gallery_products/default.png";
                                ?>
                                <tr data-origin="<?php echo htmlspecialchars($row['origin']); ?>">
                                    <td>
                                        <div class="product-id">#<?php echo htmlspecialchars($row['productID']); ?></div>
                                    </td>
                                    <td>
                                        <img src="<?php echo $image_path; ?>" 
                                             alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                             class="product-image"
                                             onerror="this.src='gallery_products/default.png'">
                                    </td>
                                    <td>
                                        <div class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></div>
                                    </td>
                                    <td>
                                        <span class="origin-badge"><?php echo htmlspecialchars($row['origin']); ?></span>
                                    </td>
                                    <td>
                                        <span class="price-badge">฿<?php echo number_format($row['price'], 2); ?></span>
                                    </td>
                                    <td>
                                        <div class="details-text tooltip-custom" 
                                             data-tooltip="<?php echo htmlspecialchars($row['details'] ?? ''); ?>">
                                            <?php echo htmlspecialchars(substr($row['details'] ?? '', 0, 50)) . (strlen($row['details'] ?? '') > 50 ? '...' : ''); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_product_form.php?productID=<?php echo urlencode($row['productID']); ?>" 
                                               class="btn btn-info-custom btn-custom btn-sm-custom tooltip-custom"
                                               data-tooltip="แก้ไขสินค้า">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="viewProduct('<?php echo addslashes($row['productID']); ?>')" 
                                                    class="btn btn-success-custom btn-custom btn-sm-custom tooltip-custom"
                                                    data-tooltip="ดูรายละเอียด">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="deleteProduct('<?php echo addslashes($row['productID']); ?>', '<?php echo addslashes(htmlspecialchars($row['product_name'])); ?>')" 
                                                    class="btn btn-danger-custom btn-custom btn-sm-custom tooltip-custom"
                                                    data-tooltip="ลบสินค้า">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-box-open"></i>
                                        </div>
                                        <h3>ยังไม่มีสินค้า</h3>
                                        <p>เริ่มต้นเพิ่มสินค้าใหม่เพื่อแสดงในระบบ</p>
                                        <a href="addProduct_form.php" class="btn btn-primary-custom btn-custom">
                                            <i class="fas fa-plus me-2"></i>เพิ่มสินค้าแรก
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Product Detail Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--gradient-primary); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>รายละเอียดสินค้า
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                </div>
                <div class="modal-body" id="productModalBody">
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

        // Base URL for AJAX calls
        const baseUrl = 'http://localhost/2567/GitHub_OTEROBOT/e-commerce';

        // Search functionality
        function searchProducts() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const table = document.getElementById('productTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let visibleCount = 0;

            for (let i = 0; i < rows.length; i++) {
                if (rows[i].cells.length < 7) continue; // Skip empty state row

                const productID = rows[i].cells[0].textContent.toLowerCase();
                const productName = rows[i].cells[2].textContent.toLowerCase();
                const origin = rows[i].cells[3].textContent.toLowerCase();
                const price = rows[i].cells[4].textContent.toLowerCase();
                const details = rows[i].cells[5].textContent.toLowerCase();

                if (productID.includes(input) ||
                    productName.includes(input) ||
                    origin.includes(input) ||
                    price.includes(input) ||
                    details.includes(input)) {
                    rows[i].style.display = '';
                    visibleCount++;
                    if (input.length > 0) {
                        rows[i].style.animation = 'highlight 0.5s ease';
                    }
                } else {
                    rows[i].style.display = 'none';
                }
            }

            document.getElementById('searchResults').textContent = input.length > 0 ? visibleCount : 0;
            document.getElementById('visibleProducts').textContent = visibleCount;

            if (visibleCount === 0 && input.length > 0) {
                showSearchEmptyState();
            } else {
                hideSearchEmptyState();
            }
        }

        // Filter by origin
        function filterProducts(origin) {
            const table = document.getElementById('productTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            const tabs = document.querySelectorAll('.filter-tab');
            let visibleCount = 0;

            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');

            for (let i = 0; i < rows.length; i++) {
                if (rows[i].cells.length < 7) continue; // Skip empty state row

                const rowOrigin = rows[i].getAttribute('data-origin');
                
                if (origin === 'all' || rowOrigin === origin) {
                    rows[i].style.display = '';
                    visibleCount++;
                } else {
                    rows[i].style.display = 'none';
                }
            }

            document.getElementById('visibleProducts').textContent = visibleCount;
            document.getElementById('searchInput').value = '';
            document.getElementById('searchResults').textContent = 0;
        }

        // View product details
        function viewProduct(productID) {
            console.log('Viewing product:', productID); // Debug
            const modalBody = document.getElementById('productModalBody');
            modalBody.innerHTML = `
                <div class="text-center py-4">
                    <div class="loading-spinner mx-auto mb-3"></div>
                    <p>กำลังโหลดข้อมูล...</p>
                </div>
            `;

            const modal = new bootstrap.Modal(document.getElementById('productModal'));
            modal.show();

            fetch(`${baseUrl}/view_product.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `productID=${encodeURIComponent(productID)}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    modalBody.innerHTML = `
                        <div class="alert alert-danger-custom alert-custom">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            ${data.error}
                        </div>
                    `;
                } else {
                    modalBody.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <img src="${data.image}" class="img-fluid rounded shadow" alt="${data.product_name}">
                            </div>
                            <div class="col-md-6">
                                <h4 class="mb-3" style="color: var(--primary-color);">${data.product_name}</h4>
                                <div class="mb-3">
                                    <strong><i class="fas fa-hashtag me-2"></i>รหัสสินค้า:</strong> ${data.productID}
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-map-marker-alt me-2"></i>แหล่งที่มา:</strong> 
                                    <span class="origin-badge">${data.origin}</span>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-dollar-sign me-2"></i>ราคา:</strong> 
                                    <span class="price-badge">฿${data.price}</span>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-info-circle me-2"></i>รายละเอียด:</strong>
                                    <p class="mt-2 text-muted">${data.details}</p>
                                </div>
                                <a href="${baseUrl}/edit_product_form.php?productID=${data.productID}" 
                                   class="btn btn-info-custom btn-custom btn-sm-custom">
                                    <i class="fas fa-edit"></i> แก้ไข
                                </a>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `
                    <div class="alert alert-danger-custom alert-custom">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        เกิดข้อผิดพลาดในการโหลดข้อมูล: ${error.message}
                    </div>
                `;
            });
        }

        // Delete product with confirmation
        function deleteProduct(productID, productName) {
            console.log('Deleting product:', productID, productName); // Debug
            Swal.fire({
                title: 'ยืนยันการลบสินค้า',
                html: `คุณต้องการลบสินค้า <strong>"${productName}"</strong> หรือไม่?<br><small class="text-muted">การดำเนินการนี้ไม่สามารถย้อนกลับได้</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e17055',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-1"></i>ลบสินค้า',
                cancelButtonText: '<i class="fas fa-times me-1"></i>ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังลบสินค้า...',
                        html: '<div class="loading-spinner mx-auto"></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });

                    fetch(`${baseUrl}/delete_product.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `productID=${encodeURIComponent(productID)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'ลบสินค้าสำเร็จ',
                                text: data.message,
                                timer: 2000,
                                timerProgressBar: true
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้: ' + error.message
                        });
                    });
                }
            });
        }

        // Export data to CSV
        function exportData() {
            const table = document.getElementById('productTable');
            const rows = table.querySelectorAll('tr');
            let csvContent = '\ufeff'; // UTF-8 BOM for Thai character support
            
            csvContent += 'รหัสสินค้า,ชื่อสินค้า,แหล่งที่มา,ราคา,รายละเอียด\n';
            
            for (let i = 1; i < rows.length; i++) {
                if (rows[i].cells.length < 7 || rows[i].style.display === 'none') continue;
                
                const cols = rows[i].cells;
                const productID = cols[0].textContent.replace('#', '');
                const productName = cols[2].textContent;
                const origin = cols[3].textContent;
                const price = cols[4].textContent.replace('฿', '').replace(',', '');
                const details = cols[5].getAttribute('data-tooltip') || cols[5].textContent;
                
                csvContent += `"${productID}","${productName}","${origin}","${price}","${details}"\n`;
            }
            
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `products_${new Date().toISOString().split('T')[0]}.csv`);
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
            refreshBtn.innerHTML = '<span class="loading-spinner"></span> รีเฟรช...';
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // Show search empty state
        function showSearchEmptyState() {
            const tbody = document.querySelector('#productTable tbody');
            const existingEmptyState = document.querySelector('.search-empty-state');
            
            if (!existingEmptyState) {
                const emptyRow = document.createElement('tr');
                emptyRow.className = 'search-empty-state';
                emptyRow.innerHTML = `
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h3>ไม่พบสินค้าที่ค้นหา</h3>
                            <p>ลองใช้คำค้นหาอื่น หรือตรวจสอบการสะกดคำ</p>
                        </div>
                    </td>
                `;
                tbody.appendChild(emptyRow);
            }
        }

        // Hide search empty state
        function hideSearchEmptyState() {
            const searchEmptyState = document.querySelector('.search-empty-state');
            if (searchEmptyState) {
                searchEmptyState.remove();
            }
        }

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
                    searchProducts();
                }
            }
        });

        // Enhanced table interactions
        document.querySelectorAll('.table-custom tbody tr').forEach(row => {
            if (row.cells.length >= 7) {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02)';
                    this.style.zIndex = '10';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                    this.style.zIndex = '1';
                });
            }
        });

        // Add CSS for highlight animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes highlight {
                0% { background-color: rgba(102, 126, 234, 0.1); }
                100% { background-color: transparent; }
            }
            
            .search-empty-state {
                animation: fadeIn 0.5s ease;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);

        // Initialize tooltips and animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            const totalProducts = document.querySelectorAll('#productTable tbody tr').length;
            const emptyStateRows = document.querySelectorAll('#productTable tbody tr td[colspan="7"]').length;
            const actualProducts = totalProducts - emptyStateRows;
            
            document.getElementById('visibleProducts').textContent = actualProducts;
            
            document.querySelectorAll('.nav-link-custom').forEach(link => {
                if (link.getAttribute('href') !== '#' && !link.getAttribute('href').includes('logout')) {
                    link.addEventListener('click', function(e) {
                        if (this.classList.contains('active')) return;
                        
                        e.preventDefault();
                        
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
                                <div class="loading-spinner" style="width: 40px; height: 40px; margin: 0 auto 20px;"></div>
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
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>