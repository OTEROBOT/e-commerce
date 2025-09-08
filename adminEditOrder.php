<?php
include "check_session.php";
include "conn.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: show_profile.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// ถ้ามีการส่งฟอร์มแก้ไข
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_price = $_POST['total_price'];
    $order_date = $_POST['order_date'];

    $sql = "UPDATE orders SET total_price=?, order_date=? WHERE order_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dsi", $total_price, $order_date, $order_id);

    if ($stmt->execute()) {
        header("Location: adminOrders.php?success=" . urlencode("แก้ไขคำสั่งซื้อเรียบร้อยแล้ว"));
        exit();
    } else {
        $error = "เกิดข้อผิดพลาดในการแก้ไข";
    }
}

// ดึงข้อมูลคำสั่งซื้อมาแสดง
$sql = "SELECT * FROM orders WHERE order_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("ไม่พบคำสั่งซื้อที่เลือก");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขคำสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">✏️ แก้ไขคำสั่งซื้อ #<?php echo $order_id; ?></h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">วันที่สั่งซื้อ</label>
                <input type="datetime-local" name="order_date" class="form-control" 
                       value="<?php echo date('Y-m-d\TH:i', strtotime($order['order_date'])); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">ราคารวม</label>
                <input type="number" step="0.01" name="total_price" class="form-control" 
                       value="<?php echo $order['total_price']; ?>" required>
            </div>
            <button type="submit" class="btn btn-success">บันทึกการแก้ไข</button>
            <a href="adminOrders.php" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
</body>
</html>
