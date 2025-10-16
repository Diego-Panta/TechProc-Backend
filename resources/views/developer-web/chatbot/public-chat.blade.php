<!DOCTYPE html>
<html>
<head>
    <title>Chatbot - Incadev</title>
    <meta charset="UTF-8">
    <style>
        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .chat-messages {
            height: 400px;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            overflow-y: auto;
            background: #f9f9f9;
        }
        .message {
            margin-bottom: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            max-width: 80%;
        }
        .user-message {
            background: #007bff;
            color: white;
            margin-left: auto;
        }
        .bot-message {
            background: #e9ecef;
            color: #333;
            margin-right: auto;
        }
        .chat-input {
            display: flex;
            gap: 10px;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .chat-input button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .chat-input button:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .typing-indicator {
            color: #666;
            font-style: italic;
        }
        /* Estilos para FAQs */
        .faqs-section {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .faqs-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 10px;
        }
        .faqs-title {
            font-weight: bold;
            color: #495057;
            margin: 0;
        }
        .category-filter {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .category-btn {
            padding: 4px 8px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 12px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .category-btn:hover {
            background: #5a6268;
        }
        .category-btn.active {
            background: #007bff;
        }
        .faqs-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .faq-btn {
            padding: 8px 12px;
            background: white;
            color: #007bff;
            border: 1px solid #007bff;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            white-space: nowrap;
        }
        .faq-btn:hover {
            background: #007bff;
            color: white;
        }
        .no-faqs {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 10px;
        }
        .suggested-questions {
            margin-top: 10px;
            padding: 10px;
            background: #e7f3ff;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .suggested-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <h1>Chatbot de Soporte</h1>
        
        <!-- Sección de FAQs -->
        <div class="faqs-section">
            <div class="faqs-header">
                <h3 class="faqs-title">Preguntas Frecuentes</h3>
            </div>
            
            <!-- Filtro por categoría -->
            <div class="category-filter">
                <button class="category-btn active" data-category="all">Todas</button>
                @foreach($categories as $category)
                <button class="category-btn" data-category="{{ $category }}">{{ $category }}</button>
                @endforeach
            </div>
            
            <!-- Botones de FAQs -->
            <div class="faqs-buttons" id="faqsButtons">
                @foreach($faqs as $faq)
                <button class="faq-btn" data-question="{{ $faq->question }}">
                    {{ $faq->question }}
                </button>
                @endforeach
            </div>
        </div>

        <!-- Chat -->
        <div class="chat-messages" id="chatMessages">
            <div class="message bot-message">
                ¡Hola! Soy tu asistente virtual de Incadev. ¿En qué puedo ayudarte hoy? Puedes escribir tu pregunta o seleccionar una de las preguntas frecuentes.
            </div>
        </div>
        
        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Escribe tu mensaje..." maxlength="1000">
            <button id="sendButton" onclick="sendMessage()">Enviar</button>
        </div>
        
        <!-- Preguntas sugeridas -->
        <div class="suggested-questions">
            <div class="suggested-title">También puedes preguntar:</div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button class="faq-btn" onclick="sendSuggestedQuestion('¿Cuál es el horario de atención?')">Horario de atención</button>
                <button class="faq-btn" onclick="sendSuggestedQuestion('¿Cómo me inscribo a un curso?')">Inscripción a cursos</button>
                <button class="faq-btn" onclick="sendSuggestedQuestion('¿Dónde encuentro mis certificados?')">Certificados</button>
            </div>
        </div>
    </div>

    <script>
        let conversationId = null;
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const faqsButtons = document.getElementById('faqsButtons');

        // Iniciar conversación al cargar la página
        async function startConversation() {
            try {
                const response = await fetch('/developer-web/chatbot/conversation/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    conversationId = data.data.conversation_id;
                } else {
                    showError('Error al iniciar la conversación');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Error de conexión');
            }
        }

        // Enviar mensaje
        async function sendMessage() {
            const message = messageInput.value.trim();
            
            if (!message || !conversationId) return;
            
            await processUserMessage(message);
        }

        // Procesar mensaje del usuario
        async function processUserMessage(message) {
            // Agregar mensaje del usuario al chat
            addMessage(message, 'user');
            messageInput.value = '';
            sendButton.disabled = true;
            
            // Mostrar indicador de typing
            showTypingIndicator();
            
            try {
                const response = await fetch('/developer-web/chatbot/conversation/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: message,
                        conversation_id: conversationId
                    })
                });
                
                const data = await response.json();
                
                // Remover indicador de typing
                removeTypingIndicator();
                
                if (data.success) {
                    addMessage(data.data.response, 'bot');
                } else {
                    addMessage('Lo siento, hubo un error al procesar tu mensaje. Por favor, intenta nuevamente.', 'bot');
                }
            } catch (error) {
                console.error('Error:', error);
                removeTypingIndicator();
                addMessage('Error de conexión. Por favor, intenta nuevamente.', 'bot');
            }
            
            sendButton.disabled = false;
        }

        // Enviar pregunta sugerida
        function sendSuggestedQuestion(question) {
            if (!conversationId) {
                alert('Por favor, espera a que se inicie la conversación.');
                return;
            }
            processUserMessage(question);
        }

        // Filtrar FAQs por categoría
        async function filterFaqsByCategory(category) {
            try {
                const response = await fetch(`/developer-web/chatbot/faqs/category/${category}`);
                const data = await response.json();
                
                if (data.success) {
                    updateFaqsButtons(data.data);
                }
            } catch (error) {
                console.error('Error al filtrar FAQs:', error);
            }
        }

        // Actualizar botones de FAQs
        function updateFaqsButtons(faqs) {
            faqsButtons.innerHTML = '';
            
            if (faqs.length === 0) {
                faqsButtons.innerHTML = '<div class="no-faqs">No hay preguntas frecuentes en esta categoría</div>';
                return;
            }
            
            faqs.forEach(faq => {
                const button = document.createElement('button');
                button.className = 'faq-btn';
                button.textContent = faq.question;
                button.setAttribute('data-question', faq.question);
                button.onclick = function() {
                    if (!conversationId) {
                        alert('Por favor, espera a que se inicie la conversación.');
                        return;
                    }
                    processUserMessage(faq.question);
                };
                faqsButtons.appendChild(button);
            });
        }

        // Agregar mensaje al chat
        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;
            messageDiv.textContent = text;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Mostrar indicador de typing
        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typingIndicator';
            typingDiv.className = 'message bot-message typing-indicator';
            typingDiv.textContent = 'Escribiendo...';
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Remover indicador de typing
        function removeTypingIndicator() {
            const typingDiv = document.getElementById('typingIndicator');
            if (typingDiv) {
                typingDiv.remove();
            }
        }

        // Mostrar error
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'message bot-message';
            errorDiv.style.background = '#dc3545';
            errorDiv.style.color = 'white';
            errorDiv.textContent = message;
            chatMessages.appendChild(errorDiv);
        }

        // Event Listeners
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Filtro de categorías
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remover clase active de todos los botones
                document.querySelectorAll('.category-btn').forEach(b => {
                    b.classList.remove('active');
                });
                
                // Agregar clase active al botón clickeado
                this.classList.add('active');
                
                // Filtrar FAQs
                const category = this.getAttribute('data-category');
                filterFaqsByCategory(category);
            });
        });

        // Inicializar botones de FAQs existentes
        document.querySelectorAll('.faq-btn[data-question]').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!conversationId) {
                    alert('Por favor, espera a que se inicie la conversación.');
                    return;
                }
                const question = this.getAttribute('data-question');
                processUserMessage(question);
            });
        });

        // Iniciar conversación al cargar la página
        document.addEventListener('DOMContentLoaded', startConversation);
    </script>
</body>
</html>