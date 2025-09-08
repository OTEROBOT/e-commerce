<?php
// adminViewOrder.php
include "check_session.php";
include "conn.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: show_profile.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    echo "❌ ไม่พบหมายเลขคำสั่งซื้อ";
    exit;
}

$order_id = intval($_GET['order_id']);

// ------------------ ดึงข้อมูล order ------------------
$sqlOrder = "SELECT o.*, c.username, c.name 
             FROM orders o
             JOIN customer c ON o.customer_id = c.id
             WHERE o.order_id = ?";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("i", $order_id);
$stmtOrder->execute();
$resultOrder = $stmtOrder->get_result();

if ($resultOrder->num_rows == 0) {
    echo "❌ ไม่พบข้อมูลคำสั่งซื้อ";
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
    <title>รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?> (Admin)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* พื้นหลัง Gradient Animated */
        body {
            min-height: 100vh;
            background: linear-gradient(270deg, #6a11cb, #2575fc, #ff6a00, #ff0084);
            background-size: 800% 800%;
            animation: gradientBG 20s ease infinite;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        /* กล่องโปร่งใสแบบ Glass Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
            color: #fff;
        }

        h2 {
            font-weight: bold;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
        }

        table {
            border-radius: 15px;
            overflow: hidden;
        }

        .table thead {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        .btn-custom {
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-custom:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .total-box {
            background: rgba(0,0,0,0.4);
            border-radius: 15px;
            padding: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            text-align: right;
        }

        .fade-in {
            animation: fadeInUp 1.2s ease;
        }

        @keyframes fadeInUp {
            0% {opacity: 0; transform: translateY(30px);}
            100% {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>
    <div class="container glass-card fade-in">
        <h2 class="text-center mb-4">📦 รายละเอียดคำสั่งซื้อ #<?php echo $order_id; ?></h2>

        <div class="mb-4">
            <p><strong>👤 ลูกค้า:</strong> <?php echo htmlspecialchars($order['username']) . " (" . htmlspecialchars($order['name']) . ")"; ?></p>
            <p><strong>📦 ผู้รับ:</strong> <?php echo htmlspecialchars($order['recipient_name']); ?></p>
            <p><strong>🏠 ที่อยู่จัดส่ง:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
            <p><strong>🕒 วันที่สั่งซื้อ:</strong> <?php echo $order['order_date']; ?></p>
        </div>

        <table class="table table-bordered table-hover text-center text-white">
            <thead>
                <tr>
                    <th>รหัสสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>ราคา (บาท)</th>
                    <th>จำนวน</th>
                    <th>รวม</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $resultDetails->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $item['product_id']; ?></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="total-box mt-3">
            💰 ราคารวมทั้งหมด: <?php echo number_format($order['total_price'], 2); ?> บาท
        </div>

        <div class="text-center mt-4">
            <a href="adminOrders.php" class="btn btn-custom btn-light">⬅️ กลับไปหน้ารายการคำสั่งซื้อ</a>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
