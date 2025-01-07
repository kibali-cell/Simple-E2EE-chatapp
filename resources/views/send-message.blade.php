<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secure Messaging</title>
    <script src="https://cdn.jsdelivr.net/npm/crypto-js@4.1.1/crypto-js.min.js"></script>
</head>
<body>
    <h1>Secure Messaging & File Sharing</h1>

    <!-- Message Input -->
    <textarea id="message" placeholder="Type your message"></textarea>

    <!-- File Input -->
    <input type="file" id="file">

    <!-- Recipient Dropdown -->
    <label for="recipient">Send to:</label>
    <select id="recipient"></select>

    <button onclick="sendMessage()">Send</button>

    <h2>Messages</h2>
    <div id="messages"></div>

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
            const response = await fetch('/users');
            const users = await response.json();

            const recipientDropdown = document.getElementById("recipient");
            users.forEach(user => {
                const option = document.createElement("option");
                option.value = user.id;
                option.textContent = user.name;
                recipientDropdown.appendChild(option);
            });
        }

        async function sendMessage() {
    console.log("Send button clicked");

    const message = document.getElementById("message").value;
    const fileInput = document.getElementById("file");
    const recipientId = document.getElementById("recipient").value;

    console.log("Message:", message);
    console.log("Recipient ID:", recipientId);

    const formData = new FormData();
    formData.append('recipient_id', recipientId);
    if (message) {
        const encryptedMessage = encryptMessage(message);
        formData.append('encrypted_message', encryptedMessage);
        console.log("Encrypted Message:", encryptedMessage);
    }
    if (fileInput.files[0]) {
        formData.append('file', fileInput.files[0]);
        console.log("File selected:", fileInput.files[0].name);
    }

    try {
        const response = await fetch('/send-message', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
        });

        console.log("Response status:", response.status);
        if (response.ok) {
            alert('Message/File sent successfully!');
            fetchMessages(); // Refresh messages after sending
        } else {
            const error = await response.json();
            console.error("Error response:", error);
            alert('Failed to send message/file.');
        }
    } catch (error) {
        console.error("Fetch error:", error);
        alert('An error occurred. Check the console for details.');
    }
}


        async function fetchMessages() {
            const response = await fetch('/messages');
            const messages = await response.json();

            const messageDiv = document.getElementById('messages');
            messageDiv.innerHTML = '';

            messages.forEach(msg => {
                if (msg.encrypted_message) {
                    const decrypted = decryptMessage(msg.encrypted_message);
                    messageDiv.innerHTML += `<p>Message: ${decrypted}</p>`;
                }
                if (msg.file_path) {
                    messageDiv.innerHTML += `<p>File: <a href="/storage/${msg.file_path}" download>Download</a></p>`;
                }
            });
        }

        loadRecipients();
        fetchMessages();
    </script>
</body>
</html>
