<?php
header('Content-Type: application/json');
error_reporting(0);
include 'db.php';

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
$stmt->bind_param("i", $id);

$response = [];
if($stmt->execute()) {
    $response["success"] = true;
} else {
    $response["error"] = $conn->error;
}

$stmt->close();
$conn->close();
exit(json_encode($response));
?>