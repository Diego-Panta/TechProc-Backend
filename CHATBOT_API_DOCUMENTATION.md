# 🤖 Chatbot API - Documentación Técnica

## Descripción General

API REST para chatbot inteligente con integración de Gemini AI que combina:
- **Búsqueda automática en FAQs** con algoritmo de similitud
- **Respuestas generadas por IA** (Gemini) cuando no encuentra FAQs
- **Sistema de feedback** y calificaciones
- **Analytics** de conversaciones y uso

---

## 🔗 Base URL

```
http://localhost:8000/api/developer-web
```

---

## 📋 Endpoints Disponibles

### 1. Conversaciones

#### 1.1 Iniciar Conversación

Crea una nueva conversación y retorna un ID único.

**Endpoint:**
```
POST /chatbot/conversation/start
```

**Headers:**
```json
{
    "Accept": "application/json"
}
```

**Body:** No requiere

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "conversation_id": 1,
    "welcome_message": "¡Hola! Soy el asistente virtual. ¿En qué puedo ayudarte hoy?",
    "response_delay": 1000
}
```

**Campos de la Respuesta:**
- `conversation_id`: ID único de la conversación (usar en mensajes subsecuentes)
- `welcome_message`: Mensaje de bienvenida configurable
- `response_delay`: Delay recomendado en milisegundos para simular escritura

---

#### 1.2 Enviar Mensaje

Envía un mensaje del usuario y recibe respuesta del bot.

**Endpoint:**
```
POST /chatbot/conversation/message
```

**Headers:**
```json
{
    "Accept": "application/json",
    "Content-Type": "application/json"
}
```

**Body:**
```json
{
    "conversation_id": 1,
    "message": "¿Cómo me inscribo en un curso?"
}
```

**Respuesta Exitosa - FAQ Encontrada (200):**
```json
{
    "success": true,
    "data": {
        "success": true,
        "response": "Para inscribirte en un curso, inicia sesión en tu cuenta, navega a la sección de cursos, selecciona el curso deseado y haz clic en 'Inscribirse'.",
        "source": "faq",
        "faq_id": 5,
        "conversation_id": 1,
        "response_delay": 1000
    }
}
```

**Respuesta Exitosa - Gemini AI (200):**
```json
{
    "success": true,
    "data": {
        "success": true,
        "response": "La mejor manera de aprender programación es practicando regularmente...",
        "source": "gemini",
        "conversation_id": 1,
        "response_delay": 1000
    }
}
```

**Campos de la Respuesta:**
- `response`: Texto de la respuesta del bot
- `source`: Origen de la respuesta (`"faq"` o `"gemini"`)
- `faq_id`: ID del FAQ usado (solo cuando `source = "faq"`)
- `conversation_id`: ID de la conversación
- `response_delay`: Delay recomendado en ms

**Respuesta de Error (500):**
```json
{
    "success": false,
    "data": {
        "success": false,
        "response": "Lo siento, estoy teniendo dificultades técnicas en este momento.",
        "source": "fallback",
        "conversation_id": 1,
        "response_delay": 0,
        "error": "Mensaje de error técnico"
    }
}
```

---

#### 1.3 Finalizar Conversación

Finaliza la conversación y envía feedback opcional.

**Endpoint:**
```
POST /chatbot/conversation/end
```

**Headers:**
```json
{
    "Accept": "application/json",
    "Content-Type": "application/json"
}
```

**Body:**
```json
{
    "conversation_id": 1,
    "feedback": {
        "rating": 5,
        "comment": "Excelente servicio, muy útil",
        "resolved": true
    }
}
```

**Campos del Body:**
- `conversation_id`: (requerido) ID de la conversación
- `feedback.rating`: (opcional) Calificación de 1 a 5
- `feedback.comment`: (opcional) Comentario de texto libre
- `feedback.resolved`: (opcional) Boolean - si se resolvió la consulta

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "message": "Conversación finalizada correctamente"
}
```

---

#### 1.4 Obtener Historial de Conversación

Obtiene el historial completo de una conversación.

**Endpoint:**
```
GET /chatbot/conversations/{id}
```

**Headers:**
```json
{
    "Accept": "application/json"
}
```

**Parámetros URL:**
- `{id}`: ID de la conversación

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "started_at": "2025-11-23T10:30:00.000000Z",
        "ended_at": "2025-11-23T10:35:00.000000Z",
        "first_message": "¿Cómo me inscribo?",
        "last_bot_response": "Para inscribirte...",
        "message_count": 5,
        "faq_matched": {
            "id": 5,
            "question": "¿Cómo me inscribo en un curso?",
            "answer": "Para inscribirte...",
            "category": "academico"
        },
        "satisfaction_rating": 5,
        "resolved": true
    }
}
```

**Respuesta Error - No Encontrada (404):**
```json
{
    "success": false,
    "message": "Conversación no encontrada"
}
```

---

### 2. FAQs (Preguntas Frecuentes)

#### 2.1 Listar FAQs Públicas

Obtiene todas las FAQs activas disponibles públicamente.

**Endpoint:**
```
GET /chatbot/faqs/public
```

**Headers:**
```json
{
    "Accept": "application/json"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "question": "¿Cómo me inscribo en un curso?",
            "answer": "Para inscribirte en un curso...",
            "category": "academico",
            "keywords": ["inscripción", "curso", "matricula"],
            "usage_count": 25,
            "active": true,
            "created_at": "2025-11-23T10:00:00.000000Z"
        }
    ]
}
```

---

#### 2.2 Obtener FAQ por ID

Obtiene una FAQ específica.

**Endpoint:**
```
GET /chatbot/faqs/public/{id}
```

**Headers:**
```json
{
    "Accept": "application/json"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "question": "¿Cómo me inscribo en un curso?",
        "answer": "Para inscribirte en un curso...",
        "category": "academico",
        "keywords": ["inscripción", "curso", "matricula"],
        "usage_count": 25,
        "active": true
    }
}
```

---

#### 2.3 Obtener Categorías

Lista todas las categorías disponibles para FAQs.

**Endpoint:**
```
GET /chatbot/faqs/categories
```

**Headers:**
```json
{
    "Accept": "application/json"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "general": "General",
        "academico": "Académico",
        "tecnico": "Técnico",
        "pagos": "Pagos",
        "soporte": "Soporte"
    }
}
```

---

### 3. Configuración del Chatbot

#### 3.1 Obtener Configuración

Obtiene la configuración actual del chatbot.

**Endpoint:**
```
GET /chatbot/config
```

**Headers:**
```json
{
    "Accept": "application/json"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "enabled": true,
        "greeting_message": "¡Hola! Soy el asistente virtual. ¿En qué puedo ayudarte hoy?",
        "fallback_message": "Lo siento, no entendí tu pregunta. ¿Podrías reformularla?",
        "response_delay": 1000,
        "max_conversations_per_day": 1000,
        "contact_threshold": 3,
        "updated_at": "2025-11-23T10:00:00.000Z"
    }
}
```

**Campos de Configuración:**
- `enabled`: Si el chatbot está activo o no
- `greeting_message`: Mensaje inicial al iniciar conversación
- `fallback_message`: Mensaje cuando no entiende la pregunta
- `response_delay`: Delay en ms para simular escritura
- `max_conversations_per_day`: Límite diario de conversaciones
- `contact_threshold`: Número de intentos antes de sugerir contacto humano

---

#### 3.2 Verificar Estado del Servicio

Verifica que el servicio esté funcionando correctamente.

**Endpoint:**
```
GET /chatbot/config/health
```

**Headers:**
```json
{
    "Accept": "application/json"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "status": "healthy",
        "config_loaded": true,
        "cache_driver": "redis",
        "config_keys": ["enabled", "greeting_message", "fallback_message", "response_delay"],
        "cache_key": "chatbot_config"
    }
}
```

---

### 4. Analytics (Solo Admin)

#### 4.1 Obtener Analytics Completos

Obtiene estadísticas completas del chatbot.

**Endpoint:**
```
GET /chatbot/analytics/summary
```

**Headers:**
```json
{
    "Accept": "application/json",
    "Authorization": "Bearer {token}"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "total": 150,
        "resolved": 120,
        "active": 30,
        "resolved_rate": 0.8,
        "avg_satisfaction": 4.5,
        "handed_to_human": 10,
        "faqs_by_category": [
            {
                "category": "academico",
                "count": 45
            },
            {
                "category": "tecnico",
                "count": 30
            }
        ],
        "most_used_faqs": [
            {
                "id": 5,
                "question": "¿Cómo me inscribo en un curso?",
                "usage_count": 89
            }
        ],
        "conversations_by_day": [
            {
                "date": "2025-11-23",
                "count": 25
            },
            {
                "date": "2025-11-22",
                "count": 30
            }
        ]
    }
}
```

---

## 🔄 Flujo de Uso Completo

### Ejemplo: Usuario hace una consulta

```javascript
// 1. Frontend inicia conversación
POST /chatbot/conversation/start
→ Recibe: { conversation_id: 1, welcome_message: "¡Hola!..." }

// 2. Usuario escribe mensaje
POST /chatbot/conversation/message
Body: {
    conversation_id: 1,
    message: "¿Cómo me inscribo en un curso?"
}
→ Bot busca en FAQs
→ Si encuentra: responde con FAQ
→ Si NO encuentra: usa Gemini AI
→ Recibe: { response: "Para inscribirte...", source: "faq" }

// 3. Usuario continúa conversación
POST /chatbot/conversation/message
Body: {
    conversation_id: 1,
    message: "¿Y cuánto cuesta?"
}
→ Recibe nueva respuesta

// 4. Usuario termina conversación
POST /chatbot/conversation/end
Body: {
    conversation_id: 1,
    feedback: {
        rating: 5,
        resolved: true
    }
}
→ Conversación cerrada
```

---

## 🧠 Lógica de Respuesta del Bot

### Prioridad de Respuesta:

1. **Búsqueda en FAQs:**
   - Calcula similitud entre mensaje y preguntas FAQ
   - Si similitud > 60% → responde con FAQ
   - Incrementa contador de uso del FAQ

2. **Gemini AI (Fallback):**
   - Si no encuentra FAQ similar
   - Envía mensaje a Gemini AI
   - Recibe respuesta personalizada

3. **Mensaje de Fallback:**
   - Si Gemini falla (error de API)
   - Retorna mensaje configurable de error

### Algoritmo de Similitud:

El sistema usa **similitud de coseno** para comparar textos:
- Convierte mensaje y preguntas a vectores de palabras
- Calcula similitud (0.0 - 1.0)
- Threshold: **0.6 (60%)**
- Ignora mayúsculas/minúsculas

---

## 📊 Categorías de FAQs

| Categoría | Valor | Descripción |
|-----------|-------|-------------|
| General | `general` | Preguntas generales |
| Académico | `academico` | Cursos, inscripciones, certificados |
| Técnico | `tecnico` | Problemas técnicos, acceso |
| Pagos | `pagos` | Métodos de pago, facturación |
| Soporte | `soporte` | Atención al cliente |

---

## ⚙️ Campos Importantes

### Conversación (`ChatbotConversation`)

```json
{
    "id": 1,
    "started_at": "2025-11-23T10:00:00Z",
    "ended_at": "2025-11-23T10:05:00Z",
    "first_message": "Hola",
    "last_bot_response": "¡Hola! ¿En qué puedo ayudarte?",
    "message_count": 5,
    "faq_matched_id": 3,
    "satisfaction_rating": 5,
    "feedback": "Muy útil",
    "resolved": true,
    "handed_to_human": false
}
```

### FAQ (`ChatbotFaq`)

```json
{
    "id": 1,
    "question": "¿Cómo me inscribo?",
    "answer": "Para inscribirte...",
    "category": "academico",
    "keywords": ["inscripción", "curso"],
    "usage_count": 25,
    "active": true,
    "created_at": "2025-11-23T10:00:00Z",
    "updated_at": "2025-11-23T10:00:00Z"
}
```

---

## 🎯 Códigos de Estado HTTP

| Código | Significado |
|--------|-------------|
| 200 | Éxito |
| 201 | Recurso creado |
| 404 | No encontrado |
| 422 | Error de validación |
| 500 | Error del servidor |

---

## 🔐 Autenticación

### Endpoints Públicos (No requieren auth):
- `POST /chatbot/conversation/start`
- `POST /chatbot/conversation/message`
- `POST /chatbot/conversation/end`
- `GET /chatbot/conversations/{id}`
- `GET /chatbot/faqs/public`
- `GET /chatbot/faqs/public/{id}`
- `GET /chatbot/faqs/categories`
- `GET /chatbot/config`
- `GET /chatbot/config/health`

### Endpoints Protegidos (Requieren Bearer token):
- `GET /chatbot/analytics/summary`
- `POST /chatbot/faqs` (CRUD admin)
- `PUT /chatbot/faqs/{id}` (CRUD admin)
- `DELETE /chatbot/faqs/{id}` (CRUD admin)
- `PUT /chatbot/config` (Admin)

**Header de autenticación:**
```
Authorization: Bearer {token}
```

---

## 🚀 Configuración del Frontend

### 1. Variables de Entorno Recomendadas

```javascript
const CHATBOT_CONFIG = {
    baseUrl: 'http://localhost:8000/api/developer-web',
    endpoints: {
        start: '/chatbot/conversation/start',
        message: '/chatbot/conversation/message',
        end: '/chatbot/conversation/end',
        faqs: '/chatbot/faqs/public',
        config: '/chatbot/config'
    }
}
```

### 2. Estado Mínimo Necesario

```javascript
const chatbotState = {
    conversationId: null,          // ID de conversación activa
    messages: [],                   // Array de mensajes
    isTyping: false,               // Bot está "escribiendo"
    config: null,                  // Configuración del bot
    error: null                    // Error actual si existe
}
```

### 3. Estructura de Mensaje

```javascript
const message = {
    id: 1,
    text: "Hola, ¿cómo estás?",
    sender: "user",                // "user" o "bot"
    timestamp: "2025-11-23T10:00:00Z",
    source: "faq",                 // "faq", "gemini", o null
    faqId: 5                       // Solo si source = "faq"
}
```

### 4. Ejemplo de Implementación

```javascript
// Iniciar conversación
async function startConversation() {
    const response = await fetch(
        `${CHATBOT_CONFIG.baseUrl}${CHATBOT_CONFIG.endpoints.start}`,
        {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        }
    );
    const data = await response.json();

    chatbotState.conversationId = data.conversation_id;

    // Agregar mensaje de bienvenida
    chatbotState.messages.push({
        text: data.welcome_message,
        sender: 'bot',
        timestamp: new Date().toISOString()
    });

    return data;
}

// Enviar mensaje
async function sendMessage(messageText) {
    // Agregar mensaje del usuario
    chatbotState.messages.push({
        text: messageText,
        sender: 'user',
        timestamp: new Date().toISOString()
    });

    // Mostrar indicador de "escribiendo"
    chatbotState.isTyping = true;

    const response = await fetch(
        `${CHATBOT_CONFIG.baseUrl}${CHATBOT_CONFIG.endpoints.message}`,
        {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: chatbotState.conversationId,
                message: messageText
            })
        }
    );

    const result = await response.json();
    const botData = result.data;

    // Simular delay de escritura
    await new Promise(resolve =>
        setTimeout(resolve, botData.response_delay || 1000)
    );

    chatbotState.isTyping = false;

    // Agregar respuesta del bot
    chatbotState.messages.push({
        text: botData.response,
        sender: 'bot',
        timestamp: new Date().toISOString(),
        source: botData.source,
        faqId: botData.faq_id
    });

    return botData;
}

// Finalizar conversación
async function endConversation(rating, comment, resolved) {
    const response = await fetch(
        `${CHATBOT_CONFIG.baseUrl}${CHATBOT_CONFIG.endpoints.end}`,
        {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: chatbotState.conversationId,
                feedback: {
                    rating: rating,
                    comment: comment,
                    resolved: resolved
                }
            })
        }
    );

    const result = await response.json();

    // Limpiar estado
    chatbotState.conversationId = null;
    chatbotState.messages = [];

    return result;
}
```

---

## 📝 Notas Importantes para Frontend

1. **Siempre guardar `conversation_id`** recibido en `/start` para usar en mensajes subsecuentes

2. **Respetar `response_delay`** para simular escritura natural del bot

3. **Mostrar indicador visual** cuando `isTyping = true`

4. **Diferenciar visualmente** respuestas de FAQ vs Gemini AI usando el campo `source`

5. **Permitir rating al final** de la conversación (1-5 estrellas)

6. **Manejar errores gracefully** cuando `success = false`

7. **Implementar timeout** en requests (30 segundos recomendado)

8. **Cache la configuración** del bot localmente para no pedirla en cada load

---

## 🎨 Recomendaciones UX

- Mostrar avatar/icono diferente para mensajes de FAQ vs Gemini
- Agregar badge "Pregunta frecuente" cuando `source = "faq"`
- Implementar scroll automático al final al recibir mensajes
- Guardar conversación en localStorage por si usuario recarga página
- Mostrar timestamp solo en algunos mensajes (no todos)
- Permitir copiar respuestas del bot
- Agregar botón "¿Fue útil esta respuesta?" después de cada mensaje del bot

---

## 🔍 Troubleshooting

### El bot no responde
- Verificar que `conversation_id` sea válido
- Revisar que el mensaje no esté vacío
- Verificar conectividad con API

### Error 403 en respuestas
- API key de Gemini inválida o revocada
- Contactar con backend para actualizar API key

### Respuestas siempre de Gemini (nunca FAQs)
- Puede que no hayan FAQs creadas aún
- O las preguntas del usuario no tienen similitud > 60% con FAQs

### Delay muy largo
- `response_delay` configurable en `/config`
- Frontend puede ignorarlo si es muy largo

---

**Versión:** 1.0
**Última actualización:** 2025-11-23
**Modelo AI:** Gemini 2.0 Flash Lite
