<?php
header('Content-Type: application/json');
error_reporting(0);
include 'db.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        throw new Exception("Invalid JSON input");
    }

    // Validate required fields
    $required = ['id', 'naziv'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate and prepare images
    $slike = [];
    if (isset($data['slike']) && is_array($data['slike'])) {
        $slike = $data['slike'];
    }
    
    $slike = array_filter($slike, function($img) {
        return strpos($img, 'data:image/') === 0;
    });
    $slike_json = json_encode($slike, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare("UPDATE cars SET 
        naziv=?, kilometri=?, godina=?, cijena=?, vlasnik=?, opis=?, slike=?
        WHERE id=?");

    if (!$stmt) {
        throw new Exception("Prepare error: " . $conn->error);
    }

    $stmt->bind_param("ssissssi",
        $data['naziv'],
        $data['kilometri'],
        $data['godina'],
        $data['cijena'],
        $data['vlasnik'],
        $data['opis'],
        $slike_json,
        $data['id']
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute error: " . $stmt->error);
    }

    $response = [
        'success' => true,
        'affected_rows' => $stmt->affected_rows
    ];

} catch(Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Update failed: ' . $e->getMessage()
    ];
}

if(isset($stmt)) $stmt->close();
if(isset($conn)) $conn->close();

echo json_encode($response);
exit;
?>