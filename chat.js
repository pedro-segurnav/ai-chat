document.addEventListener('DOMContentLoaded', function () {
    (function () {
        const scripts = document.getElementsByTagName('script');
        const currentScript = scripts[scripts.length - 1];
        const botId = currentScript.getAttribute('data-bot-id');

        if (!botId) {
            console.error('Error: Missing data-bot-id attribute in the script tag.');
            return;
        }

        // Create chat container
        const chatContainer = document.createElement('div');
        chatContainer.id = 'chat-container';
        chatContainer.style.position = 'fixed';
        chatContainer.style.bottom = '20px';
        chatContainer.style.right = '20px';
        chatContainer.style.width = '350px';
        chatContainer.style.height = '500px';
        chatContainer.style.border = '1px solid #ccc';
        chatContainer.style.borderRadius = '10px';
        chatContainer.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
        chatContainer.style.backgroundColor = '#fff';
        chatContainer.innerHTML = `
            <div id="chat-header" style="background-color: #007bff; color: white; padding: 10px; border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <strong>Loading...</strong>
            </div>
            <div id="chat-box" style="height: 380px; overflow-y: auto; padding: 10px; border-bottom: 1px solid #ccc;"></div>
            <div style="padding: 10px;">
                <input type="text" id="user-input" style="width: 80%; padding: 5px;" placeholder="Type your message..." onkeypress="handleKeyPress(event)">
                <button style="width: 15%; padding: 5px;" onclick="sendMessage()">Send</button>
            </div>
        `;
        document.body.appendChild(chatContainer);

        let botName = 'Bot'; // Default bot name

        // Fetch bot details
        fetch(`/api/get_bot_details.php?bot_id=${botId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }

                botName = data.name || 'Bot';
                const greetingMessage = data.greeting || 'Hello! How can I assist you today?';

                // Update chat header
                document.getElementById('chat-header').innerHTML = `<strong>${botName}</strong>`;

                // Display bot greeting
                const chatBox = document.getElementById('chat-box');
                chatBox.innerHTML += `<div><strong>${botName}:</strong> ${greetingMessage}</div>`;
            })
            .catch(error => {
                console.error('Error fetching bot details:', error);
            });

        // Handle sending a message
        window.sendMessage = function () {
            const userInput = document.getElementById('user-input').value.trim();
            if (!userInput) return;

            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML += `<div><strong>You:</strong> ${userInput}</div>`;
            document.getElementById('user-input').value = '';

            // Show loading indicator
            chatBox.innerHTML += `<div id="loading">Loading...</div>`;
            chatBox.scrollTop = chatBox.scrollHeight;

            fetch('/api/bot_response.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: 'abc123xyz', bot_id: botId, message: userInput })
            })
            .then(response => response.json())
            .then(data => {
                // Remove loading indicator
                document.getElementById('loading')?.remove();

                chatBox.innerHTML += `<div><strong>${data.bot_name || botName}:</strong> ${data.response}</div>`;
                chatBox.scrollTop = chatBox.scrollHeight;
            })
            .catch(error => {
                console.error('Error:', error);
                // Remove loading indicator
                document.getElementById('loading')?.remove();

                chatBox.innerHTML += `<div><strong>${botName}:</strong> Sorry, I encountered an error.</div>`;
                chatBox.scrollTop = chatBox.scrollHeight;
            });
        };

        // Handle pressing the Enter key
        window.handleKeyPress = function (event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        };
    })();
});