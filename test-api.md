# Test de las rutas API

## Resumen del problema:
Las peticiones POST a `/api/lms/categories` están redirigiendo a `/` (welcome page) en lugar de llegar al controlador.

## Cambios realizados:

1. ✅ Agregado `api: __DIR__.'/../routes/api.php'` en `bootstrap/app.php`
2. ✅ Cambiado prefijo de `'api/lms'` a `'lms'` en `app/Domains/Lms/routes.php`
3. ✅ Eliminada la carga duplicada de rutas desde `DomainServiceProvider`
4. ✅ Corregido error en `welcome.blade.php` (Route::has -> \Illuminate\Support\Facades\Route::has)

## Próximos pasos para probar:

1. **Limpiar la caché de Laravel:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan optimize:clear
   ```

2. **Verificar que las rutas se carguen correctamente:**
   ```bash
   php artisan route:list --path=api/lms
   ```

3. **Probar la petición nuevamente en Postman:**
   - Method: POST
   - URL: http://127.0.0.1:8000/api/lms/categories
   - Headers:
     - Content-Type: application/json
     - Accept: application/json
   - Body (raw JSON):
     ```json
     {
         "name": "Desarrollo Web",
         "slug": "desarrollo-web",
         "image": "https://example.com/images/desarrollo-web.jpg",
         "category_id": null
     }
     ```

4. **Si sigue sin funcionar, verificar:**
   - Que el servidor Laravel esté corriendo: `php artisan serve`
   - Revisar el archivo `bootstrap/cache/routes-v7.php` (si existe, eliminarlo)
   - Verificar logs: `storage/logs/laravel.log`

## URL correcta de las rutas:
- GET    http://127.0.0.1:8000/api/lms/categories
- GET    http://127.0.0.1:8000/api/lms/categories/{id}
- POST   http://127.0.0.1:8000/api/lms/categories
- PUT    http://127.0.0.1:8000/api/lms/categories/{id}
- DELETE http://127.0.0.1:8000/api/lms/categories/{id}
