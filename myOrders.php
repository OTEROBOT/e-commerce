<?php
include "check_session.php";
include "conn.php";

$user_id = $_SESSION['user_id'];

// ดึงรายการสั่งซื้อของลูกค้าคนนี้
$sql = "SELECT order_id, order_date, total_price 
        FROM orders 
        WHERE customer_id = ? 
        ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คำสั่งซื้อของฉัน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">📦 คำสั่งซื้อของฉัน</h2>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-hover bg-white shadow-sm">
                <thead class="table-success">
                    <tr>
                        <th>หมายเลขคำสั่งซื้อ</th>
                        <th>วันที่สั่งซื้อ</th>
                        <th>ราคารวม</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['order_date']; ?></td>
                            <td>฿<?php echo number_format($row['total_price'], 2); ?></td>
                            <td>
                                <a href="viewOrder.php?order_id=<?php echo $row['order_id']; ?>" 
                                   class="btn btn-sm btn-primary">ดูรายละเอียด</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="showProduct.php" class="btn btn-secondary mt-3">⬅️ กลับไปเลือกซื้อสินค้า</a>
        <?php else: ?>
            <div class="alert alert-info">❌ คุณยังไม่มีคำสั่งซื้อ</div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php $stmt->close(); $conn->close(); ?>
