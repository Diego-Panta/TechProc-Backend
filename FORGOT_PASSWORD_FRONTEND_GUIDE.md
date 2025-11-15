# 📧 Guía de Implementación Frontend - Recuperación de Contraseña

## 📋 Tabla de Contenidos

1. [Resumen del Sistema](#resumen-del-sistema)
2. [Flujo Completo](#flujo-completo)
3. [Endpoints del Backend](#endpoints-del-backend)
4. [Páginas del Frontend](#páginas-del-frontend)
5. [Componentes Requeridos](#componentes-requeridos)
6. [Ejemplos de Código](#ejemplos-de-código)
7. [Validaciones y Errores](#validaciones-y-errores)
8. [Testing](#testing)

---

## 🎯 Resumen del Sistema

El sistema de recuperación de contraseña utiliza **recovery_email** (email de recuperación) en lugar del email principal. Esto permite a los usuarios recuperar su cuenta cuando no tienen acceso a su email principal.

### Características Principales:

- ✅ Usa `recovery_email` (no email principal)
- ✅ Solo recovery emails **verificados** pueden recuperar contraseñas
- ✅ Email enviado vía **Brevo** al recovery_email
- ✅ Token expira según configuración (default: 60 minutos)
- ✅ Frontend URL: `http://localhost:4321`

---

## 🔄 Flujo Completo

```
┌─────────────────────────────────────────────────────────────┐
│ 1. SOLICITAR RECUPERACIÓN                                   │
│    Usuario ingresa su recovery_email                        │
│    POST /api/auth/forgot-password                          │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. BACKEND VALIDA                                           │
│    - Recovery email existe?                                 │
│    - Está verificado?                                       │
│    - Genera token único                                     │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. EMAIL ENVIADO                                            │
│    Destino: recovery_email (antoni.sagitario21@gmail.com)  │
│    Contenido: Link con token                               │
│    URL: http://localhost:4321/tecnologico/reset-password?  │
│         token=XXX&email=YYY                                 │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. USUARIO HACE CLIC                                        │
│    Abre el link del email                                   │
│    Frontend extrae token y email de la URL                 │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. RESETEAR CONTRASEÑA                                      │
│    Usuario ingresa nueva contraseña                        │
│    POST /api/auth/reset-password                           │
│    Body: { email, token, password, password_confirmation } │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 6. ÉXITO                                                    │
│    Contraseña actualizada                                   │
│    Todos los tokens revocados                              │
│    Usuario redirigido al login                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔌 Endpoints del Backend

### 1. Solicitar Recuperación de Contraseña

**Endpoint:** `POST /api/auth/forgot-password`

**Request Body:**
```json
{
  "email": "antoni.sagitario21@gmail.com"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Se ha enviado un enlace de recuperación a tu correo de recuperación."
}
```

**Respuesta Error - Email no existe o no verificado (404):**
```json
{
  "success": false,
  "message": "No existe una cuenta con este correo de recuperación o no ha sido verificado."
}
```

**Respuesta Error - Validación (422):**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "email": [
      "The email field is required.",
      "The email must be a valid email address."
    ]
  }
}
```

---

### 2. Resetear Contraseña

**Endpoint:** `POST /api/auth/reset-password`

**Request Body:**
```json
{
  "email": "admin@incadev.com",
  "token": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Contraseña actualizada exitosamente. Puedes iniciar sesión con tu nueva contraseña."
}
```

**Respuesta Error - Token inválido o expirado (400):**
```json
{
  "success": false,
  "message": "Token inválido o expirado. Solicita un nuevo enlace de recuperación."
}
```

**Respuesta Error - Validación (422):**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "password": [
      "The password must be at least 8 characters.",
      "The password confirmation does not match."
    ]
  }
}
```

---

## 📄 Páginas del Frontend

### Página 1: Solicitar Recuperación (`/forgot-password`)

**URL:** `http://localhost:4321/forgot-password`

**Componentes:**
- Formulario con campo de email
- Botón "Enviar enlace de recuperación"
- Mensajes de éxito/error
- Link para volver al login

**Estados:**
- `loading`: Mientras se envía la petición
- `success`: Email enviado exitosamente
- `error`: Error al enviar (mostrar mensaje)

**Mockup:**
```
┌────────────────────────────────────────────┐
│                                            │
│  🔒 Recuperar Contraseña                   │
│                                            │
│  Ingresa tu correo de recuperación:        │
│  ┌──────────────────────────────────────┐  │
│  │ antoni.sagitario21@gmail.com         │  │
│  └──────────────────────────────────────┘  │
│                                            │
│  ┌──────────────────────────────────────┐  │
│  │   Enviar enlace de recuperación    │  │
│  └──────────────────────────────────────┘  │
│                                            │
│  ← Volver al inicio de sesión              │
│                                            │
└────────────────────────────────────────────┘
```

---

### Página 2: Resetear Contraseña (`/tecnologico/reset-password`)

**URL:** `http://localhost:4321/tecnologico/reset-password?token=XXX&email=YYY`

**Query Parameters:**
- `token`: Token de reseteo (viene del email)
- `email`: Email principal del usuario (viene del email)

**Componentes:**
- Campo de nueva contraseña (password)
- Campo de confirmar contraseña (password_confirmation)
- Botón "Cambiar contraseña"
- Indicador de fortaleza de contraseña
- Mensajes de éxito/error

**Estados:**
- `loading`: Mientras se resetea
- `success`: Contraseña cambiada (redirigir a login)
- `error`: Token expirado o inválido

**Mockup:**
```
┌────────────────────────────────────────────┐
│                                            │
│  🔐 Restablecer Contraseña                 │
│                                            │
│  Nueva contraseña:                         │
│  ┌──────────────────────────────────────┐  │
│  │ ••••••••••••                         │  │
│  └──────────────────────────────────────┘  │
│  Fortaleza: ████████░░ Fuerte              │
│                                            │
│  Confirmar contraseña:                     │
│  ┌──────────────────────────────────────┐  │
│  │ ••••••••••••                         │  │
│  └──────────────────────────────────────┘  │
│                                            │
│  ┌──────────────────────────────────────┐  │
│  │      Cambiar contraseña          │  │
│  └──────────────────────────────────────┘  │
│                                            │
└────────────────────────────────────────────┘
```

---

## 🧩 Componentes Requeridos

### 1. Formulario de Solicitud de Recuperación

```typescript
interface ForgotPasswordFormProps {
  onSuccess: () => void;
  onError: (message: string) => void;
}

interface ForgotPasswordFormData {
  email: string;
}
```

### 2. Formulario de Reset de Contraseña

```typescript
interface ResetPasswordFormProps {
  token: string;
  email: string;
  onSuccess: () => void;
  onError: (message: string) => void;
}

interface ResetPasswordFormData {
  email: string;
  token: string;
  password: string;
  password_confirmation: string;
}
```

### 3. Componentes Auxiliares

- **EmailInput**: Input especializado para emails
- **PasswordInput**: Input con toggle para mostrar/ocultar
- **PasswordStrengthIndicator**: Barra de fortaleza
- **AlertMessage**: Para éxito/error
- **LoadingSpinner**: Indicador de carga

---

## 💻 Ejemplos de Código

### React + TypeScript

#### 1. Página Forgot Password

```typescript
// pages/ForgotPassword.tsx
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess(false);

    try {
      const response = await axios.post(`${API_URL}/auth/forgot-password`, {
        email: email.trim(),
      });

      if (response.data.success) {
        setSuccess(true);
        setEmail('');
      }
    } catch (err: any) {
      if (err.response?.data?.message) {
        setError(err.response.data.message);
      } else {
        setError('Error al enviar el enlace de recuperación');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow">
        <div>
          <h2 className="text-3xl font-bold text-center">
            Recuperar Contraseña
          </h2>
          <p className="mt-2 text-center text-gray-600">
            Ingresa tu correo de recuperación
          </p>
        </div>

        {success && (
          <div className="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
            Se ha enviado un enlace de recuperación a tu correo.
            Revisa tu bandeja de entrada.
          </div>
        )}

        {error && (
          <div className="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700">
              Correo de Recuperación
            </label>
            <input
              id="email"
              name="email"
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="tu-recovery@email.com"
              disabled={loading}
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {loading ? 'Enviando...' : 'Enviar enlace de recuperación'}
          </button>

          <div className="text-center">
            <button
              type="button"
              onClick={() => navigate('/login')}
              className="text-sm text-blue-600 hover:text-blue-500"
            >
              ← Volver al inicio de sesión
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
```

#### 2. Página Reset Password

```typescript
// pages/ResetPassword.tsx
import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

export default function ResetPasswordPage() {
  const [searchParams] = useSearchParams();
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const token = searchParams.get('token');
  const email = searchParams.get('email');

  useEffect(() => {
    if (!token || !email) {
      setError('Link inválido. Por favor solicita un nuevo enlace de recuperación.');
    }
  }, [token, email]);

  const getPasswordStrength = (password: string) => {
    if (password.length < 8) return { text: 'Débil', color: 'red', width: '25%' };
    if (password.length < 12) return { text: 'Media', color: 'yellow', width: '50%' };
    if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password))
      return { text: 'Media', color: 'yellow', width: '50%' };
    return { text: 'Fuerte', color: 'green', width: '100%' };
  };

  const strength = getPasswordStrength(password);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (password !== passwordConfirmation) {
      setError('Las contraseñas no coinciden');
      return;
    }

    if (password.length < 8) {
      setError('La contraseña debe tener al menos 8 caracteres');
      return;
    }

    setLoading(true);
    setError('');

    try {
      const response = await axios.post(`${API_URL}/auth/reset-password`, {
        email,
        token,
        password,
        password_confirmation: passwordConfirmation,
      });

      if (response.data.success) {
        // Mostrar mensaje de éxito
        alert('Contraseña actualizada exitosamente. Redirigiendo al login...');
        navigate('/login');
      }
    } catch (err: any) {
      if (err.response?.data?.message) {
        setError(err.response.data.message);
      } else if (err.response?.data?.errors) {
        const errors = Object.values(err.response.data.errors).flat();
        setError(errors.join(', '));
      } else {
        setError('Error al resetear la contraseña');
      }
    } finally {
      setLoading(false);
    }
  };

  if (!token || !email) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded">
          Link inválido. Por favor solicita un nuevo enlace de recuperación.
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow">
        <div>
          <h2 className="text-3xl font-bold text-center">
            Restablecer Contraseña
          </h2>
          <p className="mt-2 text-center text-gray-600">
            Ingresa tu nueva contraseña
          </p>
        </div>

        {error && (
          <div className="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <label htmlFor="password" className="block text-sm font-medium text-gray-700">
              Nueva Contraseña
            </label>
            <input
              id="password"
              name="password"
              type="password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="••••••••"
              disabled={loading}
            />
            {password && (
              <div className="mt-2">
                <div className="flex justify-between text-sm mb-1">
                  <span className="text-gray-600">Fortaleza:</span>
                  <span className={`text-${strength.color}-600 font-medium`}>
                    {strength.text}
                  </span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div
                    className={`bg-${strength.color}-600 h-2 rounded-full transition-all`}
                    style={{ width: strength.width }}
                  />
                </div>
              </div>
            )}
          </div>

          <div>
            <label htmlFor="password_confirmation" className="block text-sm font-medium text-gray-700">
              Confirmar Contraseña
            </label>
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              required
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              placeholder="••••••••"
              disabled={loading}
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {loading ? 'Cambiando contraseña...' : 'Cambiar contraseña'}
          </button>
        </form>
      </div>
    </div>
  );
}
```

---

### Vue 3 + TypeScript

#### Forgot Password Component

```vue
<!-- pages/ForgotPassword.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const API_URL = 'http://localhost:8000/api';

const email = ref('');
const loading = ref(false);
const success = ref(false);
const error = ref('');
const router = useRouter();

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';
  success.value = false;

  try {
    const response = await axios.post(`${API_URL}/auth/forgot-password`, {
      email: email.value.trim(),
    });

    if (response.data.success) {
      success.value = true;
      email.value = '';
    }
  } catch (err: any) {
    if (err.response?.data?.message) {
      error.value = err.response.data.message;
    } else {
      error.value = 'Error al enviar el enlace de recuperación';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow">
      <div>
        <h2 class="text-3xl font-bold text-center">Recuperar Contraseña</h2>
        <p class="mt-2 text-center text-gray-600">
          Ingresa tu correo de recuperación
        </p>
      </div>

      <div v-if="success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
        Se ha enviado un enlace de recuperación a tu correo.
      </div>

      <div v-if="error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
        {{ error }}
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">
            Correo de Recuperación
          </label>
          <input
            id="email"
            v-model="email"
            type="email"
            required
            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
            placeholder="tu-recovery@email.com"
            :disabled="loading"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
        >
          {{ loading ? 'Enviando...' : 'Enviar enlace de recuperación' }}
        </button>

        <div class="text-center">
          <button
            type="button"
            @click="router.push('/login')"
            class="text-sm text-blue-600 hover:text-blue-500"
          >
            ← Volver al inicio de sesión
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
```

---

## ⚠️ Validaciones y Errores

### Validaciones en el Frontend

#### Email de Recuperación:
```typescript
const validateEmail = (email: string): string | null => {
  if (!email) return 'El email es requerido';
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    return 'Email inválido';
  }
  return null;
};
```

#### Contraseña:
```typescript
const validatePassword = (password: string): string | null => {
  if (!password) return 'La contraseña es requerida';
  if (password.length < 8) {
    return 'La contraseña debe tener al menos 8 caracteres';
  }
  if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
    return 'Debe contener mayúsculas, minúsculas y números';
  }
  return null;
};

const validatePasswordConfirmation = (
  password: string,
  confirmation: string
): string | null => {
  if (password !== confirmation) {
    return 'Las contraseñas no coinciden';
  }
  return null;
};
```

### Manejo de Errores del Backend

```typescript
interface ApiError {
  success: false;
  message: string;
  errors?: Record<string, string[]>;
}

const handleApiError = (error: any): string => {
  if (error.response?.data?.message) {
    return error.response.data.message;
  }

  if (error.response?.data?.errors) {
    const errors = Object.values(error.response.data.errors).flat();
    return errors.join(', ');
  }

  return 'Error al procesar la solicitud';
};
```

---

## 🧪 Testing

### Casos de Prueba

#### 1. Forgot Password Page

```typescript
describe('ForgotPasswordPage', () => {
  test('debe mostrar error si el email no existe', async () => {
    // Mock API response
    // Verificar mensaje de error
  });

  test('debe mostrar éxito si el email existe y está verificado', async () => {
    // Mock API response
    // Verificar mensaje de éxito
  });

  test('debe validar formato de email', async () => {
    // Probar con emails inválidos
  });

  test('debe deshabilitar botón mientras está cargando', async () => {
    // Verificar estado de loading
  });
});
```

#### 2. Reset Password Page

```typescript
describe('ResetPasswordPage', () => {
  test('debe mostrar error si no hay token en URL', () => {
    // Verificar mensaje de error
  });

  test('debe validar que las contraseñas coincidan', async () => {
    // Probar con contraseñas diferentes
  });

  test('debe validar longitud mínima de contraseña', async () => {
    // Probar con contraseña corta
  });

  test('debe resetear contraseña exitosamente', async () => {
    // Mock API response
    // Verificar redirección a login
  });

  test('debe mostrar error si el token expiró', async () => {
    // Mock API response con token expirado
  });
});
```

---

## 📝 Checklist de Implementación

### Backend
- [x] Endpoint `/api/auth/forgot-password` implementado
- [x] Endpoint `/api/auth/reset-password` implementado
- [x] Sistema de recovery_email configurado
- [x] Notificaciones de email configuradas (Brevo)
- [x] Validaciones implementadas
- [x] FRONTEND_URL configurado en `.env`

### Frontend
- [ ] Página `/forgot-password` creada
- [ ] Página `/tecnologico/reset-password` creada
- [ ] Validación de formularios implementada
- [ ] Manejo de errores implementado
- [ ] Indicador de fortaleza de contraseña
- [ ] Mensajes de éxito/error
- [ ] Loading states
- [ ] Responsive design
- [ ] Testing implementado

---

## 🔧 Configuración

### Variables de Entorno (.env del Backend)

```env
# Frontend URL (usado en emails)
FRONTEND_URL=http://localhost:4321

# Configuración de email (Brevo)
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@brevo.com
MAIL_PASSWORD=tu-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@techproc.com
MAIL_FROM_NAME="TechProc"

# Tiempo de expiración del token (minutos)
AUTH_PASSWORD_TIMEOUT=60
```

### Axios Config (Frontend)

```typescript
// config/axios.ts
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export default api;
```

---

## 📧 Email que Recibirá el Usuario

```
De: TechProc <noreply@techproc.com>
Para: antoni.sagitario21@gmail.com
Asunto: Recuperación de Contraseña - TechProc

¡Hola!

Estás recibiendo este correo porque recibimos una solicitud de
recuperación de contraseña para tu cuenta.

┌──────────────────────────────────────┐
│   [Restablecer Contraseña]         │
│   http://localhost:4321/tecnologic...│
└──────────────────────────────────────┘

Este enlace de recuperación expirará en 60 minutos.

Si no solicitaste restablecer tu contraseña, no es necesario
realizar ninguna acción.

Saludos, TechProc
```

---

## 🎨 Recomendaciones de UX/UI

1. **Feedback Visual:**
   - Mostrar spinner durante peticiones
   - Animaciones suaves en transiciones
   - Colores consistentes (rojo=error, verde=éxito)

2. **Mensajes Claros:**
   - Evitar jerga técnica
   - Instrucciones paso a paso
   - Tiempos estimados ("Recibirás el email en 2-5 minutos")

3. **Accesibilidad:**
   - Labels claros en inputs
   - Contraste adecuado de colores
   - Navegación con teclado
   - Mensajes para lectores de pantalla

4. **Mobile First:**
   - Inputs grandes para móviles
   - Botones táctiles (min 44px)
   - Responsive design

---

## 📚 Recursos Adicionales

- [Laravel Password Reset Documentation](https://laravel.com/docs/11.x/passwords)
- [React Router v6](https://reactrouter.com/)
- [Axios Documentation](https://axios-http.com/)
- [Tailwind CSS](https://tailwindcss.com/)

---

## ❓ FAQ

**P: ¿Qué pasa si el usuario no tiene recovery_email?**
R: No podrá usar este endpoint. Debe contactar soporte o usar otro método de recuperación.

**P: ¿Cuánto tiempo es válido el token?**
R: 60 minutos por defecto (configurable en `config/auth.php`).

**P: ¿El email se envía al recovery_email o al email principal?**
R: Se envía al **recovery_email** (antoni.sagitario21@gmail.com), pero el link contiene el **email principal** (admin@incadev.com).

**P: ¿Qué pasa si el token expira?**
R: El usuario debe solicitar un nuevo enlace de recuperación.

**P: ¿Se pueden usar ambos emails (principal y recovery)?**
R: Actualmente solo recovery_email. Si quieres usar ambos, hay que modificar el endpoint.

---

## 📞 Soporte

Si tienes dudas sobre la implementación, contacta al equipo de backend.

**Última actualización:** 2025-01-15
