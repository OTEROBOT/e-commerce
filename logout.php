<?php
session_start();
session_unset();
session_destroy();
header("Location: login_form.php?error=ออกจากระบบแล้ว");
exit();
?>
