<?php
// adminOrders.php
include "check_session.php";
include "conn.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: show_profile.php?error=" . urlencode("คุณไม่มีสิทธิ์เข้าถึงหน้านี้"));
    exit();
}

// การค้นหาและตัวกรอง
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// SQL ดึงรายการคำสั่งซื้อ
$sql = "SELECT o.order_id, o.order_date, o.total_price, c.username, c.name 
        FROM orders o
        JOIN customer c ON o.customer_id = c.id
        WHERE 1";

if ($search !== '') {
    $sql .= " AND (c.username LIKE '%$search%' OR c.name LIKE '%$search%' OR o.order_id LIKE '%$search%')";
}

if ($filter === 'today') {
    $sql .= " AND DATE(o.order_date) = CURDATE()";
} elseif ($filter === 'week') {
    $sql .= " AND YEARWEEK(o.order_date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter === 'month') {
    $sql .= " AND MONTH(o.order_date) = MONTH(CURDATE()) AND YEAR(o.order_date) = YEAR(CURDATE())";
}

$sql .= " ORDER BY o.order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการคำสั่งซื้อ | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* พื้นหลัง Gradient เคลื่อนไหว */
        body {
            background: linear-gradient(-45deg, #f9d976, #f39f86, #a18cd1, #fbc2eb);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
            font-family: "Prompt", sans-serif;
        }
        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        h2 {
            font-weight: bold;
            color: #212529;
            text-shadow: 1px 1px 2px #ffffff80;
        }

        .navbar-brand {
            font-size: 1.4rem;
        }

        .card, .table {
            border-radius: 16px;
        }

        table tbody tr {
            transition: transform 0.15s ease, background-color 0.3s ease;
        }
        table tbody tr:hover {
            background-color: #f1f5f9;
            transform: scale(1.01);
        }

        .btn {
            border-radius: 8px;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: scale(1.08);
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="admin_profile.php">🛒 Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarNav" aria-controls="navbarNav" 
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_profile.php">โปรไฟล์</a></li>
                    <li class="nav-item"><a class="nav-link" href="product_list.php">ลิสสินค้า</a></li>
                    <li class="nav-item"><a class="nav-link" href="showmember.php">จัดการสมาชิก</a></li>
                    <li class="nav-item"><a class="nav-link active" href="adminOrders.php">รายการคำสั่งซื้อ</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">ออกจากระบบ</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- เนื้อหา -->
    <div class="container py-5">
        <h2 class="mb-3 text-center">📦 รายการคำสั่งซื้อ</h2>
        <p class="text-center text-dark mb-4">แอดมินสามารถดูและจัดการคำสั่งซื้อทั้งหมดของลูกค้าได้ที่นี่</p>

        <!-- ฟอร์มค้นหาและกรอง -->
        <form class="row mb-4 justify-content-center" method="GET" action="">
            <div class="col-md-4 mb-2">
                <input type="text" name="search" class="form-control shadow-sm" 
                       placeholder="🔍 ค้นหาชื่อผู้ใช้ / ชื่อลูกค้า / หมายเลขคำสั่งซื้อ" 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3 mb-2">
                <select name="filter" class="form-select shadow-sm">
                    <option value="">-- ตัวกรองทั้งหมด --</option>
                    <option value="today" <?php if($filter=='today') echo 'selected'; ?>>วันนี้</option>
                    <option value="week" <?php if($filter=='week') echo 'selected'; ?>>สัปดาห์นี้</option>
                    <option value="month" <?php if($filter=='month') echo 'selected'; ?>>เดือนนี้</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
            </div>
            <div class="col-md-2 mb-2">
                <a href="adminOrders.php" class="btn btn-secondary w-100">ดูทั้งหมด</a>
            </div>
        </form>

        <!-- ตาราง -->
        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive shadow-lg">
                <table class="table table-bordered table-hover bg-white text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>หมายเลขคำสั่งซื้อ</th>
                            <th>ลูกค้า</th>
                            <th>วันที่สั่งซื้อ</th>
                            <th>ราคารวม</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <a href="adminViewOrder.php?order_id=<?php echo $row['order_id']; ?>" 
                                       class="text-decoration-none fw-bold text-primary">
                                        #<?php echo $row['order_id']; ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($row['username']) . " (" . htmlspecialchars($row['name']) . ")"; ?></td>
                                <td><?php echo $row['order_date']; ?></td>
                                <td>฿<?php echo number_format($row['total_price'], 2); ?></td>
                                <td>
                                    <a href="adminEditOrder.php?order_id=<?php echo $row['order_id']; ?>" 
                                       class="btn btn-sm btn-warning">แก้ไข</a>
                                    <a href="adminDeleteOrder.php?order_id=<?php echo $row['order_id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบคำสั่งซื้อนี้?');">ลบ</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center">
                <a href="admin_profile.php" class="btn btn-secondary mt-3">⬅️ กลับไปโปรไฟล์แอดมิน</a>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center shadow-sm">❌ ยังไม่มีคำสั่งซื้อ</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
