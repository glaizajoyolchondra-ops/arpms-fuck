<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$project_id = intval($_GET['id'] ?? 0);

if ($project_id) {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE project_id = ? ORDER BY upload_date DESC LIMIT 1");
    $stmt->execute([$project_id]);
    $doc = $stmt->fetch();

    if ($doc && file_exists('../' . $doc['file_path'])) {
        $file = '../' . $doc['file_path'];
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($doc['file_name']).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        // Fallback if no file uploaded
        echo "<script>alert('No proposal document found for this project.'); history.back();</script>";
    }
}
?>
