# Guia de instalacion con XAMPP

Esta guia explica paso a paso como levantar la version PHP del proyecto
`Interfaz-php` en local usando XAMPP. Al terminar tendras:

- Backend PHP + MySQL corriendo en `http://localhost/Interfaz-php/backend/`
- Frontend Vue (dev) corriendo en `http://localhost:5173`
- Las dos partes comunicadas por el proxy de Vite (cookies de sesion OK)

---

## 1. Instalar XAMPP

1. Descarga XAMPP desde <https://www.apachefriends.org/es/index.html>
2. Instala en la ruta por defecto (`C:\xampp` en Windows).
3. Marca al menos los modulos: **Apache** y **MySQL**.

## 2. Colocar el proyecto dentro de `htdocs`

Apache sirve los archivos que hay dentro de `C:\xampp\htdocs`.

- Opcion A (recomendada): **clonar** el repo directamente alli:
  ```
  cd C:\xampp\htdocs
  git clone https://github.com/Liset17/Interfaz-php.git
  ```
- Opcion B: mover la carpeta `Interfaz-php` que ya tienes en tu PC
  a `C:\xampp\htdocs\Interfaz-php`.

Al final la ruta del backend debe ser:
```
C:\xampp\htdocs\Interfaz-php\backend\
```

## 3. Arrancar Apache y MySQL

1. Abre el **XAMPP Control Panel**.
2. Pulsa **Start** en Apache y en MySQL.
3. Deben quedar ambos en verde.

> Si Apache falla por el puerto 80, cambia a 8080 en `Config -> Apache (httpd.conf)`
> o cierra la app que lo esta usando (normalmente Skype o IIS).

## 4. Crear la base de datos

1. Abre <http://localhost/phpmyadmin> en el navegador.
2. En la barra lateral pulsa **Nueva**.
3. Nombre: `academia_teatro`. Cotejamiento: `utf8mb4_unicode_ci`. Crear.
4. Con la base de datos abierta, entra a la pestana **SQL**.
5. Abre el archivo `backend/schema.sql` del proyecto, copia TODO el contenido
   y pegalo en el recuadro. Pulsa **Continuar**.
6. Te deben aparecer 5 tablas: `profesores`, `grupos`, `alumnos`, `clases`,
   `asistencia`.

> Si quieres arrancar con datos de ejemplo, descomenta el bloque al final
> del `schema.sql` (lineas entre `/* ... */`) y vuelve a ejecutarlo.

## 5. Probar el backend

Abre en el navegador:
```
http://localhost/Interfaz-php/backend/
```
Debe devolver un JSON con la lista de endpoints. Si ves un 404, revisa
que la carpeta este en `htdocs\Interfaz-php` y Apache este en verde.

## 6. Instalar y arrancar el frontend

El frontend vive en `frontend/`. Necesitas Node.js 18+ instalado.

```
cd C:\xampp\htdocs\Interfaz-php\frontend
npm install
npm run dev
```

Abre la URL que te muestra Vite, normalmente <http://localhost:5173>.

## 7. Como se comunican frontend y backend

- El frontend llama a rutas relativas tipo `/api/auth/login.php`.
- En `vite.config.js` hay un proxy que reenvia todo `/api/*` a
  `http://localhost/Interfaz-php/backend`.
- Como ambas URLs comparten el dominio `localhost`, la cookie de sesion
  PHP viaja correctamente. Sin proxy habria lios de CORS y cookies.

## 8. Primer uso

1. En <http://localhost:5173> entra a `/register` y crea un profesor
   (el registro hace login automaticamente).
2. Vas al panel principal y pruebas cada seccion: Alumnos, Grupos,
   Clases, Asistencia.
3. Los datos se guardan en la base de datos `academia_teatro` de XAMPP.
   Lo puedes comprobar desde phpMyAdmin.

## 9. Despliegue (opcional, todo en XAMPP)

Si quieres servir TODO desde Apache sin `npm run dev`:

```
cd frontend
npm run build
```

Eso genera la carpeta `frontend/dist/`. Copia su contenido a
`C:\xampp\htdocs\Interfaz-php\public` (o a otra carpeta dentro de htdocs)
y accede a ella desde el navegador. Como el backend y el frontend compartiran
origin, el proxy deja de hacer falta.

## 10. Problemas habituales

| Sintoma | Posible causa |
| --- | --- |
| "No se pudo conectar con el servidor" en el frontend | Apache/MySQL apagados en XAMPP |
| 401 "No autenticado" en todas las llamadas | No se envia la cookie; revisa que Vite use el proxy y no llames con otro dominio |
| "Access denied for user root@localhost" en PHP | Cambia `$DB_PASS` en `backend/config/db.php` si pusiste password a MySQL |
| phpMyAdmin pide usuario y contrasena y no funciona | Usuario `root`, password vacia (por defecto en XAMPP) |
| La pagina `http://localhost/Interfaz-php/backend/` descarga el archivo en vez de ejecutarlo | Apache no esta arrancado, o la carpeta no esta en `htdocs\` |
| Error CORS en consola | Llamaste al backend directo a `http://localhost/...` en vez de usar `/api/...`; usa siempre rutas relativas |
