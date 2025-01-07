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
        .chat-container {
            height: calc(100vh - 2rem);
            background-color: #f8f9fa;
        }
        .contacts-list {
            height: 100%;
            border-right: 1px solid #dee2e6;
            background-color: white;
        }
        .contact-item {
            cursor: pointer;
            transition: background-color 0.3s;
            border-bottom: 1px solid #f1f1f1;
        }
        .contact-item:hover {
            background-color: #f8f9fa;
        }
        .contact-item.active {
            background-color: #e9ecef;
        }
        .chat-messages {
            height: calc(100% - 70px);
            overflow-y: auto;
            background-color: #edf2f7;
            padding: 1rem;
            flex-grow: 1;
        }
        .message {
            max-width: 75%;
            margin-bottom: 1rem;
            position: relative;
        }
        .message-sent {
            margin-left: auto;
        }
        .message-received .message-bubble {
            background-color: white;
            border-radius: 15px 15px 15px 0;
        }
        .message-sent .message-bubble {
            background-color: #0d6efd;
            color: white;
            border-radius: 15px 15px 0 15px;
        }

        .message-bubble {
            padding: 0.75rem 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .message-time {
            font-size: 0.75rem;
            margin-top: 0.25rem;
            opacity: 0.7;
        }
        .chat-input {
            background-color: white;
            border-top: 1px solid #dee2e6;
            padding: 1rem;
        }
        .online-indicator {
            width: 10px;
            height: 10px;
            background-color: #28a745;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/crypto-js@4.1.1/crypto-js.min.js"></script>
</head>
<body>
    <div class="container-fluid py-3">
        <div class="row chat-container">
            <div class="col-md-4 col-lg-3 px-0">
                <div class="contacts-list">
                    <div class="p-3 bg-light border-bottom">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search contacts..." id="searchInput">
                            <button class="btn btn-outline-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div id="recipient">
                        <!-- Recipients will be dynamically added here -->
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-lg-9 px-0">
                <div class="d-flex flex-column h-100">
                    <div class="p-3 bg-white border-bottom">
                        <div class="d-flex align-items-center">
                            <h5 id="chatHeader" class="mb-0">Select a contact</h5>
                        </div>
                    </div>
                    <div class="chat-messages" id="messages">
                        <!-- Messages will be dynamically added here -->
                    </div>
                    <div class="chat-input mt-auto">
                        <form id="messageForm" class="d-flex gap-2" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="file" id="file" class="form-control" style="display: none;">
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
                    <div class="contact-item p-3" data-id="${user.id}" onclick="selectRecipient(${user.id}, '${user.name.replace(/'/g, "\\'")}')">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">
                                    ${user.online ? '<span class="online-indicator"></span>' : ''}
                                    ${user.name}
                                </h6>
                                <small class="text-muted">${user.lastMessage || ''}</small>
                            </div>
                            <small class="text-muted">${user.lastMessageTime || ''}</small>
                        </div>
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
                <span class="online-indicator"></span>
                Chat with ${name}
            `;
            document.getElementById("recipient").dataset.recipientId = id;
            fetchMessages();
        }

        document.getElementById('messageForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await sendMessage();
        });

        async function sendMessage() {
            const message = document.getElementById("message").value;
            const fileInput = document.getElementById("file");
            const recipientId = document.getElementById("recipient").dataset.recipientId;

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
                    document.getElementById("file").value = '';
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
                                    <a href="${msg.file_url}" target="_blank" class="text-white">
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

        loadRecipients();
    </script>
</body>
</html>