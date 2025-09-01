<?php
include "check_session.php";
include "conn.php";

// ดึง user_id และ username จาก session
$user_id   = $_SESSION['user_id'];
$username  = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['products'])) {

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

    $total_price = $conn->real_escape_string($_POST['total']);
    $order_date  = date("Y-m-d H:i:s");

    $conn->begin_transaction();

    try {
        $sqlOrder = "INSERT INTO orders (customer_id, recipient_name, shipping_address, order_date, total_price)
                     VALUES (?, ?, ?, ?, ?)";
        $stmtOrder = $conn->prepare($sqlOrder);
        $stmtOrder->bind_param("isssd", $user_id, $recipient_name, $shipping_address, $order_date, $total_price);
        $stmtOrder->execute();
        $order_id = $stmtOrder->insert_id;

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

        $conn->commit();
        unset($_SESSION['cart']);

        // ✅ แสดงหน้าสำเร็จแบบโคตรสวย
        echo "
        <!DOCTYPE html>
        <html lang='th'>
        <head>
            <meta charset='UTF-8'>
            <title>สั่งซื้อสำเร็จ</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css' rel='stylesheet'>
            <style>
                body {
                    min-height: 100vh;
                    background: linear-gradient(135deg, #00c6ff, #0072ff, #43cea2, #185a9d);
                    background-size: 400% 400%;
                    animation: gradientShift 15s ease infinite;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                @keyframes gradientShift {
                    0% { background-position: 0% 50%; }
                    50% { background-position: 100% 50%; }
                    100% { background-position: 0% 50%; }
                }
                .success-card {
                    background: white;
                    border-radius: 20px;
                    padding: 40px;
                    max-width: 600px;
                    width: 100%;
                    text-align: center;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                    position: relative;
                    overflow: hidden;
                }
                .success-icon {
                    font-size: 80px;
                    color: #28a745;
                    margin-bottom: 20px;
                    animation: pop 0.8s ease;
                }
                @keyframes pop {
                    0% { transform: scale(0); opacity: 0; }
                    60% { transform: scale(1.2); opacity: 1; }
                    100% { transform: scale(1); }
                }
                h2 {
                    font-weight: bold;
                    color: #28a745;
                    margin-bottom: 15px;
                }
                p {
                    font-size: 18px;
                    color: #555;
                }
                .btn-custom {
                    background: linear-gradient(45deg, #28a745, #218838);
                    border: none;
                    border-radius: 50px;
                    padding: 12px 30px;
                    font-size: 18px;
                    color: white;
                    transition: 0.3s;
                }
                .btn-custom:hover {
                    background: linear-gradient(45deg, #218838, #1e7e34);
                    transform: translateY(-3px);
                    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
                }
                .order-link {
                    font-size: 20px;
                    font-weight: bold;
                    color: #0072ff;
                    text-decoration: none;
                }
                .order-link:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class='success-card'>
                <i class='bi bi-check-circle-fill success-icon'></i>
                <h2>สั่งซื้อสำเร็จ!</h2>
                <p>ขอบคุณคุณ <strong>" . htmlspecialchars($recipient_name) . "</strong> ที่ไว้วางใจสั่งซื้อกับเรา</p>
                <p>หมายเลขคำสั่งซื้อของคุณคือ 
                    <a href='viewOrder.php?order_id={$order_id}' class='order-link'>#{$order_id}</a>
                </p>
                <a href='showProduct.php' class='btn btn-custom mt-4'>🛒 เลือกซื้อสินค้าเพิ่ม</a>
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
