<?php
include "check_session.php";
include "conn.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES['profile_image'])) {
    $user_id = $_SESSION['user_id'];

    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir("profile_images")) {
        mkdir("profile_images", 0777, true);
    }

    $target_dir = "profile_images/";
    $file_name = basename($_FILES["profile_image"]["name"]);
    $file_tmp = $_FILES["profile_image"]["tmp_name"];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_name = "user_" . $user_id . "_" . time() . "." . $ext;
    $target_file = $target_dir . $new_name;

    $allowed_types = ["jpg", "jpeg", "png", "gif"];

    if (in_array($ext, $allowed_types)) {
        if (move_uploaded_file($file_tmp, $target_file)) {
            // บันทึกชื่อไฟล์ลง DB
            $sql = "UPDATE customer SET profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_name, $user_id);
            $stmt->execute();
            header("Location: show_profile.php?edit=true&msg=อัปโหลดรูปเรียบร้อยแล้ว");
            exit();
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปโหลด";
        }
    } else {
        $error = "อนุญาตเฉพาะไฟล์ jpg, jpeg, png, gif เท่านั้น";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อัปโหลดรูปโปรไฟล์</title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        h2 {
            color: #333333;
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #ddd;
            margin-bottom: 20px;
            display: none;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
            margin-bottom: 20px;
        }

        input[type="file"] {
            padding: 10px;
            font-size: 14px;
            color: #666;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
        }

        input[type="file"]::-webkit-file-upload-button {
            background-color: #e0f2f1;
            color: #00796b;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background-color: #b2dfdb;
        }

        button {
            padding: 12px 24px;
            background-color: #4caf50;
            color: white;
            font-size: 15px;
            font-weight: 500;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            font-size: 14px;
            text-decoration: none;
            color: #2196f3;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .error-msg {
            color: #d32f2f;
            font-size: 14px;
            margin-bottom: 15px;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 8px;
        }

        @media screen and (max-width: 500px) {
            .container {
                padding: 20px;
                border-radius: 12px;
            }
        }
    </style>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>อัปโหลดรูปโปรไฟล์</h2>
        <?php if (isset($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <img id="preview" class="preview" alt="Preview">
            <div class="file-input-wrapper">
                <input type="file" name="profile_image" accept="image/*" required onchange="previewImage(this)">
            </div>
            <button type="submit">อัปโหลด</button>
        </form>
        <a href="show_profile.php?edit=true" class="back-link">← กลับไปหน้าแก้ไขข้อมูล</a>
    </div>
</body>
</html>