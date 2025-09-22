<?php
include "conn.php";
include "check_session.php";

header('Content-Type: application/json');

if (!$_SESSION['is_admin']) {
    echo json_encode(['error' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้']);
    exit();
}

if (!isset($_POST['productID']) || empty($_POST['productID'])) {
    echo json_encode(['error' => 'รหัสสินค้าไม่ถูกต้อง']);
    exit();
}

$productID = mysqli_real_escape_string($conn, $_POST['productID']);

try {
    $sql = "SELECT productID, product_name, origin, price, details, image FROM product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("s", $productID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_path = !empty($row['image']) && file_exists("gallery_products/" . $row['image'])
            ? "gallery_products/" . htmlspecialchars($row['image'])
            : "gallery_products/default.png";

        $response = [
            'productID' => $row['productID'],
            'product_name' => htmlspecialchars($row['product_name']),
            'origin' => htmlspecialchars($row['origin']),
            'price' => number_format($row['price'], 2),
            'details' => htmlspecialchars($row['details']),
            'image' => $image_path
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'ไม่พบสินค้า']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>