<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $dept_id = $_POST['department_id'];

    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, department_id = ? WHERE user_id = ?");
    $stmt->execute([$full_name, $email, $dept_id, $user_id]);
    $_SESSION['full_name'] = $full_name; // Update session
    header("Location: profile.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - ARPMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body style="background: #E5E7EB;">
    <?php include '../includes/header.php'; ?>

    <main class="dashboard-container" style="max-width: 600px;">
        <div class="view-card">
            <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 24px;">Edit Profile</h2>

            <form action="" method="POST">
                <div class="form-group-premium">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="input-premium" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>

                <div class="form-group-premium">
                    <label>Email Address</label>
                    <input type="email" name="email" class="input-premium" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group-premium">
                    <label>Department</label>
                    <select name="department_id" class="select-premium" required>
                        <?php foreach($departments as $d): ?>
                            <option value="<?php echo $d['department_id']; ?>" <?php echo $d['department_id'] == $user['department_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['department_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px;">
                    <button type="button" class="btn-modal-cancel" onclick="history.back()">Cancel</button>
                    <button type="submit" class="btn-modal-submit">Save Changes</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
