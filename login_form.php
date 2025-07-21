<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ</title>
  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96c93d);
      background-size: 400%;
      animation: colorShift 15s ease infinite;
    }

    @keyframes colorShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .form-container {
      background-color: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      position: relative;
      z-index: 1;
    }

    .shop-icon {
      display: block;
      margin: 0 auto 30px;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #4CAF50;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
      box-sizing: border-box;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background-color: #45a049;
    }

    /* เพิ่มสไตล์สำหรับป๊อปอัป */
    .popup {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 14px;
      text-align: center;
      z-index: 1000;
      max-width: 90%;
      animation: fadeOut 5s ease forwards; /* เอฟเฟกต์จางหายใน 5 วินาที */
    }

    .message {
      background-color: #e8f5e9;
      color: #2e7d32;
      border: 1px solid #a5d6a7;
    }

    .error {
      background-color: #ffdddd;
      color: #b30000;
      border: 1px solid #ffaaaa;
    }

    /* เพิ่มแอนิเมชันสำหรับจางหาย */
    @keyframes fadeOut {
      0% { opacity: 1; }
      80% { opacity: 1; }
      100% { opacity: 0; display: none; }
    }

    .register-link {
      text-align: center;
      margin-top: 20px;
    }

    .register-link a {
      text-decoration: none;
      color: #4CAF50;
      font-weight: bold;
    }

    .register-link a:hover {
      text-decoration: underline;
    }

    @media screen and (max-width: 480px) {
      .form-container {
        width: 90%;
      }
      /* ปรับขนาด .shop-icon สำหรับหน้าจอเล็ก */
      .shop-icon {
        width: 200px;
        height: 200px;
      }
    }
  </style>
</head>
<body>
<div class="form-container">
  <img src="images/shop-logo.png" alt="Shop Logo" class="shop-icon">
  <!-- แก้ไข: เปลี่ยน div.message และ div.error เป็น div.popup พร้อมคลาส message หรือ error -->
  <?php if (isset($_GET['message'])): ?>
    <div class="popup message"><?= htmlspecialchars(urldecode($_GET['message'])) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="popup error"><?= htmlspecialchars(urldecode($_GET['error'])) ?></div>
  <?php endif; ?>
  <h2>เข้าสู่ระบบ</h2>
  <form method="POST" action="check_login.php" id="loginForm">
    <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
    <input type="password" name="password" placeholder="รหัสผ่าน" required>
    <button type="submit">เข้าสู่ระบบ</button>
  </form>
  <div class="register-link">
    ยังไม่มีบัญชี? <a href="register_form.php">สมัครสมาชิก</a>
  </div>
</div>
<!-- ลบ JavaScript รีเซ็ตฟอร์มเพราะไม่จำเป็น และป๊อปอัปจัดการด้วย CSS -->
</body>
</html>