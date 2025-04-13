<?php
header('Content-Type: application/json');
error_reporting(0);
include 'db.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if(!$data) {
        throw new Exception("Invalid input data");
    }

    // Validate required fields
    if(empty($data['naziv'])) {
        throw new Exception("Naziv je obavezan");
    }

    $stmt = $conn->prepare("INSERT INTO cars (naziv, kilometri, godina, cijena, vlasnik, opis, slike) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    if(!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $slike = isset($data['slike']) && is_array($data['slike']) ? json_encode($data['slike'], JSON_UNESCAPED_SLASHES) : '[]';

    $bind = $stmt->bind_param("ssissss", 
        $data['naziv'],
        $data['kilometri'],
        $data['godina'],
        $data['cijena'],
        $data['vlasnik'],
        $data['opis'],
        $slike
    );

    if(!$bind) {
        throw new Exception("Bind failed: " . $stmt->error);
    }

    if($stmt->execute()) {
        $response = [
            'success' => true,
            'id' => $stmt->insert_id
        ];
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
} catch(Exception $e) {
    http_response_code(500);
    $response = [
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ];
}

if(isset($stmt)) $stmt->close();
if(isset($conn)) $conn->close();
echo json_encode($response);
exit;
?>