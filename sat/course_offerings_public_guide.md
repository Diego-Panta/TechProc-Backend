# 📚 Endpoint Público - Cursos del Último Período Académico

## 🌐 Endpoint PÚBLICO (Sin autenticación)

### GET - Obtener cursos del último período académico
```http
GET /api/lms/course-offerings/public/latest-period
```

**⭐ NO requiere token de autenticación** - Perfecto para tu website público

---

## 📋 Información del Endpoint

**URL completa:**
```
http://localhost:8000/api/lms/course-offerings/public/latest-period
```

**Método:** `GET`

**Headers:** Ninguno requerido

**Query Parameters:** Ninguno

---

## 🎯 Funcionalidad

Este endpoint:
1. ✅ Busca el último período académico con `status = 'open'`
2. ✅ Obtiene todos los `course_offerings` de ese período
3. ✅ Incluye información completa de:
   - Curso (course)
   - Período académico (academic_period)
   - Instructor y su usuario (instructor.user)

---

## 📊 Estructura de Respuesta

### Respuesta exitosa con datos (200):

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "course_offering_id": 1001,
      "course_id": 5,
      "academic_period_id": 3,
      "instructor_id": 2,
      "schedule": "Lunes y Miércoles 18:00-20:00",
      "delivery_method": "regular",
      "created_at": "2025-01-15T10:00:00.000000Z",
      "course": {
        "id": 5,
        "course_id": 105,
        "title": "Introducción a Python",
        "name": "Python Básico",
        "description": "Aprende los fundamentos de Python desde cero",
        "level": "basic",
        "course_image": "https://example.com/images/python.jpg",
        "video_url": "https://youtube.com/watch?v=xyz",
        "duration": 40.00,
        "sessions": 12,
        "selling_price": 299.99,
        "discount_price": 199.99,
        "prerequisites": "Ninguno",
        "certificate_name": true,
        "certificate_issuer": "TechProc Academy",
        "bestseller": false,
        "featured": true,
        "highest_rated": false,
        "status": true,
        "created_at": "2025-01-10T08:00:00.000000Z"
      },
      "academic_period": {
        "id": 3,
        "academic_period_id": 2025001,
        "name": "Ciclo 2025-I",
        "start_date": "2025-01-15",
        "end_date": "2025-06-30",
        "status": "open",
        "created_at": "2025-01-01T00:00:00.000000Z"
      },
      "instructor": {
        "id": 2,
        "instructor_id": 1002,
        "user_id": 45,
        "bio": "Desarrollador con 10 años de experiencia en Python",
        "expertise_area": "Backend Development, Data Science",
        "status": "active",
        "created_at": "2024-12-01T00:00:00.000000Z",
        "user": {
          "id": 45,
          "first_name": "María",
          "last_name": "González",
          "full_name": "María González",
          "email": "maria.gonzalez@techproc.com",
          "phone_number": "+51987654321",
          "profile_photo": "https://example.com/profiles/maria.jpg"
        }
      }
    },
    {
      "id": 2,
      "course_offering_id": 1002,
      "course_id": 8,
      "academic_period_id": 3,
      "instructor_id": 5,
      "schedule": "Martes y Jueves 16:00-18:00",
      "delivery_method": "online",
      "created_at": "2025-01-15T10:30:00.000000Z",
      "course": {
        "id": 8,
        "title": "JavaScript Avanzado",
        "name": "JS Advanced",
        "description": "Domina JavaScript moderno y frameworks",
        "level": "advanced",
        ...
      },
      "academic_period": {
        "id": 3,
        "name": "Ciclo 2025-I",
        ...
      },
      "instructor": {
        "id": 5,
        "user": {
          "first_name": "Carlos",
          "last_name": "Rodríguez",
          ...
        }
      }
    }
  ]
}
```

### Respuesta cuando no hay cursos disponibles (200):

```json
{
  "success": true,
  "message": "No hay cursos disponibles en el período académico actual",
  "data": []
}
```

---

## 🔍 Campos Importantes

### Course Offering:
- `id` - ID del course offering
- `schedule` - Horario del curso
- `delivery_method` - Método de entrega (regular, online, hybrid)

### Course (curso):
- `title` - Título del curso
- `description` - Descripción completa
- `level` - Nivel: basic, intermediate, advanced
- `course_image` - URL de la imagen del curso
- `video_url` - URL del video promocional
- `duration` - Duración en horas
- `sessions` - Número de sesiones
- `selling_price` - Precio regular
- `discount_price` - Precio con descuento
- `bestseller` - ¿Es bestseller?
- `featured` - ¿Está destacado?
- `highest_rated` - ¿Mejor calificado?

### Academic Period:
- `name` - Nombre del período (ej: "Ciclo 2025-I")
- `start_date` - Fecha de inicio
- `end_date` - Fecha de fin
- `status` - Estado (open, closed)

### Instructor:
- `bio` - Biografía del instructor
- `expertise_area` - Área de expertise
- `user.first_name` - Nombre
- `user.last_name` - Apellido
- `user.email` - Email
- `user.profile_photo` - Foto de perfil

---

## 💻 Ejemplos de Uso

### cURL:
```bash
curl -X GET "http://localhost:8000/api/lms/course-offerings/public/latest-period"
```

### JavaScript (Fetch):
```javascript
fetch('http://localhost:8000/api/lms/course-offerings/public/latest-period')
  .then(response => response.json())
  .then(data => {
    console.log('Cursos disponibles:', data.data);
    data.data.forEach(courseOffering => {
      console.log(`Curso: ${courseOffering.course.title}`);
      console.log(`Instructor: ${courseOffering.instructor.user.full_name}`);
      console.log(`Horario: ${courseOffering.schedule}`);
    });
  })
  .catch(error => console.error('Error:', error));
```

### Axios:
```javascript
import axios from 'axios';

async function getCourses() {
  try {
    const response = await axios.get(
      'http://localhost:8000/api/lms/course-offerings/public/latest-period'
    );

    const courses = response.data.data;
    console.log(`${courses.length} cursos disponibles`);

    return courses;
  } catch (error) {
    console.error('Error al obtener cursos:', error);
  }
}
```

### React Example:
```jsx
import { useState, useEffect } from 'react';

function CoursesPage() {
  const [courses, setCourses] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetch('http://localhost:8000/api/lms/course-offerings/public/latest-period')
      .then(res => res.json())
      .then(data => {
        setCourses(data.data);
        setLoading(false);
      });
  }, []);

  if (loading) return <div>Cargando cursos...</div>;

  return (
    <div className="courses-grid">
      {courses.map(offering => (
        <div key={offering.id} className="course-card">
          <img src={offering.course.course_image} alt={offering.course.title} />
          <h3>{offering.course.title}</h3>
          <p>{offering.course.description}</p>
          <div className="instructor">
            Por: {offering.instructor.user.full_name}
          </div>
          <div className="price">
            {offering.course.discount_price
              ? `$${offering.course.discount_price}`
              : `$${offering.course.selling_price}`}
          </div>
          <div className="schedule">{offering.schedule}</div>
        </div>
      ))}
    </div>
  );
}
```

---

## 📦 Importar en Postman

**Archivo:** `course_offerings_public_postman_collection.json`

1. Abre Postman
2. Click en "Import"
3. Selecciona el archivo JSON
4. Configura la variable `base_url` a tu servidor

---

## 🔧 Configuración de Variables Postman

```
base_url = http://localhost:8000
```

---

## ✅ Testing Checklist

- [ ] Probar el endpoint sin token (debe funcionar)
- [ ] Verificar que retorna cursos del período actual
- [ ] Verificar que incluye información del curso completo
- [ ] Verificar que incluye datos del instructor
- [ ] Verificar que incluye información del período académico
- [ ] Probar cuando no hay cursos disponibles

---

## 🎨 Casos de Uso para tu Website

### 1. Página de Cursos Disponibles
Muestra todos los cursos del ciclo actual en una grid.

### 2. Homepage - Cursos Destacados
Filtra los cursos con `featured: true` para mostrar en la homepage.

### 3. Cursos Bestsellers
Filtra los cursos con `bestseller: true`.

### 4. Filtros por Nivel
Filtra por `level`: basic, intermediate, advanced.

### 5. Búsqueda
Implementa búsqueda por título, descripción o instructor.

---

## 🚀 Optimizaciones para Producción

### Caché en Frontend:
```javascript
// Cachear los cursos por 5 minutos
const CACHE_TIME = 5 * 60 * 1000;
let cachedCourses = null;
let cacheTimestamp = null;

async function getCachedCourses() {
  const now = Date.now();

  if (cachedCourses && (now - cacheTimestamp) < CACHE_TIME) {
    return cachedCourses;
  }

  const response = await fetch('/api/lms/course-offerings/public/latest-period');
  const data = await response.json();

  cachedCourses = data.data;
  cacheTimestamp = now;

  return cachedCourses;
}
```

---

## 📊 Estructura de Base de Datos

```
academic_periods (períodos académicos)
  ├── id
  ├── name ("Ciclo 2025-I")
  ├── start_date
  ├── end_date
  └── status ("open", "closed")

course_offerings (ofertas de cursos)
  ├── id
  ├── course_id → courses
  ├── academic_period_id → academic_periods
  ├── instructor_id → instructors
  ├── schedule
  └── delivery_method

courses (cursos)
  ├── id
  ├── title
  ├── description
  ├── level
  ├── selling_price
  └── ...

instructors (instructores)
  ├── id
  ├── user_id → users
  ├── bio
  └── expertise_area

users (usuarios)
  ├── id
  ├── first_name
  ├── last_name
  └── email
```

---

**🎉 Listo para usar en tu website!**

Este endpoint es público y no requiere autenticación, perfecto para mostrar cursos disponibles a todos los visitantes de tu sitio web.
