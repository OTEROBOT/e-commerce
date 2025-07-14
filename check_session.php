<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login_form.php?error=กรุณาเข้าสู่ระบบก่อนใช้งาน");
    exit();
}
?>
