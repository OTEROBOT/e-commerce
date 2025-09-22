<?php
session_start();
include "conn.php";
// cart.php

// ตรวจสอบ action
if (isset($_GET['action']) && $_GET['action'] == "add") {
    if (isset($_GET['id'])) {
        $productID = $conn->real_escape_string($_GET['id']);

        // ดึงข้อมูลสินค้าจากฐานข้อมูล
        $sql = "SELECT productID, product_name, price, image FROM product WHERE productID = '$productID'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();

            // ถ้า session cart ยังไม่ถูกสร้าง ให้สร้าง
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // ถ้ามีสินค้าอยู่แล้วในตะกร้า → เพิ่มจำนวน
            if (isset($_SESSION['cart'][$productID])) {
                $_SESSION['cart'][$productID]['qty'] += 1;
            } else {
                // ถ้ายังไม่มีสินค้า → เพิ่มใหม่
                $_SESSION['cart'][$productID] = [
                    "productID" => $product['productID'],
                    "name"      => $product['product_name'],
                    "price"     => $product['price'],
                    "image"     => $product['image'],
                    "qty"       => 1
                ];
            }
        }
    }

    header("Location: cart.php");
    exit;
}

// ถ้าอัปเดตจำนวนสินค้า
if (isset($_POST['update_cart'])) {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $productID => $qty) {
            $qty = intval($qty);
            if ($qty <= 0) {
                unset($_SESSION['cart'][$productID]);
            } else {
                if (isset($_SESSION['cart'][$productID])) {
                    $_SESSION['cart'][$productID]['qty'] = $qty;
                }
            }
        }
    }
    header("Location: cart.php");
    exit;
}

// ถ้ากดลบสินค้าออกจากตะกร้า
if (isset($_GET['action']) && $_GET['action'] == "remove") {
    if (isset($_GET['id']) && isset($_SESSION['cart'][$_GET['id']])) {
        unset($_SESSION['cart'][$_GET['id']]);
    }
    header("Location: cart.php");
    exit;
}

// ถ้าล้างตะกร้า
if (isset($_GET['action']) && $_GET['action'] == "clear") {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า - ร้านค้าออนไลน์</title>
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

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        .page-title {
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

        /* Cart Container */
        .cart-container {
            background: rgba(255,255,255,0.95);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            position: relative;
            overflow: hidden;
        }

        .cart-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(76, 175, 80, 0.1), transparent);
            animation: containerShimmer 6s ease-in-out infinite;
        }

        @keyframes containerShimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        /* Cart Items */
        .cart-item {
            background: rgba(255,255,255,0.8);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            border: 1px solid rgba(76, 175, 80, 0.1);
        }

        .cart-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .cart-item:hover .cart-item-image {
            transform: scale(1.05);
        }

        .cart-item-details {
            flex: 1;
            padding: 0 20px;
        }

        .cart-item-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2e7d32;
            margin-bottom: 10px;
        }

        .cart-item-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        .quantity-input {
            width: 60px;
            text-align: center;
            border: 2px solid #4CAF50;
            border-radius: 10px;
            padding: 5px;
            font-weight: 600;
        }

        .remove-btn {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 12px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .remove-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s ease;
        }

        .remove-btn:hover::before {
            left: 100%;
        }

        .remove-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(244, 67, 54, 0.4);
        }

        /* Cart Summary */
        .cart-summary {
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 15px 35px rgba(76, 175, 80, 0.3);
            position: relative;
            overflow: hidden;
        }

        .cart-summary::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: summaryShimmer 5s ease-in-out infinite;
        }

        @keyframes summaryShimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .total-price {
            font-size: 2rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        /* Buttons */
        .btn-enhanced {
            padding: 15px 30px;
            border: none;
            border-radius: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }

        .btn-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s ease;
        }

        .btn-enhanced:hover::before {
            left: 100%;
        }

        .btn-primary-enhanced {
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            color: white;
        }

        .btn-primary-enhanced:hover {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(76, 175, 80, 0.4);
            color: white;
        }

        .btn-secondary-enhanced {
            background: linear-gradient(135deg, #90a4ae, #607d8b);
            color: white;
        }

        .btn-secondary-enhanced:hover {
            background: linear-gradient(135deg, #607d8b, #455a64);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(96, 125, 139, 0.4);
            color: white;
        }

        .btn-warning-enhanced {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
        }

        .btn-warning-enhanced:hover {
            background: linear-gradient(135deg, #f57c00, #ef6c00);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 152, 0, 0.4);
            color: white;
        }

        .btn-success-enhanced {
            background: linear-gradient(135deg, #8bc34a, #689f38);
            color: white;
        }

        .btn-success-enhanced:hover {
            background: linear-gradient(135deg, #689f38, #558b2f);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(139, 195, 74, 0.4);
            color: white;
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 80px 40px;
            color: #2e7d32;
        }

        .empty-cart i {
            font-size: 6rem;
            margin-bottom: 30px;
            opacity: 0.3;
        }

        .empty-cart h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
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
            .page-title {
                font-size: 2.5rem;
            }

            .cart-item {
                flex-direction: column;
                text-align: center;
            }

            .cart-item-details {
                padding: 20px 0;
            }

            .cart-item-image {
                margin: 0 auto 20px;
            }

            .quantity-control {
                justify-content: center;
            }

            .btn-enhanced {
                width: 100%;
                margin: 10px 0;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 2rem;
            }

            .cart-container {
                padding: 20px;
                margin: 0 10px;
            }

            .cart-summary {
                padding: 20px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Ripple Effect */
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
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
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-shopping-cart"></i> ตะกร้าสินค้า <i class="fas fa-heart"></i>
            </h1>
        </div>

        <div class="cart-container animate-in">
            <?php if (!empty($_SESSION['cart'])): ?>
                <!-- Cart Items -->
                <form action="cart.php" method="post" id="cartForm">
                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $index => $item):
                        $subtotal = $item['price'] * $item['qty'];
                        $total += $subtotal;
                        
                        $image_path = !empty($item['image']) && file_exists("gallery_products/" . $item['image']) 
                            ? "gallery_products/" . htmlspecialchars($item['image']) 
                            : "gallery_products/default.png";
                    ?>
                    <div class="cart-item d-flex align-items-center">
                        <img src="<?php echo $image_path; ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="cart-item-image">
                        
                        <div class="cart-item-details">
                            <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="cart-item-price">฿<?php echo number_format($item['price'], 2); ?></div>
                            
                            <div class="quantity-control">
                                <button type="button" class="quantity-btn" onclick="updateQuantity('<?php echo $item['productID']; ?>', -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" 
                                       name="quantities[<?php echo $item['productID']; ?>]" 
                                       value="<?php echo $item['qty']; ?>" 
                                       min="1" 
                                       class="quantity-input"
                                       id="qty_<?php echo $item['productID']; ?>"
                                       onchange="this.form.submit()">
                                <button type="button" class="quantity-btn" onclick="updateQuantity('<?php echo $item['productID']; ?>', 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <div class="h4 text-success fw-bold mb-3">฿<?php echo number_format($subtotal, 2); ?></div>
                            <button type="button" 
                                    class="remove-btn" 
                                    onclick="removeItem('<?php echo $item['productID']; ?>')">
                                <i class="fas fa-trash"></i> ลบ
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <input type="hidden" name="update_cart" value="1">
                </form>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3><i class="fas fa-calculator"></i> สรุปยอดรวม</h3>
                            <div class="total-price">฿<?php echo number_format($total, 2); ?></div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="d-flex flex-column gap-3">
                                <form action="checkout.php" method="post" style="display: inline;">
                                    <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                                        <input type="hidden" name="products[<?php echo $index; ?>][productID]" value="<?php echo $item['productID']; ?>">
                                        <input type="hidden" name="products[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($item['name']); ?>">
                                        <input type="hidden" name="products[<?php echo $index; ?>][price]" value="<?php echo $item['price']; ?>">
                                        <input type="hidden" name="products[<?php echo $index; ?>][qty]" value="<?php echo $item['qty']; ?>">
                                    <?php endforeach; ?>
                                    <input type="hidden" name="total" value="<?php echo $total; ?>">
                                    <button type="submit" class="btn-enhanced btn-success-enhanced">
                                        <i class="fas fa-credit-card"></i> สั่งซื้อสินค้า
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between flex-wrap mt-4">
                    <a href="showProduct.php" class="btn-enhanced btn-secondary-enhanced">
                        <i class="fas fa-arrow-left"></i> กลับไปเลือกซื้อสินค้า
                    </a>
                    <button type="button" class="btn-enhanced btn-warning-enhanced" onclick="confirmClear()">
                        <i class="fas fa-trash-alt"></i> ล้างตะกร้าทั้งหมด
                    </button>
                </div>

            <?php else: ?>
                <!-- Empty Cart -->
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>ตะกร้าสินค้าว่างเปล่า</h3>
                    <p class="mb-4">ยังไม่มีสินค้าในตะกร้า เริ่มเลือกซื้อสินค้าได้เลย!</p>
                    <a href="showProduct.php" class="btn-enhanced btn-primary-enhanced">
                        <i class="fas fa-shopping-bag"></i> เริ่มช้อปปิ้ง
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create floating shapes
        function createFloatingShapes() {
            const shapesContainer = document.getElementById('floatingShapes');
            const shapes = ['fas fa-star', 'fas fa-heart', 'fas fa-gem', 'fas fa-leaf', 'fas fa-snowflake'];
            const shapeCount = 15;
            
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
            }, 800);
        }

        // Update quantity function
        function updateQuantity(productID, change) {
            const input = document.getElementById('qty_' + productID);
            let currentValue = parseInt(input.value);
            let newValue = currentValue + change;
            
            if (newValue < 1) newValue = 1;
            
            input.value = newValue;
            document.getElementById('cartForm').submit();
        }

        // Remove item function
        function removeItem(productID) {
            Swal.fire({
                title: 'ต้องการลบสินค้าหรือไม่?',
                text: "การกระทำนี้ไม่สามารถย้อนกลับได้",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'cart.php?action=remove&id=' + productID;
                }
            });
        }

        // Clear cart confirmation
        function confirmClear() {
            Swal.fire({
                title: 'ต้องการล้างตะกร้าทั้งหมดหรือไม่?',
                text: "สินค้าทั้งหมดในตะกร้าจะถูกลบ",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ล้างทั้งหมด!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'cart.php?action=clear';
                }
            });
        }

        // Add ripple effect to buttons
        function addRippleEffect() {
            document.querySelectorAll('.btn-enhanced, .quantity-btn, .remove-btn').forEach(button => {
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

        // Cart item entrance animation
        function animateCartItems() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationDelay = `${Math.random() * 0.5}s`;
                        entry.target.classList.add('animate-in');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.cart-item').forEach(item => {
                observer.observe(item);
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

        // Auto-save cart changes
        function setupAutoSave() {
            const quantityInputs = document.querySelectorAll('.quantity-input');
            quantityInputs.forEach(input => {
                let timeoutId;
                input.addEventListener('input', function() {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => {
                        if (this.value < 1) this.value = 1;
                        document.getElementById('cartForm').submit();
                    }, 1000);
                });
            });
        }

        // Cart total animation
        function animateTotal() {
            const totalElement = document.querySelector('.total-price');
            if (totalElement) {
                totalElement.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    totalElement.style.transform = 'scale(1)';
                }, 300);
            }
        }

        // Initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            createFloatingShapes();
            hideLoading();
            addRippleEffect();
            animateCartItems();
            addSmoothScroll();
            setupAutoSave();
            
            // Add hover effects to cart items
            document.querySelectorAll('.cart-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.02)';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Animate total on page load
            setTimeout(animateTotal, 1000);
        });

        // Add success notifications
        function showSuccess(message) {
            const notification = document.createElement('div');
            notification.className = 'alert alert-success position-fixed';
            notification.style.cssText = `
                top: 20px;
                right: 20px;
                z-index: 10000;
                opacity: 0;
                transform: translateX(100px);
                transition: all 0.3s ease;
            `;
            notification.innerHTML = `
                <i class="fas fa-check-circle"></i> ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100px)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Check for URL parameters to show success messages
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('updated') === 'true') {
            showSuccess('อัปเดตตะกร้าสินค้าเรียบร้อยแล้ว');
        }
        if (urlParams.get('added') === 'true') {
            showSuccess('เพิ่มสินค้าในตะกร้าเรียบร้อยแล้ว');
        }
    </script>
    
    <!-- SweetAlert2 for beautiful alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Additional animations */
        .cart-item {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .cart-item.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Hover effects for interactive elements */
        .quantity-input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 10px rgba(76, 175, 80, 0.3);
        }
        
        .cart-item-name {
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .cart-item-name:hover {
            color: #1b5e20;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #4CAF50, #2e7d32);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #2e7d32, #1b5e20);
        }
        
        /* Mobile optimizations */
        @media (max-width: 576px) {
            .cart-item {
                padding: 15px;
            }
            
            .cart-item-image {
                width: 80px;
                height: 80px;
            }
            
            .quantity-control {
                margin-top: 15px;
            }
            
            .remove-btn {
                width: 100%;
                margin-top: 15px;
            }
        }
    </style>
</body>
</html>

<?php $conn->close(); ?>