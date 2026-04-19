# Interfaz Profesor - Version PHP

Version en **PHP + MySQL** del panel de profesores de la Academia de Teatro
(Proyecto FP Valencia). Es hermana gemela del repo
[`Interfaz-Profesor`](https://github.com/Liset17/Interfaz-Profesor), que usa
Supabase; en este caso el backend esta hecho a mano sobre XAMPP.

## Estructura

```
Interfaz-php/
|-- backend/           # API PHP + esquema MySQL
|   |-- api/           # Endpoints (auth + CRUD)
|   |-- config/        # Conexion PDO + CORS
|   |-- includes/      # Helpers y auth check
|   |-- index.php      # Lista los endpoints (GET /)
|   `-- schema.sql     # Base de datos academia_teatro
|
`-- frontend/          # Vue 3 + Vite (UI del profesor)
    |-- src/
    |   |-- lib/api.js        # Cliente fetch central
    |   |-- services/         # auth, alumnos, grupos, clases, asistencia
    |   |-- router/           # Guards de sesion
    |   `-- views/            # home, login, register, alumnos, ...
    `-- vite.config.js        # Proxy /api -> XAMPP
```

## Como empezar

Mira [`XAMPP_SETUP.md`](XAMPP_SETUP.md) para la guia paso a paso
(instalar XAMPP, crear la BBDD, arrancar backend y frontend).

Resumen rapido:

```bash
# 1) Clona el repo dentro de C:\xampp\htdocs
cd C:\xampp\htdocs
git clone https://github.com/Liset17/Interfaz-php.git

# 2) Importa backend/schema.sql desde phpMyAdmin
#    (creando antes la BBDD academia_teatro)

# 3) Arranca Apache + MySQL desde el XAMPP Control Panel

# 4) En otra terminal:
cd C:\xampp\htdocs\Interfaz-php\frontend
npm install
npm run dev
```

Luego abre <http://localhost:5173>, registra un profesor y entra al panel.

## Endpoints

| Metodo | URL | Descripcion |
| --- | --- | --- |
| POST   | `/api/auth/register.php` | Registro de profesor |
| POST   | `/api/auth/login.php`    | Login (crea sesion PHP) |
| POST   | `/api/auth/logout.php`   | Cierra sesion |
| GET    | `/api/auth/me.php`       | Profesor logueado actual |
| *      | `/api/alumnos.php`       | CRUD alumnos |
| *      | `/api/grupos.php`        | CRUD grupos |
| *      | `/api/clases.php`        | CRUD clases |
| *      | `/api/asistencia.php`    | CRUD + upsert asistencia |

`*` = GET / POST / PUT / DELETE. Todos requieren sesion iniciada salvo
`register` y `login`.

## Autor

Kevin Siabato. Proyecto FP - Valencia.
