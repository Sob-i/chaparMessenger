<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Chapar Messenger</title>

@vite(['resources/js/app.js'])

<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Inter, Arial, sans-serif;
        background: #0f1117;
        color: #fff;
        height: 100vh;
        overflow: hidden;
    }

    .messenger {
        display: flex;
        height: 100vh;
    }

    /* Sidebar */

    .sidebar {
        width: 340px;
        background: #171a21;
        border-right: 1px solid #292d36;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .brand {
        font-size: 22px;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .search {
        padding: 0 16px 16px;
    }

    .search input {
        width: 100%;
        border: none;
        outline: none;
        background: #222630;
        color: #fff;
        padding: 12px 15px;
        border-radius: 12px;
        font-size: 14px;
    }

    .search input::placeholder {
        color: #858b98;
    }

    .chat-list {
        overflow-y: auto;
        flex: 1;
    }

    .chat-item {
        padding: 14px 18px;
        display: flex;
        gap: 12px;
        cursor: pointer;
        transition: background .15s ease;
    }

    .chat-item:hover {
        background: #20242d;
    }

    .chat-item.active {
        background: #252a34;
    }

    .avatar {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        background: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }

    .chat-info {
        min-width: 0;
        flex: 1;
    }

    .chat-top {
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    .chat-name {
        font-weight: 600;
    }

    .chat-time {
        color: #737986;
        font-size: 12px;
    }

    .chat-preview {
        color: #858b98;
        font-size: 13px;
        margin-top: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Main chat */

    .chat {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .chat-header {
        height: 76px;
        padding: 14px 22px;
        border-bottom: 1px solid #292d36;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .chat-header-info {
        flex: 1;
    }

    .chat-header-name {
        font-weight: 600;
        font-size: 16px;
    }

    .chat-status {
        color: #35c759;
        font-size: 12px;
        margin-top: 3px;
    }

    .messages {
        flex: 1;
        padding: 25px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .message {
        max-width: 65%;
        padding: 10px 14px;
        border-radius: 15px;
        font-size: 14px;
        line-height: 1.5;
    }

    .message.received {
        align-self: flex-start;
        background: #20242d;
        border-bottom-left-radius: 5px;
    }

    .message.sent {
        align-self: flex-end;
        background: #2563eb;
        border-bottom-right-radius: 5px;
    }

    .message-sender {
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 3px;
        opacity: .7;
    }

    .message-time {
        font-size: 10px;
        opacity: .55;
        margin-top: 4px;
        text-align: right;
    }

    /* Input */

    .message-input {
        padding: 15px 20px;
        border-top: 1px solid #292d36;
        display: flex;
        gap: 10px;
    }

    .message-input input {
        flex: 1;
        background: #20242d;
        color: #fff;
        border: none;
        outline: none;
        padding: 13px 16px;
        border-radius: 13px;
        font-size: 14px;
    }

    .message-input button {
        width: 48px;
        border: none;
        border-radius: 13px;
        background: #2563eb;
        color: white;
        cursor: pointer;
        font-size: 18px;
    }

    .message-input button:hover {
        background: #1d4ed8;
    }

    @media (max-width: 700px) {
        .sidebar {
            width: 85px;
        }

        .sidebar-header,
        .search {
            display: none;
        }

        .chat-info {
            display: none;
        }

        .chat-item {
            justify-content: center;
            padding: 15px 0;
        }
    }
</style>
</head>

<body>

<div class="messenger">

    <aside class="sidebar">

        <div class="sidebar-header">
            <div class="brand">Chapar</div>
        </div>

        <div class="search">
            <input
                type="text"
                id="chat-search"
                placeholder="Search chats..."
            >
        </div>

        <div class="chat-list" id="chat-list">
            <!-- Chats will be loaded here -->
        </div>

    </aside>

    <main class="chat">

        <header class="chat-header">

            <div class="avatar" id="chat-avatar">
                ?
            </div>

            <div class="chat-header-info">
                <div class="chat-header-name" id="chat-name">
                    Select a chat
                </div>

                <div class="chat-status" id="chat-status">
                    —
                </div>
            </div>

        </header>

        <section
            class="messages"
            id="messages"
        >
        </section>

        <form
            class="message-input"
            id="message-form"
        >
            <input
                type="text"
                id="message-input"
                placeholder="Type a message..."
                autocomplete="off"
            >

            <button type="submit">
                ➤
            </button>
        </form>

    </main>

</div>
</body>
</html>
