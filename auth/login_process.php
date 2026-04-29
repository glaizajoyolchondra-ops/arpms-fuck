<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Handle both hashed and plain text passwords as requested for testing robustness
        $password_correct = password_verify($password, $user['password']) || $password === $user['password'];

        if ($password_correct) {
            if ($user['status'] === 'pending') {
                header("Location: ../index.php?error=pending_approval");
                exit();
            }
            if ($user['status'] === 'inactive') {
                header("Location: ../index.php?error=account_inactive");
                exit();
            }
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            
            session_write_close(); // Ensure session is saved
            header("Location: ../dashboard.php");
            exit();
        } else {
            header("Location: ../index.php?error=invalid_credentials");
            exit();
        }
    } else {
        header("Location: ../index.php?error=invalid_credentials");
        exit();
    }
}
?>
