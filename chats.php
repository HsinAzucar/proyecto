<?php
session_start();
if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}
$id_usuario = $_SESSION['id_usuario']; // usuario logueado
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chats - Escuela Ejemplo</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/chats.css">
</head>
<body>
    <div class="chat-container">
        
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="chat-header">
                <h2>Mensajes</h2>
                
            </div>
            
            <div class="search-bar">
                <input type="text" placeholder="Buscar chats..." id="search-input">
            </div>

            <div class="chat-list" id="chat-list">
                <!-- Los chats se cargan vía fetch -->
            </div>
        </div>

        <!-- Ventana principal -->
        <div class="chat-main">
            <div class="chat-topbar" id="chat-topbar">
                <div class="current-chat" id="current-chat-info">
                    <div class="avatar" id="chat-avatar">💬</div>
                    <div>
                        <h3 id="chat-name">Selecciona un chat</h3>
                        <p class="status" id="chat-status">—</p>
                    </div>
                </div>
            </div>

            <div class="messages-area" id="messages-area">
                <p style="text-align:center; color:#94a3b8; margin-top:50px;">
                    Selecciona un chat para comenzar
                </p>
            </div>

            <div class="message-input-area">
                <input type="text" id="message-input" placeholder="Escribe un mensaje..." disabled>
                <button onclick="sendMessage()" class="send-btn" id="send-btn" disabled>Enviar</button>
            </div>
        </div>
    </div>

    <script>
        let chats = [];
        let currentChatId = null;
        const currentUserId = <?php echo $id_usuario; ?>; // usuario logueado

        // Cargar lista de chats desde backend
        function loadChats() {
            fetch("backend/get_chats.php")
            .then(r => r.json())
            .then(data => {
                chats = data.map(c => ({
                    id: c.id_usuario,
                    name: c.nombre,
                    avatar: c.id_rol == 2 ? "👩‍🏫" : "🧑‍🎓",
                    status: "—",
                    lastMessage: "",
                    time: "",
                    messages: []
                }));
                renderChatList();
            });
        }

        // Renderizar lista de chats
        function renderChatList() {
            const chatList = document.getElementById('chat-list');
            chatList.innerHTML = '';

            chats.forEach(chat => {
                const chatItem = document.createElement('div');
                chatItem.className = `chat-item ${chat.id === currentChatId ? 'active' : ''}`;
                chatItem.innerHTML = `
                    <div class="avatar">${chat.avatar}</div>
                    <div class="chat-info">
                        <h4>${chat.name}</h4>
                        <p>${chat.lastMessage || ''}</p>
                    </div>
                    <span class="time">${chat.time || ''}</span>
                `;
                chatItem.onclick = () => openChat(chat.id);
                chatList.appendChild(chatItem);
            });
        }

        // Abrir un chat
    function openChat(id) {
    currentChatId = id;
    const chat = chats.find(c => c.id === id);
    if (!chat) return;

    // Actualizar barra superior
    document.getElementById('chat-avatar').textContent = chat.avatar;
    document.getElementById('chat-name').textContent = chat.name;
    document.getElementById('chat-status').textContent = chat.status;

    // Habilitar input
    document.getElementById('message-input').disabled = false;
    document.getElementById('send-btn').disabled = false;

    // Cargar mensajes desde backend
    fetch("backend/get_mensajes.php?id_chat=" + id)
    .then(r => r.json())
    .then(msgs => {
        chat.messages = msgs.map(m => ({
            type: m.id_remitente == currentUserId ? "sent" : "received",
            text: m.mensaje || "",   // evitar undefined
            time: m.fecha || ""      // evitar undefined
        }));

        // Actualizar último mensaje en la lista
        if (chat.messages.length > 0) {
            const last = chat.messages[chat.messages.length - 1];
            chat.lastMessage = last.text;
            chat.time = last.time;
        }

        renderMessages(chat.messages);
        renderChatList();
    });
}


        // Renderizar mensajes
        function renderMessages(messages) {
            const area = document.getElementById('messages-area');
            area.innerHTML = '';

            if (messages.length === 0) {
                area.innerHTML = `<p style="text-align:center; color:#94a3b8; margin-top:50px;">No hay mensajes aún</p>`;
                return;
            }

            messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = `message ${msg.type}`;
                div.innerHTML = `
                    <div class="message-content">${msg.text}</div>
                    <span class="message-time">${msg.time}</span>
                `;
                area.appendChild(div);
            });

            area.scrollTop = area.scrollHeight;
        }

        // Enviar mensaje
        function sendMessage() {
    const input = document.getElementById('message-input');
    if (!currentChatId || input.value.trim() === '') return;

    const mensaje = input.value.trim();

    fetch("backend/enviar_mensaje.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "destinatario="+currentChatId+"&mensaje="+encodeURIComponent(mensaje)
    })
    .then(r => r.text())
    .then(res => {
        if(res === "ok"){
            // Añadir el mensaje directamente al área sin esperar reload
            const nuevoMsg = {
                type: "sent",
                text: mensaje,
                time: new Date().toLocaleString()
            };
            const chat = chats.find(c => c.id === currentChatId);
            if(chat){
                chat.messages.push(nuevoMsg);
                chat.lastMessage = nuevoMsg.text;
                chat.time = nuevoMsg.time;
                renderMessages(chat.messages);
                renderChatList();
            }
        } else {
            alert("Error al enviar mensaje ❌");
        }
    });

    input.value = '';
}


        // Inicializar
        window.onload = () => {
            loadChats();
            document.getElementById('message-input').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendMessage();
            });
        };
    </script>
</body>
</html>
