<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>เข้าสู่ระบบ</title>
  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background-color: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .form-container {
      background-color: #ffffff;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      position: relative;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 8px;
      border: 1px solid #ccc;
    }
    button {
      width: 100%;
      padding: 12px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
    }
    .error-popup {
      position: absolute;
      top: -50px;
      left: 0;
      right: 0;
      background-color: #ffdddd;
      color: #b30000;
      border: 1px solid #ffaaaa;
      padding: 10px;
      border-radius: 8px;
      text-align: center;
      font-size: 14px;
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
  </style>
</head>
<body>
<div class="form-container">
  <?php if (isset($_GET['error'])): ?>
    <div class="error-popup"><?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>
  <h2>เข้าสู่ระบบ</h2>
  <form method="POST" action="check_login.php">
    <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
    <input type="password" name="password" placeholder="รหัสผ่าน" required>
    <button type="submit">เข้าสู่ระบบ</button>
  </form>
  <div class="register-link">
    ยังไม่มีบัญชี? <a href="register_form.php">สมัครสมาชิก</a>
  </div>
</div>
</body>
</html>
