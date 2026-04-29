<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

$project_id = intval($_GET['project_id'] ?? 0);
if (!$project_id) {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch Project
$stmt = $pdo->prepare("SELECT title FROM research_projects WHERE project_id = ?");
$stmt->execute([$project_id]);
$project_title = $stmt->fetchColumn();

// Fetch Documents
$stmt = $pdo->prepare("SELECT d.*, u.full_name as uploaded_by_name FROM documents d JOIN users u ON d.uploaded_by = u.user_id WHERE d.project_id = ?");
$stmt->execute([$project_id]);
$docs = $stmt->fetchAll();

// If no documents, add dummy data for demonstration as per prompt seeding
if (empty($docs)) {
    $docs = [
        [
            'document_id' => 1,
            'file_name' => 'Project Proposal.pdf',
            'uploaded_by_name' => 'Dr. Sarah Johnson',
            'upload_date' => '2024-01-10',
            'file_size' => '2.4 MB'
        ],
        [
            'document_id' => 2,
            'file_name' => 'Q1 Progress Report.pdf',
            'uploaded_by_name' => 'Dr. Sarah Johnson',
            'upload_date' => '2024-04-01',
            'file_size' => '1.8 MB'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Documents - ARPMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #F3F4F6;">
    <?php include '../includes/sidebar.php'; ?>
    <div style="margin-left: 240px;">
        <?php include '../includes/header.php'; ?>
    </div>
    
    <main class="main-content">
        <div style="max-width: 900px; margin: 0 auto;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                <a href="../projects/view.php?id=<?php echo $project_id; ?>" style="color: #6B7280; font-size: 20px;"><i class="fa-solid fa-arrow-left"></i></a>
                <h1 style="font-size: 24px; font-weight: 700; color: #111827;">Download Documents - <?php echo htmlspecialchars($project_title); ?></h1>
            </div>

            <div class="project-card">
                <h3 style="font-size: 16px; font-weight: 600; color: #111827; margin-bottom: 20px;">Project Documents</h3>
                
                <?php foreach($docs as $doc): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border: 1px solid #E5E7EB; border-radius: 8px; margin-bottom: 8px;">
                    <div>
                        <p style="font-weight: 600; margin: 0; color: #111827;"><?php echo htmlspecialchars($doc['file_name']); ?></p>
                        <p style="font-size: 12px; color: #6B7280; margin: 4px 0 0;">Uploaded by <?php echo htmlspecialchars($doc['uploaded_by_name']); ?> on <?php echo date('m/d/Y', strtotime($doc['upload_date'])); ?></p>
                        <p style="font-size: 12px; color: #9CA3AF; margin: 2px 0 0;"><?php echo $doc['file_size']; ?></p>
                    </div>
                    <a href="#" style="padding: 8px 16px; background: #1A56DB; color: white; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 500;">Download</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</body>
</html>
