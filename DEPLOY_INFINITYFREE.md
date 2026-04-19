# Despliegue en InfinityFree

Esta guia te lleva desde cero hasta tener la aplicacion corriendo en
tu cuenta de InfinityFree.

> **Importante**: InfinityFree **bloquea los archivos que empiezan
> por punto** (`.gitignore`, `.htaccess`, etc.) en el File Manager
> web. Por eso el metodo recomendado es **FTP con FileZilla**, que
> no tiene ese problema. Dejo abajo las dos opciones.

## 0. Que vas a necesitar

- Tu cuenta de InfinityFree (panel en <https://dash.infinityfree.com>).
- Las credenciales MySQL de tu BBDD (panel -> **MySQL Databases**).
- Las credenciales FTP (panel -> **FTP Accounts**).
- FileZilla instalado (<https://filezilla-project.org/>).
- Unos 5-10 minutos.

La BBDD ya la tienes creada en InfinityFree, asi que **NO necesitas
importar el `schema.sql` de nuevo**.

---

## 1. Preparar `config/db.local.php`

El archivo `config/db.php` ya tiene valores por defecto de XAMPP
(root / sin password / 127.0.0.1). Para apuntar a InfinityFree
necesitas sobrescribirlos con un archivo **local**:

1. En tu PC, dentro de la carpeta del proyecto, ve a `config/`.
2. Copia `db.local.example.php` y renombra la copia a `db.local.php`.
3. Abrelo y pega los 4 datos que te da InfinityFree (panel ->
   **MySQL Databases** -> tu BBDD -> "MySQL details"):

```php
<?php
$DB_HOST = 'sqlXXX.infinityfree.com';         // el host que te muestra el panel
$DB_NAME = 'if0_12345678_academia_teatro';    // nombre completo
$DB_USER = 'if0_12345678';                    // usuario (empieza por if0_)
$DB_PASS = 'tu_password_real';
```

Ese archivo **no se versiona** (esta en `.gitignore`) pero si lo
vas a subir al hosting, es justo lo que queremos.

---

## 2. Metodo A (recomendado): FTP con FileZilla

### 2.1 Sacar las credenciales FTP

En el panel de InfinityFree:

1. Entra en el dominio de tu proyecto.
2. Busca la seccion **FTP Accounts** (o "FTP Details").
3. Anota:
   - **FTP Host / Server**: normalmente `ftpupload.net`
   - **FTP Username**: suele empezar por `if0_`
   - **FTP Password**: la que elegiste (o la que te genero el panel)
   - **Port**: `21`

### 2.2 Conectar con FileZilla

1. Abre FileZilla.
2. En la barra superior (Quickconnect) pon:
   - Servidor: `ftpupload.net`
   - Usuario: `if0_XXXXXXXX`
   - Contrasena: la tuya
   - Puerto: `21`
3. Pulsa **Quickconnect**.

Si todo va bien, en el panel derecho (Sitio remoto) veras una
carpeta `htdocs/`. **Todo lo que dejes dentro de `htdocs/` se
sirve publicamente**.

> Si FileZilla se queja por TLS explicito, puedes ir a
> **Archivo -> Gestor de sitios** y crear un sitio con
> "Cifrado: Solo FTP simple". InfinityFree no soporta FTPS en el
> plan gratuito.

### 2.3 Subir los archivos

1. En el panel **izquierdo** (Sitio local) navega hasta la carpeta
   del proyecto en tu PC: `C:\xampp\htdocs\php-interfaz`.
2. En el panel **derecho** entra dentro de `htdocs/`.
3. Si hay archivos de muestra (`index2.html`, `default.html`, etc.)
   borralos: click derecho -> Eliminar. Asi tu `index.php` sera
   el que cargue primero.
4. En el panel izquierdo selecciona **todos los archivos y carpetas
   del proyecto** (Ctrl+A) **excepto**:
   - `.git/`
   - `.vscode/`
   - `config/db.local.example.php` (no hace falta en produccion)
   - `*.md` (README, DEPLOY_INFINITYFREE, XAMPP_SETUP) - opcional
   - `php-interfaz.zip` (solo si lo tienes, no hace falta)
5. Arrastralos al panel derecho (dentro de `htdocs/`). Empieza la
   subida. FileZilla muestra el progreso abajo.

FTP **si sube dotfiles**, asi que `.htaccess` (y `.gitignore` si lo
subes) llegara sin problema. Solo hace falta `.htaccess`;
`.gitignore` no aporta nada al hosting.

### 2.4 Verificar la estructura final

Dentro de `htdocs/` deben estar:

```
.htaccess
index.php
login.php
register.php
logout.php
home.php
alumnos.php
grupos.php
clases.php
asistencia.php
config/
  db.php
  db.local.php         <-- con tus credenciales reales
includes/
  auth.php
  bootstrap.php
  footer.php
  header.php
assets/
  style.css
  img/
    mask.webp
    mask.jpg
    mask.png
```

> Comprueba que `config/db.local.php` llego (no el `.example`).
> Si olvidaste copiarlo, subelo suelto ahora.

---

## 3. Metodo B: File Manager web (solo si no quieres FileZilla)

Funciona, pero ojo con dos detalles:

- **No sube archivos que empiecen por punto** (`.htaccess`,
  `.gitignore`). El `.htaccess` es util pero no imprescindible
  para que la app funcione — con que `index.php` este dentro de
  `htdocs/` ya carga por defecto.
- Tiene un limite de tamanyo por peticion, asi que lo mejor es
  subir un zip.

### 3.1 Preparar el zip

Usa `php-interfaz.zip` (si te lo genere) o haz uno tu mismo con el
**contenido** de la carpeta del proyecto (no la carpeta entera, los
archivos sueltos). Excluye las mismas cosas que en el metodo FTP.

### 3.2 Subir y extraer

1. Panel -> **Online File Manager** (dentro de "Files").
2. Entra en `htdocs/`.
3. Borra los archivos de muestra (`index2.html`, `default.html`).
4. Boton **Upload** (icono nube con flecha) -> sube el zip.
5. Click derecho sobre el zip subido -> **Extract**.
6. Borra el zip cuando termine.
7. Si al extraer se crea una subcarpeta extra (`php-interfaz/`),
   muevete dentro y mueve todo a `htdocs/` (o deja que la URL
   incluya `/php-interfaz/`, tambien funciona).

### 3.3 Subir `.htaccess` a mano (opcional)

Si quieres el `.htaccess` (fuerza `DirectoryIndex index.php` y
bloquea listado de directorios):

1. Renombralo localmente a `htaccess.txt`.
2. Subelo por File Manager.
3. Una vez subido, usa la opcion **Rename** del File Manager para
   dejarlo como `.htaccess`.

O simplemente usa FTP para este archivo, que es mas rapido.

---

## 4. Probar

Abre en el navegador:

- `https://tudominio.epizy.com/` (o el dominio gratis que te dieron)

Cosas que pueden pasar:

| Sintoma | Que revisar |
| --- | --- |
| "No se pudo conectar a la base de datos" | Revisa `config/db.local.php`: host, nombre BBDD, usuario, password. Todos empiezan por `if0_` excepto el host. |
| Te sale el placeholder de InfinityFree | No borraste los archivos de muestra en `htdocs/`. Borra `index2.html` / `default.html`. |
| Pantalla en blanco | Activa errores temporalmente: en `config/db.local.php` anade `ini_set('display_errors', 1); error_reporting(E_ALL);` como primera linea despues de `<?php`. Acuerdate de quitarlo cuando funcione. |
| Pagina sin estilos (parece texto plano) | La ruta de `assets/style.css` no resuelve. Verifica que subiste la carpeta `assets/` con `style.css` dentro. |
| Warning "session_start..." | InfinityFree ya inicio la sesion automaticamente. No es bloqueante. |
| 403 Forbidden al entrar | Falta `index.php` o no esta como DirectoryIndex. Sube `.htaccess` o entra a `https://tudominio.epizy.com/index.php`. |

---

## 5. Primer login

Como la BBDD ya la tenias antes, **puedes entrar con el mismo
email/password que usabas**. Si no recuerdas uno valido, entra al
phpMyAdmin de InfinityFree, borra la fila de tu profesor en la
tabla `profesores` y registrate de nuevo desde `register.php`.

---

## 6. Actualizar despues

Cuando cambies algo del codigo:

- **FTP (FileZilla)**: arrastra los archivos cambiados al panel
  derecho. FileZilla pregunta si sobrescribir -> "Sobrescribir".
  Lo mas rapido para cambios puntuales.
- **File Manager**: sube el archivo concreto (o un zip con los
  que cambiaron y extrae).

---

## 7. Seguridad basica

- **Nunca** subas `config/db.local.php` a GitHub. Ya esta en el
  `.gitignore`.
- InfinityFree te da HTTPS gratis; una vez activo, la cookie de
  sesion pasa automaticamente a `secure` (lo detecta el bootstrap).
- Si en algun momento quieres revocar acceso, cambia el password
  de MySQL en el panel y actualiza `db.local.php`.
- No subas `.git/` al hosting. Con FTP es facil no arrastrarla;
  con el File Manager tampoco habria manera, pero en el zip si
  asegurate de excluirla.
