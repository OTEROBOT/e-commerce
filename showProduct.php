<?php
//showProduct.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "conn.php";

// รับค่าค้นหาจาก GET
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : "";

if (!empty($keyword)) {
    $sql = "SELECT productID, product_name, origin, price, image 
            FROM product 
            WHERE product_name LIKE ?";
    $stmt = $conn->prepare($sql);
    $search = "%" . $keyword . "%";
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT productID, product_name, origin, price, image FROM product";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สินค้า - ร้านค้าออนไลน์</title>
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

        /* Enhanced Carousel */
        .carousel {
            margin-bottom: 50px;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 
                0 25px 50px rgba(0,0,0,0.3),
                0 0 0 1px rgba(255,255,255,0.1);
            position: relative;
        }

        .carousel-item img {
            height: 400px;
            object-fit: cover;
            filter: brightness(0.9) contrast(1.1);
            transition: all 0.5s ease;
        }

        .carousel-item.active img {
            filter: brightness(1) contrast(1.2);
            transform: scale(1.02);
        }

        .carousel-control-prev,
        .carousel-control-next {
            background: linear-gradient(135deg, rgba(0,0,0,0.5), rgba(0,0,0,0.3));
            border-radius: 50%;
            width: 60px;
            height: 60px;
            top: 50%;
            transform: translateY(-50%);
        }

        .carousel-control-prev {
            left: 20px;
        }

        .carousel-control-next {
            right: 20px;
        }

        /* Search Section */
        .search-container {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .search-container .input-group {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 15px;
            overflow: hidden;
        }

        .search-container .form-control {
            border: none;
            padding: 15px 20px;
            font-size: 1.1rem;
            background: rgba(255,255,255,0.9);
        }

        .search-container .form-control:focus {
            box-shadow: 0 0 20px rgba(76, 175, 80, 0.3);
            border: 2px solid #4CAF50;
        }

        .search-container .btn {
            padding: 15px 25px;
            font-weight: 600;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #90a4ae, #607d8b);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #607d8b, #455a64);
            transform: translateY(-2px);
        }

        /* Product Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        /* Enhanced Product Cards */
        .product-card {
            background: rgba(255,255,255,0.95);
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(20px);
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(76, 175, 80, 0.1), transparent);
            transition: all 0.5s ease;
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }

        .product-card:hover::before {
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }

        .product-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 
                0 25px 50px rgba(0,0,0,0.3),
                0 0 0 1px rgba(76, 175, 80, 0.2);
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
            height: 250px;
        }

        .product-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .product-card:hover img {
            transform: scale(1.15) rotate(2deg);
            filter: brightness(1.1) contrast(1.1);
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.8), rgba(46, 125, 50, 0.8));
            opacity: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .overlay-icon {
            color: white;
            font-size: 3rem;
            animation: bounceIn 0.5s ease;
        }

        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        .product-body {
            padding: 25px;
            position: relative;
            z-index: 2;
        }

        .product-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 10px;
            line-height: 1.3;
            transition: color 0.3s ease;
        }

        .product-card:hover .product-title {
            color: #1b5e20;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: #4CAF50;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
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
            background: linear-gradient(135deg, #90a4ae, #607d8b);
            color: white;
            border: none;
        }

        .btn-detail:hover {
            background: linear-gradient(135deg, #607d8b, #455a64);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(96, 125, 139, 0.4);
            color: white;
        }

        .btn-cart {
            background: linear-gradient(135deg, #ff6f00, #f57c00);
            color: white;
            border: none;
        }

        .btn-cart:hover {
            background: linear-gradient(135deg, #f57c00, #ef6c00);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(255, 111, 0, 0.4);
            color: white;
        }

        .btn-login {
            background: linear-gradient(135deg, #2196f3, #1976d2);
            color: white;
            border: none;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #1976d2, #1565c0);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(33, 150, 243, 0.4);
            color: white;
        }

        /* No Products Message */
        .no-products {
            text-align: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 50px 0;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
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

            .products-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 20px;
            }

            .carousel-item img {
                height: 250px;
            }

            .navbar a {
                margin: 5px;
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }

            .product-actions {
                flex-direction: column;
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
                <i class="fas fa-sparkles"></i> ร้านค้าออนไลน์ <i class="fas fa-sparkles"></i>
            </h1>
        </div>

        <!-- Enhanced Carousel -->
        <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="gallery_products/sample1.jpg" class="d-block w-100" alt="สินค้า 1">
                </div>
                <div class="carousel-item">
                    <img src="gallery_products/sample2.jpg" class="d-block w-100" alt="สินค้า 2">
                </div>
                <div class="carousel-item">
                    <img src="gallery_products/sample3.jpg" class="d-block w-100" alt="สินค้า 3">
                </div>
                <div class="carousel-item">
                    <img src="gallery_products/sample4.jpg" class="d-block w-100" alt="สินค้า 4">
                </div>
                <div class="carousel-item">
                    <img src="gallery_products/sample5.jpg" class="d-block w-100" alt="สินค้า 5">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

        <!-- Enhanced Search -->
        <div class="search-container">
            <form method="GET" action="showProduct.php">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control" 
                           placeholder="🔍 ค้นหาสินค้าที่คุณต้องการ..." 
                           value="<?php echo htmlspecialchars($keyword); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                    <?php if (!empty($keyword)) { ?>
                        <a href="showProduct.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> ล้าง
                        </a>
                    <?php } ?>
                </div>
            </form>
        </div>

        <!-- Products Grid -->
        <div class="products-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $image_path = !empty($row['image']) && file_exists("gallery_products/" . $row['image']) 
                        ? "gallery_products/" . htmlspecialchars($row['image']) 
                        : "gallery_products/default.png";

                    // ย่อชื่อสินค้า
                    $short_name = (mb_strlen($row['product_name'], 'UTF-8') > 25) 
                        ? mb_substr($row['product_name'], 0, 25, 'UTF-8') . "..." 
                        : $row['product_name'];
            ?>
            <div class="product-card">
                <div class="product-image-container">
                    <img src="<?php echo $image_path; ?>" 
                         alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                    <div class="product-overlay">
                        <i class="fas fa-eye overlay-icon"></i>
                    </div>
                </div>
                <div class="product-body">
                    <h5 class="product-title" 
                        title="<?php echo htmlspecialchars($row['product_name']); ?>">
                        <?php echo htmlspecialchars($short_name); ?>
                    </h5>
                    <div class="product-price">
                        ฿<?php echo number_format($row['price'], 2); ?>
                    </div>
                    <div class="product-actions">
                        <a href="product_detail2.php?id=<?php echo $row['productID']; ?>" 
                           class="btn-action btn-detail">
                           <i class="fas fa-info-circle"></i> รายละเอียด
                        </a>
                        <?php if (isset($_SESSION['user_id'])) { ?>
                            <a href="cart.php?action=add&id=<?php echo $row['productID']; ?>" 
                               class="btn-action btn-cart">
                               <i class="fas fa-cart-plus"></i> ใส่ตะกร้า
                            </a>
                        <?php } else { ?>
                            <a href="login_form.php" class="btn-action btn-login">
                               <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<div class='no-products'>";
                echo "<i class='fas fa-search fa-3x mb-3'></i><br>";
                echo "ไม่พบสินค้าที่ค้นหา";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create floating shapes
        function createFloatingShapes() {
            const shapesContainer = document.getElementById('floatingShapes');
            const shapes = ['fas fa-star', 'fas fa-heart', 'fas fa-gem', 'fas fa-leaf', 'fas fa-snowflake'];
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
            document.querySelectorAll('.btn-action, .btn-primary, .btn-secondary').forEach(button => {
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

        // Parallax effect for carousel
        function addParallaxEffect() {
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const carousel = document.querySelector('.carousel');
                if (carousel) {
                    carousel.style.transform = `translateY(${scrolled * 0.3}px)`;
                }
            });
        }

        // Smooth scroll for anchor links
        function addSmoothScroll() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        }

        // Card entrance animation
        function animateCards() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationDelay = `${Math.random() * 0.5}s`;
                        entry.target.classList.add('animate-in');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.product-card').forEach(card => {
                observer.observe(card);
            });
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            createFloatingShapes();
            hideLoading();
            addRippleEffect();
            addParallaxEffect();
            addSmoothScroll();
            animateCards();
            
            // Auto-play carousel with custom interval
            const carousel = new bootstrap.Carousel(document.querySelector('#productCarousel'), {
                interval: 4000,
                ride: 'carousel'
            });

            // Search input focus effect
            const searchInput = document.querySelector('input[name="keyword"]');
            if (searchInput) {
                searchInput.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                    this.parentElement.style.transition = 'transform 0.3s ease';
                });
                
                searchInput.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            }
        });

        // Add CSS for card animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .product-card {
                opacity: 0;
                transform: translateY(50px);
                transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }
            
            .product-card.animate-in {
                opacity: 1;
                transform: translateY(0);
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
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>

<?php $conn->close(); ?>