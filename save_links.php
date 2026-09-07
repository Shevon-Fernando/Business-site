<?php
session_start();
include 'includes/db.php';

// Check authorization
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'admin@noontech.com') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $category_id = isset($data['category_id']) ? intval($data['category_id']) : 0;
    $links = isset($data['links']) ? $data['links'] : [];

    if ($category_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid category ID. You must create the category before saving links.']);
        exit;
    }

    if ($conn) {
        // Clear existing links for a fresh update
        $del_stmt = $conn->prepare("DELETE FROM image_links WHERE category_id = ?");
        $del_stmt->bind_param("i", $category_id);
        $del_stmt->execute();
        $del_stmt->close();

        $count = 0;
        if (!empty($links)) {
            $stmt = $conn->prepare("INSERT INTO image_links (category_id, link_url) VALUES (?, ?)");
            foreach ($links as $link) {
                $clean = trim($link);
                if (!empty($clean)) {
                    $stmt->bind_param("is", $category_id, $clean);
                    if ($stmt->execute())
                        $count++;
                }
            }
            $stmt->close();
        }
        echo json_encode(['success' => true, 'message' => "$count links updated successfully."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    }
}
?>