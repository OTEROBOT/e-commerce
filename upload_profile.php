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
            header("Location: show_profile.php?msg=อัปโหลดรูปเรียบร้อยแล้ว");
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
        
            .container {
        background-color: #ffffff;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 420px;
        text-align: center;
    }

    h2 {
        margin-bottom: 20px;
        color: #333;
    }

    input[type="file"] {
        margin: 0 auto 20px;
    }

    img.preview {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 15px;
        border: 2px solid #ccc;
        display: none;
    }

    button {
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        font-size: 15px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }

    button:hover {
        background-color: #45a049;
    }

    .back-link {
        display: block;
        margin-top: 20px;
        font-size: 14px;
        text-decoration: none;
        color: #4CAF50;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .error-msg {
        color: #d32f2f;
        margin-bottom: 15px;
        font-size: 14px;
    }
</style>

        
        </style>
</head>
<body>
    <h2>อัปโหลดรูปโปรไฟล์</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form action="" method="post" enctype="multipart/form-data">
    <img id="preview" class="preview" alt="Preview">
    <input type="file" name="profile_image" accept="image/*" required onchange="previewImage(this)">
    <button type="submit">อัปโหลด</button>
</form>

<a href="show_profile.php" class="back-link">← กลับไปหน้าข้อมูลสมาชิก</a>

</body>
</html>
