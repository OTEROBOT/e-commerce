<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>เข้าสู่ระบบ</title>
  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      background-color: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .form-container {
      background-color: #ffffff;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
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
    .message {
      background-color: #e8f5e9;
      color: #2e7d32;
      border: 1px solid #a5d6a7;
      padding: 10px;
      border-radius: 8px;
      text-align: center;
      font-size: 14px;
      margin-bottom: 15px;
    }
    .error {
      background-color: #ffdddd;
      color: #b30000;
      border: 1px solid #ffaaaa;
      padding: 10px;
      border-radius: 8px;
      text-align: center;
      font-size: 14px;
      margin-bottom: 15px;
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
  </style>
</head>
<body>
<div class="form-container">
  <?php if (isset($_GET['message'])): ?>
    <div class="message"><?= htmlspecialchars(urldecode($_GET['message'])) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="error"><?= htmlspecialchars(urldecode($_GET['error'])) ?></div>
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
<script>
  // ล้างข้อมูลฟอร์มเมื่อโหลดหน้า
  document.getElementById('loginForm').reset();
  setTimeout(() => {
    const message = document.querySelector('.message, .error');
    if (message) message.style.display = 'none';
}, 5000);
  // ป้องกันการส่งฟอร์มซ้ำเมื่อรีเฟรชหน้า
  window.addEventListener('beforeunload', () => {
    document.getElementById('loginForm').reset();
  });
  // ป้องกันการส่งฟอร์มซ้ำเมื่อกดปุ่มย้อนกลับ
  window.addEventListener('popstate', () => {
    document.getElementById('loginForm').reset();
  });
</script>
</body>
</html>