<?php
// myOrders.php
// แสดงรายการคำสั่งซื้อของลูกค้าที่ล็อกอินอยู่สำหรับดูประวัติการสั่งซื้อของลูกค้า
include "check_session.php";
include "conn.php";

$user_id = $_SESSION['user_id'];

// ดึงรายการสั่งซื้อของลูกค้าคนนี้
$sql = "SELECT order_id, order_date, total_price 
        FROM orders 
        WHERE customer_id = ? 
        ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำสั่งซื้อของฉัน - OTEROBOT Store</title>
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
            font-size: 4rem;
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

        /* Orders Container */
        .orders-container {
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

        .orders-container::before {
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

        .orders-container > * {
            position: relative;
            z-index: 2;
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

        /* Enhanced Orders Table */
        .orders-table-container {
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
            padding: 20px 15px;
            font-weight: 700;
            font-size: 1.1rem;
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
            padding: 20px 15px;
            border: none;
            font-weight: 500;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .order-id {
            font-weight: 700;
            color: #2e7d32;
            font-size: 1.1rem;
        }

        .order-date {
            color: #666;
            font-weight: 500;
        }

        .order-price {
            font-weight: 800;
            color: #4CAF50;
            font-size: 1.3rem;
        }

        /* Enhanced Buttons */
        .btn-action {
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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

        .btn-detail {
            background: linear-gradient(135deg, #2196f3, #1976d2);
            color: white;
        }

        .btn-detail:hover {
            background: linear-gradient(135deg, #1976d2, #1565c0);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(33, 150, 243, 0.4);
            color: white;
        }

        .btn-back {
            background: linear-gradient(135deg, #90a4ae, #607d8b);
            color: white;
        }

        .btn-back:hover {
            background: linear-gradient(135deg, #607d8b, #455a64);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(96, 125, 139, 0.4);
            color: white;
        }

        .btn-shop {
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            color: white;
            font-size: 1.2rem;
            padding: 15px 30px;
        }

        .btn-shop:hover {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(76, 175, 80, 0.4);
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            margin: 40px 0;
        }

        .empty-state i {
            font-size: 5rem;
            color: rgba(255,255,255,0.7);
            margin-bottom: 30px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .empty-state h3 {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .empty-state p {
            color: rgba(255,255,255,0.8);
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .stat-icon {
            font-size: 3rem;
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 10px;
        }

        .stat-label {
            color: rgba(255,255,255,0.8);
            font-size: 1.1rem;
            font-weight: 500;
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

            .orders-container {
                padding: 20px;
                margin: 20px 10px;
            }

            .table thead th,
            .table tbody td {
                padding: 15px 8px;
                font-size: 0.9rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stat-card {
                padding: 20px;
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
                padding: 8px 15px;
                font-size: 0.9rem;
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

    <!-- Enhanced Navbar (same as showProduct.php) -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="showProduct.php">
                <i class="fas fa-store"></i> รายการสินค้า
            </a>
            
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <!-- ถ้าล็อกอินแล้ว -->
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
                    <!-- ถ้ายังไม่ล็อกอิน -->
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
                <i class="fas fa-shopping-bag"></i> คำสั่งซื้อของฉัน <i class="fas fa-shopping-bag"></i>
            </h1>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <!-- Stats Cards -->
            <div class="stats-container">
                <?php
                // คำนวณสถิติ
                $total_orders = $result->num_rows;
                $total_amount = 0;
                $result->data_seek(0); // Reset result pointer
                while ($stat_row = $result->fetch_assoc()) {
                    $total_amount += $stat_row['total_price'];
                }
                $result->data_seek(0); // Reset อีกครั้งสำหรับแสดงผล
                ?>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label">คำสั่งซื้อทั้งหมด</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-number">฿<?php echo number_format($total_amount, 0); ?></div>
                    <div class="stat-label">ยอดซื้อทั้งหมด</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-number">฿<?php echo number_format($total_amount / $total_orders, 0); ?></div>
                    <div class="stat-label">ค่าเฉลี่ยต่อคำสั่งซื้อ</div>
                </div>
            </div>

            <!-- Orders Container -->
            <div class="orders-container">
                <h2 class="section-title">
                    <i class="fas fa-list-alt"></i>
                    รายการคำสั่งซื้อ
                </h2>

                <div class="orders-table-container">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag me-2"></i>หมายเลขคำสั่งซื้อ</th>
                                    <th><i class="fas fa-calendar-alt me-2"></i>วันที่สั่งซื้อ</th>
                                    <th><i class="fas fa-money-bill-wave me-2"></i>ราคารวม</th>
                                    <th><i class="fas fa-cogs me-2"></i>การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="order-id">#<?php echo $row['order_id']; ?></td>
                                        <td class="order-date">
                                            <i class="fas fa-clock me-2"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?>
                                        </td>
                                        <td class="order-price">฿<?php echo number_format($row['total_price'], 2); ?></td>
                                        <td>
                                            <a href="viewOrder.php?order_id=<?php echo $row['order_id']; ?>"
                                               class="btn-action btn-detail">
                                                <i class="fas fa-eye"></i> ดูรายละเอียด
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-center">
                    <a href="showProduct.php" class="btn-action btn-shop">
                        <i class="fas fa-shopping-cart"></i> เลือกซื้อสินค้าเพิ่มเติม
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <h3>ยังไม่มีคำสั่งซื้อ</h3>
                <p>คุณยังไม่เคยสั่งซื้อสินค้าใดๆ เลย</p>
                <a href="showProduct.php" class="btn-action btn-shop">
                    <i class="fas fa-store"></i> เริ่มเลือกซื้อสินค้า
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create floating shapes (same as showProduct.php)
        function createFloatingShapes() {
            const shapesContainer = document.getElementById('floatingShapes');
            const shapes = ['fas fa-shopping-bag', 'fas fa-heart', 'fas fa-star', 'fas fa-gift', 'fas fa-gem'];
            const shapeCount = 20;
            
            for (let i = 0; i < shapeCount; i++) {
                const shape = document.createElement('div');
                shape.className = 'floating-shape';
                shape.innerHTML = `<i class="${shapes[Math.floor(Math.random() * shapes.length)]}"></i>`;
                
                const size = Math.random() * 30 + 20;
                const x = Math.random() * 100;
                const y = Math.random() * 100;
                const animationDelay = Math.random() * 12;
                const animationDuration = Math.random() * 8 + 8;
                
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
            }, 1000);
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

            document.querySelectorAll('.stat-card, .orders-container, table tbody tr').forEach(element => {
                observer.observe(element);
            });
        }

        // Counter animation for stats
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (counter.textContent.includes('฿')) {
                        counter.textContent = '฿' + Math.floor(current).toLocaleString();
                    } else {
                        counter.textContent = Math.floor(current).toLocaleString();
                    }
                }, 16);
            });
        }

        // Table row hover effects
        function addTableEffects() {
            const tableRows = document.querySelectorAll('table tbody tr');
            
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
                
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.01)';
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
                    const speed = (index % 3 + 1) * 0.1;
                    shape.style.transform = `translateY(${scrolled * speed}px) rotate(${scrolled * speed * 0.5}deg)`;
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
            
            // Animate counters after a delay
            setTimeout(() => {
                animateCounters();
            }, 1500);

            // Add click sound effect (optional)
            document.querySelectorAll('.btn-action').forEach(button => {
                button.addEventListener('click', function() {
                    // You can add sound effect here if needed
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
        });

        // Add CSS for animations and toast notifications
        const additionalStyle = document.createElement('style');
        additionalStyle.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .stat-card,
            .orders-container,
            table tbody tr {
                opacity: 0;
                transform: translateY(50px);
                transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }
            
            .stat-card.animate-in,
            .orders-container.animate-in,
            table tbody tr.animate-in {
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
                animation: fadeInScale 1s ease-out;
            }
            
            .stat-card:nth-child(1) { animation-delay: 0.2s; }
            .stat-card:nth-child(2) { animation-delay: 0.4s; }
            .stat-card:nth-child(3) { animation-delay: 0.6s; }
            
            /* Enhanced hover effects for stats */
            .stat-card:hover .stat-icon {
                transform: scale(1.2) rotate(360deg);
                transition: all 0.5s ease;
            }
            
            .stat-card:hover .stat-number {
                color: #4CAF50;
                transition: color 0.3s ease;
            }
            
            /* Table row stagger animation */
            table tbody tr:nth-child(1) { animation-delay: 0.1s; }
            table tbody tr:nth-child(2) { animation-delay: 0.2s; }
            table tbody tr:nth-child(3) { animation-delay: 0.3s; }
            table tbody tr:nth-child(4) { animation-delay: 0.4s; }
            table tbody tr:nth-child(5) { animation-delay: 0.5s; }
            
            /* Loading spinner variations */
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
            
            /* Glowing effect for important elements */
            .order-price:hover,
            .stat-number:hover {
                text-shadow: 0 0 20px rgba(76, 175, 80, 0.5);
                transition: text-shadow 0.3s ease;
            }
            
            /* Mobile optimizations */
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
        `;
        document.head.appendChild(additionalStyle);
    </script>
</body>
</html>

<?php 
$stmt->close(); 
$conn->close(); 
?>