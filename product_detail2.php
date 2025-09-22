<?php
// product_detail2.php
include "conn.php";

if (!isset($_GET['id'])) {
    die("❌ ไม่พบรหัสสินค้า");
}

$id = $conn->real_escape_string($_GET['id']); // ปลอดภัย + รองรับ string
$sql = "SELECT productID, product_name, origin, price, image, category, details 
        FROM product WHERE productID = '$id'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    die("❌ ไม่พบสินค้า");
}

// ดึงสินค้าที่เกี่ยวข้อง (หมวดเดียวกัน แต่ไม่ใช่ตัวเดียวกัน)
$related_sql = "SELECT productID, product_name, image, price 
                FROM product 
                WHERE category='{$product['category']}' 
                  AND productID != '$id' 
                LIMIT 4";
$related_result = $conn->query($related_sql);

$related_products = [];
if ($related_result && $related_result->num_rows > 0) {
    while ($row = $related_result->fetch_assoc()) {
        $related_products[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($product['product_name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  * {
    box-sizing: border-box;
  }

  body {
    font-family: 'Sarabun', sans-serif;
    background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96c93d, #f093fb, #f5576c);
    background-size: 400% 400%;
    animation: gradientShift 20s ease infinite;
    min-height: 100vh;
    margin: 0;
    overflow-x: hidden;
  }

  @keyframes gradientShift {
    0% { background-position: 0% 50%; }
    25% { background-position: 100% 50%; }
    50% { background-position: 100% 100%; }
    75% { background-position: 0% 100%; }
    100% { background-position: 0% 50%; }
  }

  /* Floating particles background */
  .particles {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: -1;
  }

  .particle {
    position: absolute;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); opacity: 1; }
    50% { transform: translateY(-20px) rotate(180deg); opacity: 0.8; }
  }

  .navbar {
    background: linear-gradient(135deg, #4CAF50, #2e7d32) !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding: 20px 0;
  }

  .navbar a { 
    color: white !important; 
    font-weight: 600; 
    font-size: 1.1rem;
    text-decoration: none;
    margin: 0 15px;
    position: relative;
    transition: all 0.3s ease;
  }

  .navbar a::before {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -5px;
    left: 50%;
    background: #ffeb3b;
    transition: all 0.3s ease;
    transform: translateX(-50%);
  }

  .navbar a:hover::before {
    width: 100%;
  }

  .navbar a:hover { 
    color: #ffeb3b !important;
    transform: translateY(-2px);
  }

  .main-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
  }

  .product-showcase {
    background: rgba(255,255,255,0.95);
    border-radius: 30px;
    box-shadow: 
      0 30px 60px rgba(0,0,0,0.2),
      0 0 0 1px rgba(255,255,255,0.1);
    padding: 50px;
    margin-top: 30px;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(20px);
    animation: slideUp 0.8s ease-out;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(50px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .product-showcase::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shimmer 3s ease-in-out infinite;
  }

  @keyframes shimmer {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
  }

  .product-image-container {
    position: relative;
    perspective: 1000px;
  }

  .product-image { 
    border-radius: 25px; 
    box-shadow: 
      0 25px 50px rgba(0,0,0,0.3),
      0 0 0 1px rgba(255,255,255,0.1);
    transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    width: 100%;
    height: auto;
    transform-style: preserve-3d;
  }

  .product-image:hover { 
    transform: rotateY(5deg) rotateX(5deg) scale(1.05);
    box-shadow: 0 35px 70px rgba(0,0,0,0.4);
  }

  .product-details {
    position: relative;
    z-index: 2;
  }

  .product-title {
    font-size: 3rem;
    font-weight: 700;
    background: linear-gradient(135deg, #2e7d32, #4CAF50, #66bb6a);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
    line-height: 1.2;
    animation: textGlow 2s ease-in-out infinite alternate;
  }

  @keyframes textGlow {
    from { text-shadow: 0 0 10px rgba(46, 125, 50, 0.3); }
    to { text-shadow: 0 0 20px rgba(46, 125, 50, 0.6); }
  }

  .category-badge {
    display: inline-block;
    background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 15px;
    box-shadow: 0 8px 16px rgba(255, 107, 107, 0.3);
    animation: bounce 2s ease-in-out infinite;
  }

  @keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-5px); }
    60% { transform: translateY(-3px); }
  }

  .detail-item {
    background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
    margin: 15px 0;
    padding: 20px;
    border-radius: 15px;
    border-left: 5px solid #4CAF50;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .detail-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.5s ease;
  }

  .detail-item:hover::before {
    left: 100%;
  }

  .detail-item:hover {
    transform: translateX(10px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.2);
  }

  .price-container {
    background: linear-gradient(135deg, #2e7d32, #4CAF50);
    color: white;
    padding: 25px;
    border-radius: 20px;
    text-align: center;
    margin: 25px 0;
    position: relative;
    overflow: hidden;
    animation: priceGlow 3s ease-in-out infinite;
  }

  @keyframes priceGlow {
    0%, 100% { box-shadow: 0 0 30px rgba(46, 125, 50, 0.5); }
    50% { box-shadow: 0 0 50px rgba(46, 125, 50, 0.8); }
  }

  .price {
    font-size: 3rem;
    font-weight: 800;
    margin: 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
  }

  .currency {
    font-size: 2rem;
    vertical-align: super;
  }

  .action-buttons {
    display: flex;
    gap: 20px;
    margin-top: 30px;
  }

  .btn-buy { 
    background: linear-gradient(135deg, #ff6f00, #ffb74d);
    color: white;
    font-size: 1.3rem;
    font-weight: 600;
    padding: 18px 35px;
    border-radius: 15px;
    border: none;
    transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    position: relative;
    overflow: hidden;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(255, 111, 0, 0.4);
  }

  .btn-buy::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
  }

  .btn-buy:hover::before {
    left: 100%;
  }

  .btn-buy:hover { 
    background: linear-gradient(135deg, #f57c00, #ffcc02);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 15px 35px rgba(255, 111, 0, 0.6);
    color: white;
  }

  .btn-back { 
    background: linear-gradient(135deg, #90a4ae, #b0bec5);
    color: white;
    font-size: 1.3rem;
    font-weight: 600;
    padding: 18px 35px;
    border-radius: 15px;
    border: none;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(144, 164, 174, 0.4);
  }

  .btn-back:hover { 
    background: linear-gradient(135deg, #78909c, #90a4ae);
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(144, 164, 174, 0.6);
    color: white;
  }

  .related-section {
    margin-top: 80px;
    position: relative;
  }

  .section-title {
    font-size: 2.5rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 50px;
    background: linear-gradient(135deg, #2e7d32, #4CAF50);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
  }

  .section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(135deg, #ff6b6b, #4ecdc4);
    border-radius: 2px;
  }

  .related-card { 
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    cursor: pointer;
    background: rgba(255,255,255,0.9);
    border-radius: 20px;
    overflow: hidden;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
  }

  .related-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, transparent, rgba(76, 175, 80, 0.1));
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1;
  }

  .related-card:hover::before {
    opacity: 1;
  }

  .related-card:hover { 
    transform: translateY(-15px) scale(1.05);
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
  }

  .related-card img { 
    border-radius: 15px 15px 0 0;
    object-fit: cover;
    height: 200px;
    width: 100%;
    transition: transform 0.4s ease;
  }

  .related-card:hover img {
    transform: scale(1.1);
  }

  .related-card .card-body {
    padding: 20px;
    position: relative;
    z-index: 2;
  }

  .related-card h6 {
    font-weight: 600;
    color: #2e7d32;
    margin-bottom: 10px;
  }

  .related-card .text-success {
    font-size: 1.2rem;
    font-weight: 700;
    color: #4CAF50 !important;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .product-showcase {
      padding: 30px 20px;
    }

    .product-title {
      font-size: 2rem;
    }

    .price {
      font-size: 2rem;
    }

    .action-buttons {
      flex-direction: column;
    }

    .btn-buy, .btn-back {
      width: 100%;
    }
  }

  /* Loading animation */
  .loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.9);
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

  .spinner {
    width: 60px;
    height: 60px;
    border: 6px solid #f3f3f3;
    border-top: 6px solid #4CAF50;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
  <div class="spinner"></div>
</div>

<!-- Floating Particles -->
<div class="particles" id="particles"></div>

<nav class="navbar navbar-expand-lg px-5">
  <div class="container-fluid">
    <a class="navbar-brand" href="showProduct.php">
      <i class="fas fa-store"></i> หน้าร้านค้า
    </a>
    <div class="navbar-nav ms-auto">
      <a class="nav-link" href="cart.php">
        <i class="fas fa-shopping-cart"></i> ตะกร้าสินค้า
      </a>
      <a class="nav-link" href="myOrders.php">
        <i class="fas fa-shopping-bag"></i> การสั่งซื้อ
      </a>
      <a class="nav-link" href="show_Profile.php">
        <i class="fas fa-user"></i> โปรไฟล์
      </a>
      <a class="nav-link" href="logout.php">
        <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
      </a>
    </div>
  </div>
</nav>

<div class="main-container">
  <div class="product-showcase">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6">
        <div class="product-image-container">
          <img src="gallery_products/<?php echo $product['image'] ?: 'default.png'; ?>" 
               class="product-image" 
               alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        </div>
      </div>
      <div class="col-lg-6">
        <div class="product-details">
          <h1 class="product-title"><?php echo htmlspecialchars($product['product_name']); ?></h1>
          
          <div class="category-badge">
            <i class="fas fa-tag"></i> หมวดหมู่: <?php echo htmlspecialchars($product['category']); ?>
          </div>
          
          <div class="detail-item">
            <strong><i class="fas fa-map-marker-alt text-primary"></i> แหล่งผลิต:</strong> 
            <?php echo htmlspecialchars($product['origin']); ?>
          </div>
          
          <div class="detail-item">
            <strong><i class="fas fa-info-circle text-info"></i> รายละเอียด:</strong>
            <div class="mt-2"><?php echo nl2br(htmlspecialchars($product['details'])); ?></div>
          </div>
          
          <div class="price-container">
            <div class="price">
              <span class="currency">฿</span><?php echo number_format($product['price'], 2); ?>
            </div>
          </div>
          
          <div class="action-buttons">
            <a href="cart.php?action=add&id=<?php echo $product['productID']; ?>" 
               class="btn-buy flex-fill">
              <i class="fas fa-cart-shopping me-2"></i> หยิบใส่ตะกร้า
            </a>
            <a href="showProduct.php" class="btn-back flex-fill">
              <i class="fas fa-arrow-left me-2"></i> กลับไปหน้าสินค้า
            </a>
          </div>
        </div>
      </div>
    </div>

    <?php if(count($related_products) > 0): ?>
    <div class="related-section">
      <h2 class="section-title">
        <i class="fas fa-sparkles"></i> สินค้าแนะนำ (หมวด <?php echo htmlspecialchars($product['category']); ?>)
      </h2>
      <div class="row g-4">
        <?php foreach($related_products as $rp): ?>
        <div class="col-lg-3 col-md-6">
          <div class="card related-card h-100" 
               onclick="window.location.href='product_detail2.php?id=<?php echo $rp['productID']; ?>'">
            <img src="gallery_products/<?php echo $rp['image'] ?: 'default.png'; ?>" 
                 class="card-img-top" 
                 alt="<?php echo htmlspecialchars($rp['product_name']); ?>">
            <div class="card-body text-center">
              <h6 class="card-title"><?php echo htmlspecialchars($rp['product_name']); ?></h6>
              <p class="text-success">
                <i class="fas fa-tag"></i> ฿<?php echo number_format($rp['price'], 2); ?>
              </p>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Create floating particles
function createParticles() {
  const particlesContainer = document.getElementById('particles');
  const particleCount = 50;
  
  for (let i = 0; i < particleCount; i++) {
    const particle = document.createElement('div');
    particle.className = 'particle';
    
    const size = Math.random() * 6 + 2;
    const x = Math.random() * 100;
    const y = Math.random() * 100;
    const animationDelay = Math.random() * 6;
    
    particle.style.width = size + 'px';
    particle.style.height = size + 'px';
    particle.style.left = x + '%';
    particle.style.top = y + '%';
    particle.style.animationDelay = animationDelay + 's';
    
    particlesContainer.appendChild(particle);
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

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  createParticles();
  hideLoading();
  
  // Add smooth scrolling
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
  
  // Add click animation to buttons
  document.querySelectorAll('.btn-buy, .btn-back').forEach(button => {
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
});

// Add ripple animation keyframes
const style = document.createElement('style');
style.textContent = `
  @keyframes ripple {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);
</script>
</body>
</html>