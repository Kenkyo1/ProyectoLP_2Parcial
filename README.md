# ProyectoLP_2Parcial
# Backend PHP - App web de Denuncias

## Contenido
- init_db.sql : script para crear la base de datos y tablas.
- conexion.php : conexión a MySQL.
- auth.php : helpers de sesión y verificación.
- registro.html / registro.php : registro de usuarios.
- login.html / login.php : inicio de sesión.
- dashboard.php : área protegida - crear denuncias, ver propias y públicas.
- registrar_denuncia.php : guarda denuncias (con subida de imagen).
- ver_denuncias.php : API pública que devuelve JSON con todas las denuncias.
- editar_denuncia.php : editar denuncias propias.
- eliminar_denuncia.php : eliminar denuncias propias.
- uploads/ : carpeta para imágenes subidas.

## Instrucciones rápidas
1. Copia la carpeta `denuncias_backend` dentro de la carpeta `htdocs` (XAMPP) o en tu servidor local PHP.
2. Importa `init_db.sql` en phpMyAdmin para crear la base de datos `denuncias_db`. En caso de no crearse mediante el .sql crear la base manualmente desde phpMyAdmin. Después, importar `init_db.sql` desde la base creada.
3. Asegúrate de que `conexion.php` contenga las credenciales correctas de MySQL. Es decir, verifica las variables de host, user, pass, db y port sean las correctas para conectarse correctamente a la base de datos creada.
4. Accede a `http://localhost:[puerto]/denuncias_backend/registro.html` para crear un usuario o `login.html` para iniciar sesión. En `[puerto]` debes ubicar el puerto que usa tu servidor, en caso de que hayas modificado el por defecto.
5. Desde `dashboard.php` puedes crear, editar y eliminar tus denuncias, además de ver todas las denuncias públicas.
6. Si trabajas con Visual Studio Code, abre la carpeta del proyecto y usa la extensión PHP Server o ejecuta XAMPP.

## Notas de seguridad y mejoras futuras
- Las contraseñas usan `password_hash` y `password_verify`.
- Todas las consultas usan `prepare` para evitar inyección SQL.
- Validación mínima en uploads (tipo y tamaño). Para producción, mejorar validaciones y manejo de sesiones.
