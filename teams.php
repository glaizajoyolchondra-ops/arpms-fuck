<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'config/database.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch all users the current user can chat with
$query = "SELECT user_id, full_name, role, status FROM users WHERE user_id != ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$contacts = $stmt->fetchAll();

// Default contact if none selected
$selected_contact_id = $_GET['user_id'] ?? ($contacts[0]['user_id'] ?? null);
$selected_contact = null;
if ($selected_contact_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$selected_contact_id]);
    $selected_contact = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams - ARPMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .chat-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            height: calc(100vh - 80px);
            background: white;
            border-top: 1px solid #E5E7EB;
        }
        .chat-sidebar {
            border-right: 1px solid #E5E7EB;
            display: flex;
            flex-direction: column;
            background: #F9FAFB;
            min-height: 0;
        }
        .chat-main {
            display: flex;
            flex-direction: column;
            background: white;
            min-height: 0;
        }
        .contact-item {
            padding: 16px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            border-bottom: 1px solid #F3F4F6;
            transition: all 0.2s;
        }
        .contact-item:hover {
            background: #F3F4F6;
        }
        .contact-item.active {
            background: #EEF2FF;
            border-left: 4px solid #2D5BFF;
        }
        .chat-header {
            padding: 16px 24px;
            border-bottom: 1px solid #E5E7EB;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-messages {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
            background: #F3F4F6;
            min-height: 0;
        }
        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
        }
        .message-sent {
            align-self: flex-end;
            background: #2D5BFF;
            color: white;
            border-bottom-right-radius: 4px;
        }
        .message-received {
            align-self: flex-start;
            background: white;
            color: #111827;
            border-bottom-left-radius: 4px;
            border: 1px solid #E5E7EB;
        }
        .chat-input-area {
            padding: 20px 24px;
            border-top: 1px solid #E5E7EB;
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .chat-input-wrapper {
            flex: 1;
            position: relative;
        }
        .chat-input {
            width: 100%;
            height: 48px;
            border-radius: 24px;
            border: 1px solid #E5E7EB;
            padding: 0 48px 0 20px;
            font-size: 14px;
            outline: none;
        }
        .chat-input:focus {
            border-color: #2D5BFF;
        }
    </style>
</head>
<body style="overflow: hidden;">
    <?php include 'includes/header.php'; ?>

    <div class="chat-container">
        <!-- Sidebar (Image 4 Left) -->
        <div class="chat-sidebar">
            <div style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <button onclick="location.href='dashboard.php'" style="background: none; border: none; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-chevron-left"></i> Back
                    </button>
                </div>
                <div style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 14px; color: #9CA3AF;"></i>
                    <input type="text" class="input-premium" placeholder="Search members..." style="padding-left: 44px; height: 44px; border-radius: 8px; background: white;">
                </div>
            </div>

            <div style="overflow-y: auto; flex: 1; min-height: 0;">
                <?php foreach($contacts as $contact): ?>
                    <div class="contact-item <?php echo $selected_contact_id == $contact['user_id'] ? 'active' : ''; ?>" onclick="location.href='?user_id=<?php echo $contact['user_id']; ?>'">
                        <div class="user-avatar-blue" style="width: 44px; height: 44px; font-size: 18px; flex-shrink: 0;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div style="flex: 1; overflow: hidden;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-weight: 700; font-size: 14px; color: #111827;"><?php echo htmlspecialchars($contact['full_name']); ?></span>
                                <span style="font-size: 11px; color: #9CA3AF;">12:45 PM</span>
                            </div>
                            <div style="font-size: 13px; color: #6B7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo ucfirst($contact['role']); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Main Chat Area (Image 4 Right) -->
        <div class="chat-main">
            <?php if ($selected_contact): ?>
                <div class="chat-header">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="user-avatar-blue" style="width: 40px; height: 40px; font-size: 16px;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 15px; color: #111827;"><?php echo htmlspecialchars($selected_contact['full_name']); ?></div>
                            <div style="font-size: 12px; color: #10B981;">Last seen: 2 mins ago</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 20px; color: #6B7280; font-size: 18px;">
                        <i class="fa-solid fa-magnifying-glass" style="cursor: pointer;"></i>
                        <i class="fa-solid fa-phone" style="cursor: pointer;"></i>
                        <i class="fa-solid fa-video" style="cursor: pointer;"></i>
                        <i class="fa-regular fa-bell" style="cursor: pointer;"></i>
                        <i class="fa-regular fa-trash-can" style="cursor: pointer;"></i>
                    </div>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <!-- Messages will be loaded here via JS -->
                </div>

                <div class="chat-input-area">
                    <i class="fa-regular fa-face-smile" style="font-size: 22px; color: #6B7280; cursor: pointer;"></i>
                    <i class="fa-solid fa-paperclip" style="font-size: 20px; color: #6B7280; cursor: pointer;" onclick="document.getElementById('chatFile').click()"></i>
                    <input type="file" id="chatFile" style="display: none;">
                    
                    <div class="chat-input-wrapper">
                        <input type="text" class="chat-input" id="chatInput" placeholder="Type your message...">
                        <i class="fa-solid fa-paper-plane" style="position: absolute; right: 16px; top: 14px; color: #2D5BFF; font-size: 18px; cursor: pointer;" onclick="sendMessage()"></i>
                    </div>
                </div>
            <?php else: ?>
                <div style="flex: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; color: #6B7280;">
                    <i class="fa-solid fa-comments" style="font-size: 64px; margin-bottom: 16px; color: #E5E7EB;"></i>
                    <p>Select a contact to start chatting</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const chatInput = document.getElementById('chatInput');
        const chatMessages = document.getElementById('chatMessages');
        const selectedContactId = <?php echo $selected_contact_id ? $selected_contact_id : 'null'; ?>;
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;

        function loadMessages() {
            if (!selectedContactId) return;
            fetch(`includes/get_messages.php?contact_id=${selectedContactId}`)
                .then(res => res.json())
                .then(messages => {
                    chatMessages.innerHTML = '';
                    let lastDate = '';
                    messages.forEach(msg => {
                        const date = new Date(msg.created_at).toLocaleDateString();
                        if (date !== lastDate) {
                            const dateDiv = document.createElement('div');
                            dateDiv.style = "text-align: center; margin: 20px 0;";
                            dateDiv.innerHTML = `<span style="background: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; color: #6B7280; border: 1px solid #E5E7EB;">${date}</span>`;
                            chatMessages.appendChild(dateDiv);
                            lastDate = date;
                        }

                        const div = document.createElement('div');
                        const isSent = msg.sender_id == currentUserId;
                        div.className = `message-bubble ${isSent ? 'message-sent' : 'message-received'}`;
                        const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        div.innerHTML = msg.content + 
                            `<div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 4px; font-size: 10px; color: ${isSent ? 'rgba(255,255,255,0.7)' : '#9CA3AF'};">
                                <span></span>
                                <span>${time}</span>
                            </div>`;
                        chatMessages.appendChild(div);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
        }

        function sendMessage() {
            if (!chatInput) return;
            const msg = chatInput.value.trim();
            if (!msg || !selectedContactId) return;

            const formData = new FormData();
            formData.append('receiver_id', selectedContactId);
            formData.append('content', msg);

            fetch('includes/send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    chatInput.value = '';
                    loadMessages();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendMessage();
            });
            loadMessages();
            // Optional: poll for new messages every 5 seconds
            setInterval(loadMessages, 5000);
        }
    </script>
</body>
</html>
