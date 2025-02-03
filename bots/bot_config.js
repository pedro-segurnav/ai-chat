const botConfig = {
    colors: {
        primary: '#007bff',
        secondary: '#6c757d',
        background: '#ffffff'
    },
    soundEnabled: true,
    greetings: ['Hello!', 'Hi there!', 'How can I assist you?'],
    randomNames: ['Martin', 'Sophia', 'Alex', 'Emma']
};

function applyBotConfig(config) {
    document.getElementById('chat-box').style.backgroundColor = config.colors.background;
    document.getElementById('send-btn').style.backgroundColor = config.colors.primary;

    if (config.soundEnabled) {
        const audio = new Audio('notification.mp3');
        audio.play();
    }

    const greeting = config.greetings[Math.floor(Math.random() * config.greetings.length)];
    const botName = config.randomNames[Math.floor(Math.random() * config.randomNames.length)];
    document.getElementById('bot-name').textContent = botName;
    document.getElementById('greeting-message').textContent = `${greeting} I'm ${botName}.`;
}

applyBotConfig(botConfig);