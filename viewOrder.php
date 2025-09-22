<?php
// viewOrder.php
// แสดงรายละเอียดคำสั่งซื้อเฉพาะรายการของลูกค้าที่ล็อกอินอยู่ในถานะAdmin
include "check_session.php";
include "conn.php";

// ตรวจสอบว่ามี order_id ส่งมาหรือไม่
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "❌ ไม่พบหมายเลขคำสั่งซื้อ";
    exit;
}

$order_id = intval($_GET['order_id']);

// ------------------ ดึงข้อมูล order ------------------
$sqlOrder = "SELECT * FROM orders WHERE order_id = ?";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("i", $order_id);
$stmtOrder->execute();
$resultOrder = $stmtOrder->get_result();

if ($resultOrder->num_rows == 0) {
    echo "ไม่พบข้อมูลคำสั่งซื้อ";
    exit;
}

$order = $resultOrder->fetch_assoc();

// ------------------ ดึงรายละเอียดสินค้า ------------------
$sqlDetails = "SELECT * FROM order_details WHERE order_id = ?";
$stmtDetail = $conn->prepare($sqlDetails);
$stmtDetail->bind_param("i", $order_id);
$stmtDetail->execute();
$resultDetails = $stmtDetail->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?> - OTEROBOT Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96c93d, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: gradientFlow 25s ease infinite;
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            25% { background-position: 100% 50%; }
            50% { background-position: 100% 100%; }
            75% { background-position: 0% 100%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating Elements Background */
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .floating-shape {
            position: absolute;
            opacity: 0.1;
            animation: floatShape 12s ease-in-out infinite;
        }

        @keyframes floatShape {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(-15px) rotate(240deg); }
        }

        /* Enhanced Navbar */
        .navbar {
            background: linear-gradient(135deg, #4CAF50, #2e7d32, #1b5e20) !important;
            padding: 20px 0;
            box-shadow: 
                0 10px 30px rgba(0,0,0,0.3),
                0 0 0 1px rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            border-bottom: 2px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }

        .navbar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.05), transparent);
            animation: navShimmer 4s ease-in-out infinite;
        }

        @keyframes navShimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .navbar a {
            color: white !important;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            margin: 0 15px;
            padding: 10px 15px;
            border-radius: 10px;
            position: relative;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            z-index: 2;
        }

        .navbar a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.05));
            border-radius: 10px;
            opacity: 0;
            transition: all 0.3s ease;
            transform: scale(0.8);
        }

        .navbar a:hover::before {
            opacity: 1;
            transform: scale(1);
        }

        .navbar a:hover {
            color: #ffeb3b !important;
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        /* Main Container */
        .main-container {
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        /* Hero Section */
        .hero-section {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, #f8f9fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(255,255,255,0.5);
            margin-bottom: 20px;
            animation: titleGlow 3s ease-in-out infinite alternate;
        }

        @keyframes titleGlow {
            from { text-shadow: 0 0 30px rgba(255,255,255,0.5); }
            to { text-shadow: 0 0 50px rgba(255,255,255,0.8); }
        }

        .order-number {
            font-size: 1.5rem;
            color: rgba(255,255,255,0.9);
            font-weight: 600;
            margin-bottom: 10px;
        }

        /* Order Details Container */
        .order-container {
            background: rgba(255,255,255,0.95);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 
                0 25px 50px rgba(0,0,0,0.3),
                0 0 0 1px rgba(255,255,255,0.2);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .order-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(76, 175, 80, 0.05), transparent);
            animation: containerShimmer 6s ease-in-out infinite;
        }

        @keyframes containerShimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .order-container > * {
            position: relative;
            z-index: 2;
        }

        /* Order Info Cards */
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .info-card {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(255,255,255,0.05));
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(76, 175, 80, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }

        .info-card:hover::before {
            left: 100%;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-color: rgba(76, 175, 80, 0.4);
        }

        .info-card h4 {
            color: #2e7d32;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card h4 i {
            font-size: 1.5rem;
            color: #4CAF50;
        }

        .info-card p {
            color: #333;
            font-size: 1.1rem;
            font-weight: 500;
            margin: 10px 0;
            line-height: 1.6;
        }

        .info-highlight {
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            color: white;
            padding: 15px 25px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.2rem;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 10px 25px rgba(76, 175, 80, 0.3);
        }

        /* Section Title */
        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #2e7d32;
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .section-title::before,
        .section-title::after {
            content: '';
            flex: 1;
            height: 3px;
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            border-radius: 3px;
            max-width: 100px;
        }

        .section-title i {
            color: #4CAF50;
            font-size: 3rem;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        /* Enhanced Product Table */
        .products-table-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }

        .table {
            margin: 0;
            border: none;
        }

        .table thead th {
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            color: white;
            border: none;
            padding: 25px 20px;
            font-weight: 700;
            font-size: 1.2rem;
            text-align: center;
            position: relative;
        }

        .table thead th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        }

        .table tbody tr {
            background: white;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: none;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.05), rgba(255,255,255,0.95));
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .table tbody td {
            padding: 25px 20px;
            border: none;
            font-weight: 500;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-size: 1.1rem;
        }

        .product-id {
            font-weight: 700;
            color: #2e7d32;
            font-size: 1.2rem;
        }

        .product-name {
            color: #333;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .product-price,
        .product-total {
            font-weight: 800;
            color: #4CAF50;
            font-size: 1.3rem;
        }

        .product-quantity {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1976d2;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            display: inline-block;
        }

        /* Summary Section */
        .order-summary {
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            color: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            margin: 30px 0;
            box-shadow: 0 15px 35px rgba(76, 175, 80, 0.4);
            position: relative;
            overflow: hidden;
        }

        .order-summary::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: summaryShimmer 3s ease-in-out infinite;
        }

        @keyframes summaryShimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .order-summary > * {
            position: relative;
            z-index: 2;
        }

        .summary-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .summary-amount {
            font-size: 3.5rem;
            font-weight: 900;
            text-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Enhanced Buttons */
        .btn-action {
            padding: 15px 30px;
            border-radius: 15px;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
            margin: 10px;
        }

        .btn-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s ease;
        }

        .btn-action:hover::before {
            left: 100%;
        }

        .btn-back {
            background: linear-gradient(135deg, #90a4ae, #607d8b);
            color: white;
        }

        .btn-back:hover {
            background: linear-gradient(135deg, #607d8b, #455a64);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(96, 125, 139, 0.4);
            color: white;
        }

        .btn-shop {
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            color: white;
        }

        .btn-shop:hover {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(76, 175, 80, 0.4);
            color: white;
        }

        /* Action Buttons Container */
        .action-buttons {
            text-align: center;
            padding: 30px 0;
        }

        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        .loading-overlay.fade-out {
            opacity: 0;
            pointer-events: none;
        }

        .loading-spinner {
            width: 80px;
            height: 80px;
            border: 8px solid #f3f3f3;
            border-top: 8px solid #4CAF50;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Stats Mini Cards */
        .order-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-mini-card {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .stat-mini-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .stat-mini-icon {
            font-size: 2rem;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        .stat-mini-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 5px;
        }

        .stat-mini-label {
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 2rem;
                flex-direction: column;
                gap: 15px;
            }

            .section-title::before,
            .section-title::after {
                display: none;
            }

            .order-container {
                padding: 20px;
                margin: 20px 10px;
            }

            .order-info {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .table thead th,
            .table tbody td {
                padding: 15px 10px;
                font-size: 0.9rem;
            }

            .order-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .summary-amount {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .btn-action {
                padding: 10px 20px;
                font-size: 1rem;
            }

            .order-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Floating Shapes Background -->
    <div class="floating-shapes" id="floatingShapes"></div>

    <!-- Enhanced Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="showProduct.php">
                <i class="fas fa-store"></i> รายการสินค้า
            </a>
            
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart"></i> ตะกร้าสินค้า
                    </a>
                    <a class="nav-link" href="myOrders.php">
                        <i class="fas fa-shopping-bag"></i> การสั่งซื้อ
                    </a>
                    <a class="nav-link" href="show_profile.php">
                        <i class="fas fa-user"></i> โปรไฟล์
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                    </a>
                <?php } else { ?>
                    <a class="nav-link" href="login_form.php">
                        <i class="fas fa-shopping-cart"></i> ตะกร้าสินค้า
                    </a>
                    <a class="nav-link" href="login_form.php">
                        <i class="fas fa-shopping-bag"></i> การสั่งซื้อ
                    </a>
                    <a class="nav-link" href="login_form.php">
                        <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
                    </a>
                    <a class="nav-link" href="register_form.php">
                        <i class="fas fa-user-plus"></i> สมัครสมาชิก
                    </a>
                <?php } ?>
            </div>
        </div>
    </nav>

    <div class="container main-container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1 class="hero-title">
                <i class="fas fa-receipt"></i> รายละเอียดคำสั่งซื้อ <i class="fas fa-receipt"></i>
            </h1>
            <div class="order-number">คำสั่งซื้อ #<?php echo $order_id; ?></div>
        </div>

        <!-- Order Stats -->
        <div class="order-stats">
            <?php
            // คำนวณจำนวนสินค้าทั้งหมด
            $total_items = 0;
            $resultDetails->data_seek(0);
            while ($stat_item = $resultDetails->fetch_assoc()) {
                $total_items += $stat_item['quantity'];
            }
            $resultDetails->data_seek(0);
            ?>
            
            <div class="stat-mini-card">
                <div class="stat-mini-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-mini-value"><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></div>
                <div class="stat-mini-label">วันที่สั่งซื้อ</div>
            </div>
            
            <div class="stat-mini-card">
                <div class="stat-mini-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-mini-value"><?php echo $total_items; ?></div>
                <div class="stat-mini-label">จำนวนสินค้า</div>
            </div>
            
            <div class="stat-mini-card">
                <div class="stat-mini-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-mini-value">฿<?php echo number_format($order['total_price'], 0); ?></div>
                <div class="stat-mini-label">ราคารวม</div>
            </div>
        </div>

        <!-- Order Details Container -->
        <div class="order-container">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i>
                ข้อมูลการสั่งซื้อ
            </h2>

            <!-- Order Information -->
            <div class="order-info">
                <div class="info-card">
                    <h4><i class="fas fa-user"></i> ข้อมูลลูกค้า</h4>
                    <p><strong>ชื่อผู้รับ:</strong> <?php echo htmlspecialchars($order['recipient_name']); ?></p>
                    <p><strong>วันที่สั่งซื้อ:</strong> <?php echo date('d/m/Y เวลา H:i น.', strtotime($order['order_date'])); ?></p>
                </div>
                
                <div class="info-card">
                    <h4><i class="fas fa-map-marker-alt"></i> ที่อยู่จัดส่ง</h4>
                    <p><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                </div>
            </div>

            <div class="info-highlight">
                <i class="fas fa-receipt"></i> ราคารวมทั้งหมด: <?php echo number_format($order['total_price'], 2); ?> บาท
            </div>

            <h3 class="section-title">
                <i class="fas fa-shopping-cart"></i>
                รายการสินค้า
            </h3>

            <div class="products-table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>รหัสสินค้า</th>
                                <th><i class="fas fa-box me-2"></i>ชื่อสินค้า</th>
                                <th><i class="fas fa-money-bill-wave me-2"></i>ราคา (บาท)</th>
                                <th><i class="fas fa-sort-numeric-up me-2"></i>จำนวน</th>
                                <th><i class="fas fa-calculator me-2"></i>รวม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $resultDetails->fetch_assoc()) { ?>
                                <tr>
                                    <td class="product-id"><?php echo $item['product_id']; ?></td>
                                    <td class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td class="product-price">฿<?php echo number_format($item['price'], 2); ?></td>
                                    <td><span class="product-quantity"><?php echo $item['quantity']; ?></span></td>
                                    <td class="product-total">฿<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-title">
                    <i class="fas fa-receipt"></i> ยอดรวมทั้งหมด
                </div>
                <div class="summary-amount">
                    ฿<?php echo number_format($order['total_price'], 2); ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="myOrders.php" class="btn-action btn-back">
                    <i class="fas fa-arrow-left"></i> กลับไปหน้าคำสั่งซื้อ
                </a>
                <a href="showProduct.php" class="btn-action btn-shop">
                    <i class="fas fa-shopping-cart"></i> เลือกซื้อสินค้าเพิ่มเติม
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create floating shapes
        function createFloatingShapes() {
            const shapesContainer = document.getElementById('floatingShapes');
            const shapes = ['fas fa-receipt', 'fas fa-shopping-bag', 'fas fa-box', 'fas fa-star', 'fas fa-gem', 'fas fa-heart'];
            const shapeCount = 25;
            
            for (let i = 0; i < shapeCount; i++) {
                const shape = document.createElement('div');
                shape.className = 'floating-shape';
                shape.innerHTML = `<i class="${shapes[Math.floor(Math.random() * shapes.length)]}"></i>`;
                
                const size = Math.random() * 35 + 25;
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                const animationDelay = Math.random() * 12;
                const animationDuration = Math.random() * 10 + 10;
                
                shape.style.fontSize = size + 'px';
                shape.style.left = x + '%';
                shape.style.top = y + '%';
                shape.style.animationDelay = animationDelay + 's';
                shape.style.animationDuration = animationDuration + 's';
                shape.style.color = `hsl(${Math.random() * 360}, 70%, 80%)`;
                
                shapesContainer.appendChild(shape);
            }
        }

        // Hide loading overlay
        function hideLoading() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            setTimeout(() => {
                loadingOverlay.classList.add('fade-out');
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                }, 500);
            }, 1200);
        }

        // Add ripple effect to buttons
        function addRippleEffect() {
            document.querySelectorAll('.btn-action').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255,255,255,0.6)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.pointerEvents = 'none';
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        }

        // Animate elements on scroll
        function animateOnScroll() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationDelay = `${Math.random() * 0.5}s`;
                        entry.target.classList.add('animate-in');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.info-card, .stat-mini-card, table tbody tr, .order-summary').forEach(element => {
                observer.observe(element);
            });
        }

        // Table row hover effects
        function addTableEffects() {
            const tableRows = document.querySelectorAll('table tbody tr');
            
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.15}s`;
                
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                    this.style.zIndex = '10';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.zIndex = '1';
                });
            });
        }

        // Parallax effect
        function addParallaxEffect() {
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const shapes = document.querySelectorAll('.floating-shape');
                
                shapes.forEach((shape, index) => {
                    const speed = (index % 4 + 1) * 0.12;
                    shape.style.transform = `translateY(${scrolled * speed}px) rotate(${scrolled * speed * 0.3}deg)`;
                });
            });
        }

        // Info cards animation
        function addInfoCardEffects() {
            const cards = document.querySelectorAll('.info-card');
            
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.2}s`;
                
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                    this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.2)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = '';
                });
            });
        }

        // Stats cards animation
        function addStatsAnimation() {
            const statCards = document.querySelectorAll('.stat-mini-card');
            
            statCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.3}s`;
                
                // Animate numbers
                const valueElement = card.querySelector('.stat-mini-value');
                const value = valueElement.textContent;
                
                if (!isNaN(parseInt(value))) {
                    const targetNumber = parseInt(value);
                    let currentNumber = 0;
                    const increment = targetNumber / 50;
                    
                    const timer = setInterval(() => {
                        currentNumber += increment;
                        if (currentNumber >= targetNumber) {
                            currentNumber = targetNumber;
                            clearInterval(timer);
                        }
                        
                        if (value.includes('฿')) {
                            valueElement.textContent = '฿' + Math.floor(currentNumber).toLocaleString();
                        } else if (value.includes('/')) {
                            valueElement.textContent = value; // Keep date format
                        } else {
                            valueElement.textContent = Math.floor(currentNumber).toLocaleString();
                        }
                    }, 50);
                }
            });
        }

        // Enhanced button interactions
        function enhanceButtons() {
            document.querySelectorAll('.btn-action').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.05)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
                
                button.addEventListener('mousedown', function() {
                    this.style.transform = 'translateY(-2px) scale(1.02)';
                });
                
                button.addEventListener('mouseup', function() {
                    this.style.transform = 'translateY(-5px) scale(1.05)';
                });
            });
        }

        // Toast notification system
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-notification toast-${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            createFloatingShapes();
            hideLoading();
            addRippleEffect();
            animateOnScroll();
            addTableEffects();
            addParallaxEffect();
            addInfoCardEffects();
            enhanceButtons();
            
            // Add stats animation after delay
            setTimeout(() => {
                addStatsAnimation();
            }, 1800);

            // Show welcome toast
            setTimeout(() => {
                showToast('รายละเอียดคำสั่งซื้อโหลดเสร็จแล้ว!', 'success');
            }, 2000);
        });

        // Add CSS for animations and effects
        const additionalStyle = document.createElement('style');
        additionalStyle.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .info-card,
            .stat-mini-card,
            table tbody tr,
            .order-summary {
                opacity: 0;
                transform: translateY(50px);
                transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }
            
            .info-card.animate-in,
            .stat-mini-card.animate-in,
            table tbody tr.animate-in,
            .order-summary.animate-in {
                opacity: 1;
                transform: translateY(0);
            }
            
            .toast-notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: rgba(76, 175, 80, 0.95);
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                backdrop-filter: blur(10px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                transform: translateX(100%);
                transition: transform 0.3s ease;
                z-index: 10000;
                display: flex;
                align-items: center;
                gap: 10px;
                font-weight: 600;
            }
            
            .toast-notification.show {
                transform: translateX(0);
            }
            
            .toast-error {
                background: rgba(244, 67, 54, 0.95) !important;
            }
            
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(50px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes fadeInScale {
                from {
                    opacity: 0;
                    transform: scale(0.8);
                }
                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }
            
            .hero-section {
                animation: fadeInScale 1.2s ease-out;
            }
            
            .stat-mini-card:nth-child(1) { animation-delay: 0.2s; }
            .stat-mini-card:nth-child(2) { animation-delay: 0.5s; }
            .stat-mini-card:nth-child(3) { animation-delay: 0.8s; }
            
            .info-card:nth-child(1) { animation-delay: 0.3s; }
            .info-card:nth-child(2) { animation-delay: 0.6s; }
            
            /* Enhanced hover effects */
            .stat-mini-card:hover .stat-mini-icon {
                transform: scale(1.3) rotate(360deg);
                transition: all 0.6s ease;
            }
            
            .stat-mini-card:hover .stat-mini-value {
                color: #4CAF50;
                transform: scale(1.1);
                transition: all 0.3s ease;
            }
            
            .product-price:hover,
            .product-total:hover {
                text-shadow: 0 0 15px rgba(76, 175, 80, 0.6);
                transform: scale(1.05);
                transition: all 0.3s ease;
            }
            
            /* Table row stagger animation */
            table tbody tr:nth-child(1) { animation-delay: 0.1s; }
            table tbody tr:nth-child(2) { animation-delay: 0.2s; }
            table tbody tr:nth-child(3) { animation-delay: 0.3s; }
            table tbody tr:nth-child(4) { animation-delay: 0.4s; }
            table tbody tr:nth-child(5) { animation-delay: 0.5s; }
            
            /* Loading spinner enhancement */
            .loading-spinner::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 60px;
                height: 60px;
                border: 6px solid transparent;
                border-top: 6px solid #2e7d32;
                border-radius: 50%;
                transform: translate(-50%, -50%);
                animation: spin 0.8s linear infinite reverse;
            }
            
            /* Quantity badge animation */
            .product-quantity {
                animation: bounceIn 0.6s ease-out;
                animation-fill-mode: both;
            }
            
            @keyframes bounceIn {
                0% { transform: scale(0.3); opacity: 0; }
                50% { transform: scale(1.05); }
                70% { transform: scale(0.9); }
                100% { transform: scale(1); opacity: 1; }
            }
            
            /* Summary amount pulse effect */
            .summary-amount:hover {
                animation: none;
                transform: scale(1.1);
                text-shadow: 0 0 30px rgba(255,255,255,0.8);
                transition: all 0.3s ease;
            }
            
            /* Mobile toast positioning */
            @media (max-width: 768px) {
                .toast-notification {
                    right: 10px;
                    left: 10px;
                    transform: translateY(-100%);
                }
                
                .toast-notification.show {
                    transform: translateY(0);
                }
            }
            
            /* Button press effect */
            .btn-action:active {
                transform: translateY(-1px) scale(0.98) !important;
                transition: all 0.1s ease;
            }
            
            /* Card glow effect on hover */
            .info-card:hover {
                box-shadow: 
                    0 15px 35px rgba(0,0,0,0.15),
                    0 0 20px rgba(76, 175, 80, 0.2);
            }
            
            /* Enhanced shimmer effects */
            .order-container:hover::before {
                animation-duration: 4s;
            }
            
            .order-summary:hover::before {
                animation-duration: 2s;
            }
        `;
        document.head.appendChild(additionalStyle);
    </script>
</body>
</html>

<?php
$conn->close();
?>