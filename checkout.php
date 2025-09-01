<?php
include "check_session.php";
include "conn.php";

// ดึง user_id และ username จาก session
$user_id   = $_SESSION['user_id'];
$username  = $_SESSION['username'];

// ตรวจสอบว่ามีข้อมูลตะกร้าและ form ถูกส่งมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['products'])) {

    // --- ดึงข้อมูลลูกค้าจากฐานข้อมูล ---
    $sqlCustomer = "SELECT * FROM customer WHERE id = ?";
    $stmtCus = $conn->prepare($sqlCustomer);
    $stmtCus->bind_param("i", $user_id);
    $stmtCus->execute();
    $resultCus = $stmtCus->get_result();

    if ($resultCus->num_rows == 0) {
        header("Location: login_form.php?error=" . urlencode("ไม่พบข้อมูลลูกค้า"));
        exit;
    }

    $customer = $resultCus->fetch_assoc();
    $recipient_name   = $customer['name'];
    $shipping_address = $customer['address'];

    // --- ราคารวม ---
    $total_price = $conn->real_escape_string($_POST['total']);
    $order_date  = date("Y-m-d H:i:s");

    // --- เริ่ม transaction ---
    $conn->begin_transaction();

    try {
        // 1) เพิ่มข้อมูลลงตาราง orders
        $sqlOrder = "INSERT INTO orders (customer_id, recipient_name, shipping_address, order_date, total_price)
                     VALUES (?, ?, ?, ?, ?)";
        $stmtOrder = $conn->prepare($sqlOrder);
        $stmtOrder->bind_param("isssd", $user_id, $recipient_name, $shipping_address, $order_date, $total_price);
        $stmtOrder->execute();
        $order_id = $stmtOrder->insert_id;

        // 2) เพิ่มข้อมูลสินค้าแต่ละชิ้นลง order_details
        $sqlDetail = "INSERT INTO order_details (order_id, product_id, product_name, price, quantity)
                      VALUES (?, ?, ?, ?, ?)";
        $stmtDetail = $conn->prepare($sqlDetail);

        foreach ($_POST['products'] as $product) {
            $product_id   = $product['productID'];
            $product_name = $product['name'];
            $price        = $product['price'];
            $qty          = $product['qty'];

            $stmtDetail->bind_param("issdi", $order_id, $product_id, $product_name, $price, $qty);
            $stmtDetail->execute();
        }

        // 3) commit ข้อมูลทั้งหมด
        $conn->commit();
        unset($_SESSION['cart']); // ล้างตะกร้า

        // ✅ แสดงผลลัพธ์สวยงาม
        echo "
        <!DOCTYPE html>
        <html lang='th'>
        <head>
            <meta charset='UTF-8'>
            <title>สั่งซื้อสำเร็จ</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light'>
        <div class='container py-5'>
            <div class='card shadow-lg p-4 text-center'>
                <h2 class='text-success'>✅ สั่งซื้อเรียบร้อยแล้ว</h2>
                <p>ขอบคุณคุณ <strong>" . htmlspecialchars($recipient_name) . "</strong> ที่สั่งซื้อกับเรา</p>
                <p>หมายเลขคำสั่งซื้อของคุณคือ 
                    <strong><a href='viewOrder.php?order_id={$order_id}'>#{$order_id}</a></strong>
                </p>
                <a href='showProduct.php' class='btn btn-primary mt-3'>🛒 เลือกซื้อสินค้าเพิ่ม</a>
            </div>
        </div>
        </body>
        </html>
        ";

    } catch (Exception $e) {
        $conn->rollback();
        echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
    }

} else {
    echo "คุณยังไม่ได้เลือกสินค้า <a href='showProduct.php'>เลือก</a>";
}

$conn->close();
?>
