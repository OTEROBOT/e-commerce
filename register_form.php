<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- เพิ่ม responsive -->
    <title>ฟอร์มสมัครสมาชิก</title>
    <link rel="stylesheet" href="css_form.css">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            padding-top: 50px;
            margin: 0;
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
            box-sizing: border-box;
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
            position: fixed; /* เปลี่ยนเป็น fixed เพื่อให้มองเห็นได้เสมอ */
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
            z-index: 1000;
            max-width: 90%;
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
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

        @media screen and (max-width: 480px) {
            .form-container {
                width: 90%;
            }
        }
    </style>
    <script>
        // ซ่อน popup หลัง 5 วินาที
        window.onload = function() {
            const msg = document.querySelector('.popup-msg');
            if (msg) {
                setTimeout(() => msg.style.display = 'none', 5000);
            }
        };
    </script>
</head>
<body>
<div class="form-container">
    <?php
    // แสดงข้อความ popup สวยงามถ้ามี msg
    if (isset($_GET['msg'])) {
        $msg = htmlspecialchars($_GET['msg']);
        $class = strpos($msg, '❌') === 0 ? 'popup-msg error-msg' : 'popup-msg';
        echo "<div class='$class'>$msg</div>";
    }
    ?>

    <form action="register.php" method="post">
        <h2>ฟอร์มสมัครสมาชิก</h2>

        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required minlength="6">

        <label>ชื่อ - นามสกุล:</label>
        <input type="text" name="name" required>

        <label>อีเมล:</label>
        <input type="email" name="email" required>

        <label>เบอร์โทรศัพท์:</label>
        <input type="tel" name="mobile_phone" pattern="[0-9]{10}" required placeholder="ตัวอย่าง 0812345678" title="กรุณากรอกเบอร์โทร 10 หลัก">

        <label>ที่อยู่:</label>
        <textarea name="address" rows="3" required></textarea>

        <label><input type="checkbox" name="is_admin" value="1"> ลงทะเบียนเป็นแอดมิน</label> <!-- เพิ่มตัวเลือก is_admin -->

        <button type="submit">ส่งข้อมูลการสมัคร</button>
    </form>

    <div class="login-link">
        เป็นสมาชิกอยู่แล้ว? <a href="login_form.php">เข้าสู่ระบบ</a>
    </div>
</div>
</body>
</html>