<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar">
  <ul>
    <li><a href="showProduct.php">รายการสินค้า</a></li>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="myOrders.php">การสั่งซื้อ</a></li>
        <li><a href="cart.php">ตะกร้า</a></li>
        <li><a href="show_profile.php">โปรไฟล์</a></li>
        <li><a href="logout.php">ออกจากระบบ</a></li>
    <?php else: ?>
        <li><a href="login_form.php?redirect=cart.php">ตะกร้า</a></li>
        <li><a href="login_form.php?redirect=myOrders.php">การสั่งซื้อ</a></li>
        <li><a href="login_form.php">เข้าสู่ระบบ</a></li>
        <li><a href="register_form.php">สมัครสมาชิก</a></li>
    <?php endif; ?>
  </ul>
</nav>

<style>
.navbar {
    background-color: #4CAF50;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.navbar ul {
    list-style: none;
    display: flex;
    gap: 15px;
    margin: 0;
    padding: 0;
}
.navbar ul li a {
    color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 18px;
            margin-right: 20px;
}
.navbar ul li a:hover {
    color: #e0e0e0;
}
</style>
