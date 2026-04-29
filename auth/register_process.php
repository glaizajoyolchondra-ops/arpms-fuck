<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $department_name = trim($_POST['department']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        header("Location: ../index.php?error=password_mismatch");
        exit();
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: ../index.php?error=email_exists");
        exit();
    }

    // Handle department
    $department_id = null;
    if (!empty($department_name)) {
        $stmt = $pdo->prepare("SELECT department_id FROM departments WHERE department_name = ?");
        $stmt->execute([$department_name]);
        $department_id = $stmt->fetchColumn();

        if (!$department_id) {
            $stmt = $pdo->prepare("INSERT INTO departments (department_name) VALUES (?)");
            $stmt->execute([$department_name]);
            $department_id = $pdo->lastInsertId();
        }
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, role, department_id, password, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$full_name, $email, $role, $department_id, $hashed_password]);

    header("Location: ../index.php?success=1");
    exit();
}
?>
