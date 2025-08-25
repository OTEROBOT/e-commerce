<?php
header('Content-Type: application/json');
include "conn.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ✅ query หลัก
$sql = "SELECT productID, product_name, origin, price, image 
        FROM product 
        WHERE (product_name LIKE ? OR details LIKE ?)";
$params = ["%$search%", "%$search%"];
$types = "ss";

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $row['image_exists'] = !empty($row['image']) && file_exists("gallery_products/" . $row['image']);
    $row['product_name'] = htmlspecialchars($row['product_name']);
    $row['origin'] = htmlspecialchars($row['origin']);
    $products[] = $row;
}

// ✅ นับจำนวนสินค้าทั้งหมดเพื่อเช็ค hasMore
$count_sql = "SELECT COUNT(*) as total FROM product WHERE (product_name LIKE ? OR details LIKE ?)";
$count_params = ["%$search%", "%$search%"];
$count_types = "ss";

if (!empty($category)) {
    $count_sql .= " AND category = ?";
    $count_params[] = $category;
    $count_types .= "s";
}

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($count_types, ...$count_params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total = $count_result->fetch_assoc()['total'] ?? 0;

$hasMore = ($page * $limit) < $total;

echo json_encode([
    'products' => $products,
    'hasMore' => $hasMore
]);

$stmt->close();
$count_stmt->close();
$conn->close();
