# Interfaz Profesor - Version PHP

Panel de profesores de la Academia de Teatro (Proyecto FP Valencia),
reescrito en **PHP puro + MySQL**. Esta version elimina Vue y Vite:
cada pagina es un `.php` que se sirve directamente desde Apache y
consulta la base de datos con PDO.

Es hermana gemela del repo
[`Interfaz-Profesor`](https://github.com/Liset17/Interfaz-Profesor),
que usa Supabase. Aqui el backend esta hecho a mano sobre XAMPP.

## Estructura

```
php-interfaz/
|-- index.php            # Redirige a login.php o home.php segun sesion
|-- login.php            # Formulario de login (POST self)
|-- register.php         # Alta de profesor + login automatico
|-- logout.php           # Destruye la sesion
|-- home.php             # Panel principal con las 4 tarjetas
|-- alumnos.php          # CRUD alumnos (PHP + PDO)
|-- grupos.php           # CRUD grupos + buscador + progress bar
|-- clases.php           # CRUD clases
|-- asistencia.php       # Pasar asistencia (upsert por alumno+fecha)
|-- schema.sql           # Esquema MySQL
|-- config/db.php        # Conexion PDO (respeta db.local.php si existe)
|-- includes/            # bootstrap, auth, header, footer
`-- assets/              # style.css + imagenes
```

No hay `node_modules`, ni `package.json`, ni servidor de desarrollo:
solo XAMPP (Apache + MySQL).

## Como empezar

Mira [`XAMPP_SETUP.md`](XAMPP_SETUP.md) para la guia detallada.

Resumen rapido:

```bash
# 1) Clonar dentro de htdocs
cd C:\xampp\htdocs
git clone https://github.com/Liset17/Interfaz-php.git php-interfaz

# 2) Crear la BBDD `academia_teatro` desde phpMyAdmin
#    e importar schema.sql

# 3) Arrancar Apache + MySQL en XAMPP Control Panel

# 4) Abrir en el navegador:
#    http://localhost/php-interfaz/
```

Registra un profesor, entra al panel y listo.

## Paginas disponibles

| URL (relativa a `/php-interfaz/`) | Accion |
| --- | --- |
| `login.php`       | Iniciar sesion (POST self-submit) |
| `register.php`    | Crear profesor + login automatico |
| `logout.php`      | Cerrar sesion (solo POST) |
| `home.php`        | Panel principal |
| `alumnos.php`     | CRUD de alumnos |
| `grupos.php`      | CRUD de grupos + buscador |
| `clases.php`      | CRUD de clases |
| `asistencia.php`  | Control de asistencia (upsert) |

Todas las paginas salvo `login.php` y `register.php` exigen sesion
iniciada (redirigen a `login.php` si no la hay).

## Decisiones tecnicas

- **PHP puro**: cada pagina ejecuta su propia consulta PDO, sin API
  intermedia. Los formularios usan `method="post"` tradicional y
  siguen el patron **PRG** (Post / Redirect / Get) para que al
  refrescar no se reenvien datos.
- **XSS**: toda salida pasa por `e($valor)` (htmlspecialchars).
- **Sesion**: `session_set_cookie_params` con `HttpOnly` y
  `SameSite=Lax`. El flag `secure` se detecta automaticamente segun
  HTTPS, asi funciona igual en XAMPP local y en produccion HTTPS.
- **Flash messages**: mensajes breves tras crear/editar/borrar, se
  guardan en `$_SESSION['flash']` y se muestran una vez.
- **JavaScript**: solo el minimo (marcar todos los checkboxes de
  asistencia y `onchange=submit()` en los filtros). No hay SPA.

## Autor

Wilbelys Liset Ramirez. Proyecto FP - Valencia.
