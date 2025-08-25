<?php
include "conn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productID'])) {
    $productID = $_POST['productID'];
    $sql = "SELECT productID FROM product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $productID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo 'exists';
    } else {
        echo 'available';
    }

    $stmt->close();
}

$conn->close();
?>