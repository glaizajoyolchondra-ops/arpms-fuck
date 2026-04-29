<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $role = $_POST['role'] ?? null;

    if (!$user_id || !$role) {
        echo json_encode(['success' => false, 'message' => 'Missing data']);
        exit();
    }

    // Validate role
    $allowed_roles = ['admin', 'coordinator', 'researcher'];
    if (!in_array($role, $allowed_roles)) {
        echo json_encode(['success' => false, 'message' => 'Invalid role']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->execute([$role, $user_id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
