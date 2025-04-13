<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Adjust for production
error_reporting(0);
require 'db.php';

try {
    // Validate database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT id, naziv, kilometri, godina, cijena, vlasnik, opis, slike FROM cars");
    
    if (!$stmt || !$stmt->execute()) {
        throw new Exception("Query error: " . $conn->error);
    }

    $result = $stmt->get_result();
    $response = [];

    while ($row = $result->fetch_assoc()) {
        // Add error handling for JSON decoding
        $slike = [];
        if (!empty($row['slike'])) {
            $decoded = json_decode($row['slike'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $slike = $decoded;
            } else {
                error_log("Invalid JSON in images for car ID {$row['id']}: " . json_last_error_msg());
            }
        }
        $row['slike'] = $slike;
        $response[] = $row;
    }

    echo json_encode($response, JSON_UNESCAPED_SLASHES);

} catch(Exception $e) {
    http_response_code(500);
    error_log($e->getMessage()); // Log error without exposing details
    echo json_encode(["error" => "Failed to retrieve car listings"]);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>