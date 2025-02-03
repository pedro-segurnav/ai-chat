document.addEventListener('DOMContentLoaded', function () {
    // Get the script element that loaded this file
    const scripts = document.getElementsByTagName('script');
    const currentScript = scripts[scripts.length - 1]; // The last script is this one
    const scriptSrc = currentScript.src;

    // Parse the query string from the script's URL
    const urlParams = new URLSearchParams(scriptSrc.split('?')[1]);
    const botId = urlParams.get('bot_id');

    if (!botId) {
        console.error('Bot ID is missing.');
        return;
    }

    console.log('Bot ID:', botId);

    // Create the chat box
    const chatBox = document.createElement('div');
    chatBox.id = 'chat-box';
    chatBox.style.position = 'fixed';
    chatBox.style.bottom = '20px';
    chatBox.style.right = '20px';
    chatBox.style.width = '300px';
    chatBox.style.height = '400px';
    chatBox.style.backgroundColor = '#fff';
    chatBox.style.border = '1px solid #ccc';
    chatBox.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
    chatBox.style.display = 'flex';
    chatBox.style.flexDirection = 'column';
    chatBox.innerHTML = `
        <div style="padding: 10px; background-color: #0d6efd; color: #fff; font-weight: bold;">
            Chat with us!
        </div>
        <div id="messages" style="flex: 1; overflow-y: auto; padding: 10px;"></div>
        <div style="display: flex; padding: 10px; border-top: 1px solid #ccc;">
            <input id="user-input" type="text" style="flex: 1; padding: 5px;" placeholder="Type a message...">
            <button id="send-btn" style="padding: 5px 10px; margin-left: 5px;">Send</button>
        </div>
    `;
    document.body.appendChild(chatBox);

    // Initialize DOM elements after the chat box is added to the DOM
    const messagesDiv = document.getElementById('messages');
    const userInput = document.getElementById('user-input');
    const sendBtn = document.getElementById('send-btn');

    // Add event listener to the send button
sendBtn.addEventListener('click', function () {
    const message = userInput.value.trim();
    if (message) {
        appendMessage('You', message);
        userInput.value = '';

        // Log the request payload
        const requestData = {
            token: 'abc123xyz', // Replace with the actual secret token
            bot_id: botId,
            message: message
        };
        console.log('Sending request:', requestData);

        // Send message to server
        fetch('https://ai.oemdrive.com/api/bot_response.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log('Server response status:', response.status); // Log the HTTP status code
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Server response data:', data); // Log the server's response
            if (data.response) {
                appendMessage('Bot', data.response);
            } else {
                console.error('Invalid response from server:', data);
                appendMessage('Bot', 'An error occurred.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            appendMessage('Bot', 'An error occurred.');
        });
    }
});

    function appendMessage(sender, text) {
        const messageDiv = document.createElement('div');
        messageDiv.style.marginBottom = '10px';
        messageDiv.innerHTML = `<strong>${sender}:</strong> ${text}`;
        messagesDiv.appendChild(messageDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
});