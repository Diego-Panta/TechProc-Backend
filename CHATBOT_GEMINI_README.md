# Chatbot con Gemini AI - Guía de Uso

## 🤖 Descripción

Sistema de chatbot inteligente que combina:
- **Búsqueda en FAQs** con algoritmo de similitud de coseno (60% threshold)
- **Integración con Gemini 2.0 Flash Lite** como fallback para respuestas no encontradas en FAQs
- **Configuración dinámica** con cache
- **Analytics completos** de conversaciones y uso de FAQs

## 🔑 Configuración

### 1. API Key de Gemini

Ya está configurada en tu `.env`:
```env
GEMINI_API_KEY=AIzaSyCOrNJs5KXL6Oh1fdmtJDWmVZlf_a8rqOU
```

### 2. Verificar configuración en `config/services.php`:
```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
],
```

## 📁 Arquitectura del Sistema

### Controladores:
- **ChatbotApiController** - Gestión de conversaciones con Gemini AI
- **ChatbotFaqApiController** - CRUD de FAQs
- **ChatbotConfigController** - Configuración dinámica del bot

### Servicios:
- **GeminiChatbotService** - Lógica principal con integración de Gemini AI
- **ChatbotFaqService** - Gestión de FAQs
- **ChatbotConfigService** - Configuración con cache (24h)

### Repositorio:
- **ChatbotRepository** - Acceso a datos con algoritmo de similitud inteligente

## 🚀 Endpoints Disponibles

### Conversaciones (Públicas)
```
POST   /api/developer-web/chatbot/conversation/start
POST   /api/developer-web/chatbot/conversation/message
POST   /api/developer-web/chatbot/conversation/end
GET    /api/developer-web/chatbot/conversations/{id}
```

### FAQs (Públicas)
```
GET    /api/developer-web/chatbot/faqs/public
GET    /api/developer-web/chatbot/faqs/public/{id}
GET    /api/developer-web/chatbot/faqs/categories
```

### FAQs (Admin - requiere auth)
```
GET    /api/developer-web/chatbot/faqs
POST   /api/developer-web/chatbot/faqs
PUT    /api/developer-web/chatbot/faqs/{id}
DELETE /api/developer-web/chatbot/faqs/{id}
GET    /api/developer-web/chatbot/faqs/stats/summary
```

### Configuración
```
GET    /api/developer-web/chatbot/config
PUT    /api/developer-web/chatbot/config
POST   /api/developer-web/chatbot/config/reset
GET    /api/developer-web/chatbot/config/health
```

### Analytics (Admin)
```
GET    /api/developer-web/chatbot/analytics/summary
```

## 📊 Flujo de Funcionamiento

### 1. Iniciar Conversación
```json
POST /api/developer-web/chatbot/conversation/start

Respuesta:
{
    "success": true,
    "conversation_id": 1,
    "welcome_message": "¡Hola! Soy el asistente virtual. ¿En qué puedo ayudarte hoy?",
    "response_delay": 1000
}
```

### 2. Enviar Mensaje
```json
POST /api/developer-web/chatbot/conversation/message
{
    "conversation_id": 1,
    "message": "¿Cómo me inscribo en un curso?"
}

Respuesta (si encuentra FAQ):
{
    "success": true,
    "response": "Para inscribirte en un curso...",
    "source": "faq",
    "faq_id": 5,
    "conversation_id": 1,
    "response_delay": 1000
}

Respuesta (si usa Gemini):
{
    "success": true,
    "response": "Basándome en tu pregunta...",
    "source": "gemini",
    "conversation_id": 1,
    "response_delay": 1000
}
```

### 3. Finalizar Conversación
```json
POST /api/developer-web/chatbot/conversation/end
{
    "conversation_id": 1,
    "feedback": {
        "rating": 5,
        "comment": "Excelente servicio",
        "resolved": true
    }
}
```

## 🎯 Categorías de FAQs

El sistema usa enum `FaqCategory` con las siguientes categorías:
- `general` - Preguntas generales
- `academico` - Inscripciones, cursos, certificados
- `tecnico` - Problemas técnicos, acceso
- `pagos` - Métodos de pago, facturación
- `soporte` - Atención al cliente

## 🧠 Algoritmo de Matching de FAQs

El `ChatbotRepository` usa un algoritmo de similitud de coseno:

```php
private function calculateSimilarity(string $text1, string $text2): float
{
    // Convierte textos a vectores de palabras
    // Calcula producto punto entre vectores
    // Normaliza con magnitudes de vectores
    // Retorna similitud (0.0 - 1.0)
}
```

**Threshold:** 60% de similitud para considerar match válido

Si no encuentra match en FAQs → usa Gemini AI

## ⚙️ Configuración Dinámica

### Valores configurables:
```json
{
    "enabled": true,
    "greeting_message": "¡Hola! Soy el asistente virtual...",
    "fallback_message": "Lo siento, no entendí tu pregunta...",
    "response_delay": 1000,
    "max_conversations_per_day": 1000,
    "contact_threshold": 3
}
```

### Cache:
- **Duración:** 24 horas
- **Backup:** 48 horas
- **Driver:** Configurado en `config/cache.php`

## 📝 Testeo con Postman

1. Importa el archivo `CHATBOT_GEMINI_POSTMAN.json` en Postman
2. Configura las variables de entorno:
   - `base_url`: `http://localhost:8000`
   - `auth_token`: (tu token de autenticación para endpoints admin)

3. Ejecuta en orden:
   - **5. Sample FAQs Data** - Crea FAQs de prueba
   - **1.1 Start Conversation** - Inicia conversación
   - **1.2 Send Message** - Prueba matching con FAQs
   - **1.3 Send Message** - Prueba respuesta con Gemini AI
   - **1.4 End Conversation** - Finaliza con feedback

## 📈 Analytics Disponibles

```json
GET /api/developer-web/chatbot/analytics/summary

{
    "total": 150,
    "resolved": 120,
    "active": 30,
    "resolved_rate": 0.8,
    "avg_satisfaction": 4.5,
    "handed_to_human": 10,
    "faqs_by_category": [...],
    "most_used_faqs": [...],
    "conversations_by_day": [...]
}
```

## 🔍 Logs

Todos los eventos se registran en Laravel logs:
- Inicio/fin de conversaciones
- Llamadas a Gemini API (con primeros 10 caracteres del API key)
- Matching de FAQs
- Errores y excepciones

## 🛠️ Troubleshooting

### Error: "Gemini API Key no configurada"
- Verifica que `GEMINI_API_KEY` esté en `.env`
- Ejecuta `php artisan config:clear`

### Bot no responde / Error 500
- Revisa logs en `storage/logs/laravel.log`
- Verifica conectividad con Gemini API
- Confirma que el cache driver esté funcionando

### FAQs no se encuentran
- Verifica que las FAQs tengan `active = true`
- Revisa keywords y similitud de texto
- Threshold actual: 60% (ajustable en `ChatbotRepository::findMatchingFaq`)

## 📌 Notas Importantes

1. **Modelo de Gemini:** `gemini-2.0-flash-lite` (configurable en `GeminiChatbotService`)
2. **Timeout API:** 30 segundos con 3 reintentos
3. **Temperature:** 0.7 (respuestas balanceadas entre creatividad y precisión)
4. **Max Tokens:** 500 por respuesta
5. **Prompt del sistema:** Optimizado para contexto educativo de Incadev

## 🎨 Personalización

### Cambiar el prompt del sistema:
Edita `GeminiChatbotService::buildPrompt()`:
```php
private function buildPrompt(string $message): string
{
    return "Eres un asistente virtual para [TU CONTEXTO]...";
}
```

### Ajustar threshold de similitud:
Edita `ChatbotRepository::findMatchingFaq()`:
```php
if ($similarity > 0.6) { // Cambia 0.6 por tu threshold deseado
    return $faq;
}
```

## ✅ Ventajas de esta Implementación

1. ✅ **Doble capa de respuesta:** FAQs primero, Gemini como fallback
2. ✅ **Algoritmo inteligente:** Similitud de coseno para matching preciso
3. ✅ **Configuración dinámica:** Sin necesidad de redeployar
4. ✅ **Analytics completos:** Tracking de uso y satisfacción
5. ✅ **Manejo robusto de errores:** Mensajes de fallback configurables
6. ✅ **Logs detallados:** Debugging fácil
7. ✅ **Cache optimizado:** Mejor performance
8. ✅ **Categorización:** FAQs organizadas por dominio

---

**Desarrollado con:** Laravel + Gemini 2.0 Flash Lite
**Última actualización:** 2025-11-23
