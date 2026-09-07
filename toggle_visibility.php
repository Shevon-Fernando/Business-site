<?php
/**
 * noontech - Category Visibility Toggle API Endpoint
 * Accepts a POST request with {id, is_visible} and flips the is_visible flag.
 * Protected: admin-only session check.
 */
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

// Guard: only admin may call this
$is_admin = (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'admin@noontech.com');
if (!$is_admin) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? intval($input['id']) : 0;
$is_visible = isset($input['is_visible']) ? intval($input['is_visible']) : 0;

// Clamp to 0 or 1
$is_visible = $is_visible ? 1 : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

if (!isset($conn) || $db_connection_error) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Database unavailable']);
    exit;
}

$stmt = $conn->prepare("UPDATE `graphic_design_categories` SET `is_visible` = ? WHERE `id` = ?");
$stmt->bind_param("ii", $is_visible, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $id, 'is_visible' => $is_visible]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$stmt->close();
?>