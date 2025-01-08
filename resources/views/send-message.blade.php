<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure Messaging</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --background-light: #f8f9fa;
            --chat-bg: #edf2f7;
            --message-sent: #0d6efd;
            --message-received: #ffffff;
            --border-color: #dee2e6;
            --hover-color: #f8f9fa;
            --active-color: #e9ecef;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
        }

        body {
            background-color: var(--background-light);
            height: 100vh;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        .chat-container {
            height: calc(100vh - 2rem);
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            margin: 1rem auto;
            overflow: hidden;
        }

        .contacts-list {
            height: 100%;
            border-right: 1px solid var(--border-color);
            background-color: white;
        }

        .contact-item {
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .contact-item:hover {
            background-color: var(--hover-color);
            transform: translateX(4px);
        }

        .contact-item.active {
            background-color: var(--active-color);
            border-left: 4px solid var(--primary-color);
        }

        .contact-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .chat-messages {
            height: calc(100vh - 180px);
            overflow-y: auto;
            background-color: var(--chat-bg);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .message {
            max-width: 75%;
            margin-bottom: 1.5rem;
            position: relative;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-sent {
            margin-left: auto;
        }

        .message-bubble {
            padding: 0.75rem 1rem;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            position: relative;
        }

        .message-received .message-bubble {
            background-color: var(--message-received);
            border-bottom-left-radius: 4px;
        }

        .message-sent .message-bubble {
            background-color: var(--message-sent);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-time {
            font-size: 0.75rem;
            margin-top: 0.5rem;
            opacity: 0.7;
            text-align: right;
        }

        .chat-input {
        background-color: white;
        border-top: 1px solid var(--border-color);
        padding: 1rem;
        width: calc(100% - 33.333%); /* Adjust for the contacts column width */
        position: fixed;
        bottom: 1rem;
        right: 1rem;
        margin: 0;
        z-index: 1000;
    }


    @media (max-width: 992px) {
        .chat-input {
            width: calc(100% - 33.333%);
            right: 1rem;
        }
    }

    @media (min-width: 992px) {
        .chat-input {
            width: calc(75% - 2rem); /* Adjust for large screens */
        }
    }

    .selected-file {
        display: flex;
        align-items: center;
        background-color: #e9ecef;
        padding: 0.25rem 0.75rem;
        border-radius: 16px;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .selected-file .file-name {
        margin-right: auto;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .selected-file .remove-file {
        cursor: pointer;
        color: var(--secondary-color);
        margin-left: 0.5rem;
    }

    .selected-file .remove-file:hover {
        color: #dc3545;
    }

    .message-form-container {
        width: 100%;
    }

        #messageForm {
            width: 100%;
        }

        .chat-input .input-group {
            display: flex;
            align-items: center;
            background-color: var(--background-light);
            border-radius: 24px;
            padding: 0.5rem;
            gap: 0.5rem;
            width: 100%;
        }

        .chat-input input[type="text"] {
            flex: 1;
            border: none;
            background: transparent;
            padding: 0.5rem 1rem;
            width: 100%;
        }

        .chat-input input[type="text"]:focus {
            outline: none;
            box-shadow: none;
        }

        .chat-input .btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            flex-shrink: 0;
        }

        .chat-input .btn-outline-secondary {
            border: 1px solid var(--border-color);
            background-color: white;
            margin-right: 0.25rem;
        }

        .chat-input .btn-primary {
            background-color: var(--primary-color);
            border: none;
        }

        .online-indicator {
            width: 12px;
            height: 12px;
            background-color: #28a745;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            position: relative;
        }

        .online-indicator::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: inherit;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.5; }
            70% { transform: scale(1.5); opacity: 0; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        #searchInput {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            width: 100%;
        }

        #searchInput:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            border-color: var(--primary-color);
        }

        #chatHeader {
            font-weight: 600;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-3">
        <div class="row chat-container">
            <!-- Contacts List -->
            <div class="col-md-4 col-lg-3 px-0">
                <div class="contacts-list">
                    <div class="p-3 bg-light">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search contacts..." id="searchInput">
                        </div>
                    </div>
                    <div id="recipient">
                        <!-- Recipients will be dynamically added here -->
                    </div>
                </div>
            </div>
            
            <!-- Chat Area -->
            <div class="col-md-8 col-lg-9 px-0">
                <div class="d-flex flex-column h-100">
                    <div class="p-3 bg-white border-bottom">
                        <div class="d-flex align-items-center">
                            <span class="online-indicator"></span>
                            <h5 id="chatHeader" class="mb-0">Select a contact</h5>
                        </div>
                    </div>
                    <div class="chat-messages" id="messages">
                        <!-- Messages will be dynamically added here -->
                    </div>
                    <div class="chat-input">
    <form id="messageForm">
        <div id="selected-file-container"></div>
        <div class="input-group">
            <input type="file" id="file" style="display: none;">
            <label class="btn btn-outline-secondary" for="file">
                <i class="fas fa-paperclip"></i>
            </label>
            <input type="text" class="form-control" placeholder="Type a message..." id="message">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </form>
</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/crypto-js@4.1.1/crypto-js.min.js"></script>
    <script>
        const encryptionKey = "your-256-bit-secret-key";

        function encryptMessage(message) {
            return CryptoJS.AES.encrypt(message, encryptionKey).toString();
        }

        function decryptMessage(ciphertext) {
            const bytes = CryptoJS.AES.decrypt(ciphertext, encryptionKey);
            return bytes.toString(CryptoJS.enc.Utf8);
        }

        async function loadRecipients() {
            try {
                const response = await fetch('/users');
                const users = await response.json();

                const recipientDropdown = document.getElementById("recipient");
                recipientDropdown.innerHTML = users.map(user => `
                    <div class="contact-item" data-id="${user.id}" onclick="selectRecipient(${user.id}, '${user.name.replace(/'/g, "\\'")}')">
                        <div class="contact-avatar">${user.name.charAt(0)}</div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0">
                                ${user.online ? '<span class="online-indicator"></span>' : ''}
                                ${user.name}
                            </h6>
                            <small class="text-muted">${user.lastMessage || ''}</small>
                        </div>
                        <small class="text-muted">${user.lastMessageTime || ''}</small>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Failed to load recipients:', error);
            }
        }

        function selectRecipient(id, name) {
            document.querySelectorAll('.contact-item').forEach(item => item.classList.remove('active'));
            document.querySelector(`.contact-item[data-id="${id}"]`).classList.add('active');
            document.getElementById("chatHeader").innerHTML = `
                ${name}
            `;
            document.getElementById("recipient").dataset.recipientId = id;
            fetchMessages();
        }

        document.getElementById('messageForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await sendMessage();
        });

        document.getElementById('file').addEventListener('change', function(e) {
        const fileContainer = document.getElementById('selected-file-container');
        if (this.files.length > 0) {
            const fileName = this.files[0].name;
            fileContainer.innerHTML = `
                <div class="selected-file">
                    <i class="fas fa-file me-2"></i>
                    <span class="file-name">${fileName}</span>
                    <span class="remove-file" onclick="removeFile()">
                        <i class="fas fa-times"></i>
                    </span>
                </div>
            `;
        } else {
            fileContainer.innerHTML = '';
        }
    });

    // Function to remove selected file
    function removeFile() {
        const fileInput = document.getElementById('file');
        const fileContainer = document.getElementById('selected-file-container');
        fileInput.value = '';
        fileContainer.innerHTML = '';
    }

    async function sendMessage() {
        const message = document.getElementById("message").value;
        const fileInput = document.getElementById("file");
        const recipientId = document.getElementById("recipient").dataset.recipientId;
        const fileContainer = document.getElementById('selected-file-container');

        if (!recipientId) {
            alert("Select a recipient first.");
            return;
        }

        if (!message && !fileInput.files[0]) {
            return;
        }

        const formData = new FormData();
        formData.append('recipient_id', recipientId);

        if (message) {
            const encryptedMessage = encryptMessage(message);
            formData.append('encrypted_message', encryptedMessage);
        }

        if (fileInput.files[0]) {
            formData.append('file', fileInput.files[0]);
        }

        try {
            const response = await fetch('/send-message', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData,
            });

            if (response.ok) {
                document.getElementById("message").value = '';
                fileInput.value = '';
                fileContainer.innerHTML = '';
                await fetchMessages();
            } else {
                alert("Failed to send message/file.");
            }
        } catch (error) {
            console.error('Failed to send message:', error);
            alert("Failed to send message/file.");
        }
    }

        async function fetchMessages() {
            const recipientId = document.getElementById("recipient").dataset.recipientId;

            try {
                const response = await fetch(`/messages/${recipientId}`);
                const messages = await response.json();

                const messageDiv = document.getElementById("messages");
                messageDiv.innerHTML = '';

                messages.forEach(msg => {
                    const messageElement = document.createElement('div');
                    messageElement.className = `message ${msg.sent ? 'message-sent' : 'message-received'}`;
                    const time = new Date(msg.created_at).toLocaleString(); 
                    messageElement.innerHTML = `
                        <div class="message-bubble">
                            ${msg.encrypted_message ? decryptMessage(msg.encrypted_message) : ""}
                            ${msg.file_url ? `
                                <div class="mt-2">
                                    <a href="${msg.file_url}" target="_blank" class="text-secondary">
                                        <i class="fas fa-file"></i> ${msg.file_name || 'Attachment'}
                                    </a>
                                </div>
                            ` : ""}
                        </div>
                        <div class="message-time text-muted">${time}</div>
                    `;
                    messageDiv.appendChild(messageElement);
                });

                messageDiv.scrollTop = messageDiv.scrollHeight;
            } catch (error) {
                console.error('Failed to fetch messages:', error);
            }
        }

        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.contact-item').forEach(item => {
                const name = item.querySelector('h6').textContent.toLowerCase();
                item.style.display = name.includes(searchTerm) ? 'block' : 'none';
            });
        });

        // Initialize the chat
        loadRecipients();
    </script>
</body>
</html>