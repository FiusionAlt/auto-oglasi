<?php
header('Content-Type: application/json');
error_reporting(0); // Completely disable error reporting
ini_set('display_errors', 0);
header("Access-Control-Allow-Origin: *");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auto_oglasi";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch(Exception $e) {
    http_response_code(500);
    die(json_encode(["error" => $e->getMessage()]));
}
?>