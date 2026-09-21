const API = '/api';

let authToken = localStorage.getItem('auth_token');
let currentUser = null;
let currentChat = null;
let currentChannel = null;


async function apiRequest(url, options = {}) {
    const response = await fetch(`${API}${url}`, {
        ...options,

        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',

            ...(authToken
                ? {
                    'Authorization': `Bearer ${authToken}`,
                }
                : {}),

            ...(options.headers || {}),
        },
    });

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.message || 'Something went wrong');
    }

    return data;
}


async function loadChats() {
    try {
        const data = await apiRequest('/chats');

        if (!data.success) {
            throw new Error(
                data.message || 'Could not load chats'
            );
        }

        renderChats(data.chats);

    } catch (error) {
        console.error('Failed to load chats:', error);
    }
}


function renderChats(chats) {
    const chatList = document.getElementById('chat-list');

    chatList.innerHTML = '';

    chats.forEach(chat => {
        const chatElement = document.createElement('div');

        chatElement.className = 'chat-item';

        chatElement.dataset.chatId = chat.id;

        chatElement.innerHTML = `
            <div class="avatar">
                ${getInitial(chat.name)}
            </div>

            <div class="chat-info">
                <div class="chat-top">
                    <div class="chat-name">
                        ${chat.name ?? 'Private Chat'}
                    </div>
                </div>

                <div class="chat-preview">
                    ${chat.type}
                </div>
            </div>
        `;

        chatElement.addEventListener('click', () => {
            selectChat(chat);
        });

        chatList.appendChild(chatElement);
    });
}


function getInitial(name) {
    if (!name) {
        return '?';
    }

    return name.charAt(0).toUpperCase();
}


function selectChat(chat) {
    if (currentChannel && currentChat) {
        window.Echo.leave(`chat.${currentChat.id}`);
        currentChannel = null;
    }

    currentChat = chat;

    document.getElementById('chat-name').textContent =
        chat.name ?? 'Private Chat';

    document.getElementById('chat-avatar').textContent =
        getInitial(chat.name);

    console.log('Selected chat:', chat);

    currentChannel = window.Echo.private(
        `chat.${chat.id}`
    );

    currentChannel.subscribed(() => {
        console.log(
            `Successfully subscribed to chat.${chat.id}`
        );
    });

    currentChannel.error((error) => {
        console.error(
            `Failed to subscribe to chat.${chat.id}:`,
            error
        );
    });

    currentChannel.listen(
        '.message.sent',
        (event) => {
            console.log('New message:', event);

            addMessage(event.message);
        }
    );
}


function addMessage(message) {
    const messagesContainer =
        document.getElementById('messages');

    const messageElement =
        document.createElement('div');

    messageElement.classList.add('message');


    /*
     * Determine whether this message
     * was sent by the current user.
     */
    if (
        currentUser &&
        message.sender_id === currentUser.id
    ) {
        messageElement.classList.add('sent');
    } else {
        messageElement.classList.add('received');
    }


    const senderName =
        message.sender_info?.name ?? 'Unknown';


    messageElement.innerHTML = `
        <div class="message-sender">
            ${senderName}
        </div>

        <div class="message-text">
            ${message.message}
        </div>
    `;


    messagesContainer.appendChild(messageElement);


    /*
     * Automatically scroll to the newest message.
     */
    messagesContainer.scrollTop =
        messagesContainer.scrollHeight;
}


loadChats();
