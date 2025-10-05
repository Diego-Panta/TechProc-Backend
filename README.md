# 🧠 TechProc Backend — Arquitectura DDD con Laravel

Este proyecto implementa una arquitectura modular **DDD (Domain-Driven Design)** en **Laravel**, separando la lógica por dominios como *LMS*, *Seguridad*, *Soporte*, *Desarrollo Web*, entre otros.  
Además, está diseñado para interactuar con un **frontend separado** (por ejemplo, React) a través de **API REST**.

## 🚀 Características principales

- ✅ Estructura **modular y escalable** basada en DDD.  
- ✅ Separación clara entre **controladores, servicios, repositorios, modelos y requests**.  
- ✅ Integración con **PostgreSQL**.  
- ✅ Configuración lista para **frontend desacoplado** (React, Vue, Angular, etc.).  
- ✅ Endpoints limpios y organizados por dominio.  
- ✅ Ejemplo funcional de **autenticación básica (login)**.

## 🧩 Estructura de Carpetas

El proyecto adopta un enfoque de **Dominios** dentro de `app/Domains/`.  
Cada módulo posee sus propias capas de lógica interna:

```bash

app/
└── Domains/
├── LMS/
│   ├── Http/
│   │   └── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Repositories/
│   └── routes.php
├── Seguridad/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   ├── Repositories/
│   └── routes.php
├── Soporte/
├── Infraestructura/
├── DesarrolloWeb/
└── Analitica/

````

Cada dominio se comporta como un **módulo independiente** con su propio enrutamiento, lógica y modelo de datos.

## ⚙️ Instalación

### 1️⃣ Clonar el repositorio

```bash
git clone https://github.com/usuario/techproc-backend.git
cd techproc-backend
````

### 2️⃣ Instalar dependencias

```bash
composer install
```
### 3️⃣ Crear archivo `.env`

Copia el archivo de ejemplo y ajusta las variables de entorno:

```bash
cp .env.example .env
```

Luego edita las credenciales para PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dbtechproc
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

### 4️⃣ Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 5️⃣ Ejecutar migraciones

```bash
php artisan migrate
```

---

## 🧠 Estructura DDD en Detalle

### 📦 Modelos (`Models/`)

Representan las entidades del dominio.
Ejemplo: `User.php`, `Course.php`, `Ticket.php`.

### ⚙️ Servicios (`Services/`)

Contienen la **lógica de negocio** principal.
Ejemplo: autenticación, procesamiento de datos, reglas de negocio, validaciones personalizadas.

### 🧰 Repositorios (`Repositories/`)

Encapsulan la lógica de acceso a base de datos.
Facilitan el cambio de ORM o fuente de datos sin alterar la lógica del dominio.

### 🚦 Controladores (`Http/Controllers/`)

Se encargan de manejar las **peticiones HTTP**, invocando los servicios correspondientes.

### 🧾 Requests (`Http/Requests/`)

Gestionan la **validación de datos entrantes** en las peticiones POST, PUT, PATCH, etc.

### 🧱 Middleware (`Http/Middleware/`)

Filtran las solicitudes entrantes, por ejemplo: autenticación, permisos, logs, etc.

### 🛣 Rutas (`routes.php`)

Cada dominio posee su propio archivo `routes.php`.
Estos se importan desde `routes/api.php` o `routes/web.php`.

---

## 🔐 Ejemplo: Autenticación (POST /auth/login)

Ejemplo del endpoint incluido para probar la estructura.

**Archivo:** `app/Domains/Seguridad/routes.php`

```php
use Illuminate\Support\Facades\Route;
use App\Domains\Seguridad\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});
```

**Flujo:**

```
Frontend (React)
   ↓ POST /api/auth/login
AuthController
   ↓
AuthService
   ↓
UserRepository
   ↓
Base de datos PostgreSQL
```

---

## 🌍 Integración con Frontend (React)

Tu proyecto React se comunica mediante endpoints REST.

Ejemplo básico usando **Axios**:

```js
import axios from 'axios';

const API_URL = 'http://localhost:8000/api/auth/login';

async function login(email, password) {
  try {
    const response = await axios.post(API_URL, { email, password });
    console.log('Usuario autenticado:', response.data.user);
  } catch (error) {
    console.error('Error en login:', error.response?.data);
  }
}

login('admin@techproc.com', 'admin123');
```

---

## 🧱 Base de datos (PostgreSQL)

### Configuración en `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dbtechproc
DB_USERNAME=postgres
DB_PASSWORD=tu_contraseña
```

### Ejemplo de migración de usuarios:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('username')->unique();
    $table->string('email')->unique();
    $table->string('password');
    $table->string('role')->default('user');
    $table->string('name')->nullable();
    $table->timestamps();
});
```

Ejecutar:

```bash
php artisan migrate
```

---

## 🧩 Cargar rutas de los módulos

En `routes/api.php` agrega:

```php
require base_path('app/Domains/Seguridad/routes.php');
require base_path('app/Domains/LMS/routes.php');
```

Esto permite mantener las rutas organizadas por dominio.

---

## 🧠 Buenas prácticas recomendadas

* Usa **Repositorios** para manejar consultas de base de datos.
* Mantén **los controladores delgados** (solo coordinan acciones).
* Centraliza la lógica en **Servicios**.
* Utiliza **Requests** para validaciones.
* Agrupa **rutas y módulos** por dominio funcional.
* Evita lógica de negocio en controladores o modelos directamente.

---

## 🧪 Pruebas locales

1. Inicia el servidor:

   ```bash
   php artisan serve
   ```

2. Endpoint disponible:

   ```
   POST http://localhost:8000/api/auth/login
   ```

3. Cuerpo de ejemplo:

   ```json
   {
     "email": "admin@techproc.com",
     "password": "admin123"
   }
   ```

4. Respuesta esperada:

   ```json
   {
     "user": { "id": 1, "username": "admin", "email": "admin@techproc.com" },
     "token": "eyJhbGc...",
     "refreshToken": "eyJhbGc..."
   }
   ```

---

## 📁 Licencia

Este proyecto está bajo la licencia MIT.
Puedes modificar y distribuir el código libremente, citando la autoría original.

---

## 👨‍💻 Autor

**Diego Panta P.**
Proyecto de arquitectura DDD con Laravel y React — *TechProc Backend v1.0*
📧 [diego@techproc.com](mailto:diego@techproc.com)

