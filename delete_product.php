<?php
include "conn.php";
include "check_session.php";

header('Content-Type: application/json');

if (!$_SESSION['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้']);
    exit();
}

if (!isset($_POST['productID']) || empty($_POST['productID'])) {
    echo json_encode(['success' => false, 'message' => 'รหัสสินค้าไม่ถูกต้อง']);
    exit();
}

$productID = mysqli_real_escape_string($conn, $_POST['productID']);

$conn->begin_transaction();

try {
    $sql = "SELECT image FROM product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("s", $productID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image = $row['image'];

        $sql_delete = "DELETE FROM product WHERE productID = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        if (!$stmt_delete) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt_delete->bind_param("s", $productID);
        if ($stmt_delete->execute()) {
            if (!empty($image) && file_exists("gallery_products/" . $image) && $image !== 'default.png') {
                if (!unlink("gallery_products/" . $image)) {
                    throw new Exception("ไม่สามารถลบไฟล์รูปภาพได้");
                }
            }
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'ลบสินค้าสำเร็จ']);
        } else {
            throw new Exception("เกิดข้อผิดพลาดในการลบสินค้า: " . $conn->error);
        }
        $stmt_delete->close();
    } else {
        throw new Exception("ไม่พบสินค้าที่ต้องการลบ");
    }
    $stmt->close();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>