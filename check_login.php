<?php
// check_login.php
  session_start();
  include "conn.php";

  // ล้างเซสชันเก่าก่อนเริ่มใหม่
  session_unset();
  session_destroy();
  session_start();

  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if (empty($username) || empty($password)) {
      header("Location: login_form.php?error=" . urlencode("กรุณากรอกชื่อผู้ใช้และรหัสผ่าน"));
      exit();
  }

  $sql = "SELECT id, username, password, is_admin FROM customer WHERE username = ?";
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
      die("Prepare failed: " . $conn->error);
  }
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
      $user = $result->fetch_assoc();

      if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['is_admin'] = $user['is_admin'];
    $_SESSION['sess_id'] = session_id();
    $stmt->close();
    $result->close();

    // ✅ ถ้ามีการส่ง redirect มาจาก login_form.php ให้พากลับไปหน้าที่ตั้งใจเข้า
    if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
        header("Location: " . $_GET['redirect']);
        exit();
    }

    // ถ้าไม่มี redirect ให้แยกตามสิทธิ์
    if ($_SESSION['is_admin'] == 1) {
        header("Location: admin_profile.php");
    } else {
        header("Location: showProduct.php");
    }
    exit();
}

  } else {
      $stmt->close();
      $result->close();
      header("Location: login_form.php?error=" . urlencode("ไม่พบชื่อผู้ใช้"));
      exit();
  }
  ?>