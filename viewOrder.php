<?php
// viewOrder.php
// แสดงรายละเอียดคำสั่งซื้อเฉพาะรายการของลูกค้าที่ล็อกอินอยู่ในถานะAdmin
include "check_session.php";
include "conn.php";

// ตรวจสอบว่ามี order_id ส่งมาหรือไม่
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "❌ ไม่พบหมายเลขคำสั่งซื้อ";
    exit;
}

$order_id = intval($_GET['order_id']);

// ------------------ ดึงข้อมูล order ------------------
$sqlOrder = "SELECT * FROM orders WHERE order_id = ?";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("i", $order_id);
$stmtOrder->execute();
$resultOrder = $stmtOrder->get_result();

if ($resultOrder->num_rows == 0) {
    echo "ไม่พบข้อมูลคำสั่งซื้อ";
    exit;
}

$order = $resultOrder->fetch_assoc();

// ------------------ ดึงรายละเอียดสินค้า ------------------
$sqlDetails = "SELECT * FROM order_details WHERE order_id = ?";
$stmtDetail = $conn->prepare($sqlDetails);
$stmtDetail->bind_param("i", $order_id);
$stmtDetail->execute();
$resultDetails = $stmtDetail->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?></title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, sans-serif;
            padding: 20px;
            margin: 0;
            background: #f9fafb;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        h2 {
            color: #2563eb;
            margin-bottom: 10px;
        }
        h3 {
            margin-top: 25px;
            color: #1d4ed8;
        }
        .info p {
            margin: 6px 0;
            font-size: 1rem;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
            border-radius: 10px;
            overflow: hidden;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        table th {
            background: #2563eb;
            color: white;
        }
        table tr:nth-child(even) {
            background: #f3f4f6;
        }
        .summary {
            margin-top: 20px;
            font-size: 1.2rem;
            text-align: right;
            font-weight: bold;
            color: #111827;
        }
        a.btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            border-radius: 8px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            transition: 0.2s;
        }
        a.btn:hover {
            background: #1e40af;
        }
        /* Responsive */
        @media (max-width: 600px) {
            body { padding: 10px; }
            table th, table td { font-size: 0.85rem; padding: 6px; }
            .summary { font-size: 1rem; }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📦 รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?></h2>

    <div class="info">
        <p><strong>ลูกค้า:</strong> <?php echo htmlspecialchars($order['recipient_name']); ?></p>
        <p><strong>ที่อยู่จัดส่ง:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
        <p><strong>วันที่สั่งซื้อ:</strong> <?php echo $order['order_date']; ?></p>
    </div>

    <h3>🛒 รายการสินค้า</h3>
    <table>
        <tr>
            <th>รหัสสินค้า</th>
            <th>ชื่อสินค้า</th>
            <th>ราคา (บาท)</th>
            <th>จำนวน</th>
            <th>รวม</th>
        </tr>
        <?php while ($item = $resultDetails->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $item['product_id']; ?></td>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td><?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
        <?php } ?>
    </table>

    <div class="summary">
        ราคารวมทั้งหมด: <?php echo number_format($order['total_price'], 2); ?> บาท
    </div>

    <a href="myOrders.php" class="btn">⬅️ กลับไปหน้าคำสั่งซื้อของฉัน</a>
</div>

</body>
</html>

<?php
$conn->close();
?>
