<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ฟอร์มสมัครสมาชิก</title>
    <link rel="stylesheet" href="css_form.css">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            padding-top: 50px;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            position: relative;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="tel"],
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .popup-msg {
            position: absolute;
            top: -50px;
            left: 0;
            right: 0;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
        }

        .login-link {
            margin-top: 15px;
            text-align: center;
        }

        .login-link a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">

<?php
// แสดงข้อความ popup สวยงามถ้ามี msg
if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
    echo "<div class='popup-msg'>$msg</div>";
}
?>

<form action="register.php" method="post">
    <h2>ฟอร์มสมัครสมาชิก</h2>

    <label>Username:</label>
    <input type="text" name="username" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <label>ชื่อ - นามสกุล:</label>
    <input type="text" name="name" required>

    <label>อีเมล:</label>
    <input type="email" name="email" required>

    <label>เบอร์โทรศัพท์:</label>
    <input type="tel" name="mobile_phone" pattern="[0-9]{10}" required placeholder="ตัวอย่าง 0812345678">

    <label>ที่อยู่:</label>
    <textarea name="address" rows="3" required></textarea>

    <button type="submit">ส่งข้อมูลการสมัคร</button>
</form>

<div class="login-link">
    เป็นสมาชิกอยู่แล้ว? <a href="login_form.php">เข้าสู่ระบบ</a>
</div>

</div>

</body>
</html>
