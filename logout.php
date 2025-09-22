<?php
session_start();

if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
}

header("Location: showProduct.php?message=" . urlencode("ออกจากระบบแล้ว"));
exit();
?>