<?php
// =============================================================
// HELPERS REUTILIZABLES
// -------------------------------------------------------------
// Funciones pequenas que se usan en muchos endpoints:
//   - json_response: mandar JSON y salir
//   - read_json_body: decodificar el body JSON de POST/PUT
//   - require_method: validar que el metodo HTTP sea el esperado
// =============================================================

/**
 * Devuelve una respuesta JSON con el codigo de estado indicado
 * y termina la ejecucion. Evita duplicar `echo json_encode(...)`
 * y `http_response_code(...)` en cada endpoint.
 */
function json_response($data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Lee y decodifica el cuerpo JSON de la peticion. Si el JSON es
 * invalido devuelve 400. Si no hay body, devuelve un array vacio.
 */
function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) {
        return [];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_response(['ok' => false, 'error' => 'JSON invalido'], 400);
    }
    return $data;
}

/**
 * Fuerza que la peticion venga con uno de los metodos permitidos.
 * En caso contrario devuelve 405.
 */
function require_method(array $allowed): string
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, $allowed, true)) {
        header('Allow: ' . implode(', ', $allowed));
        json_response(['ok' => false, 'error' => 'Metodo no permitido'], 405);
    }
    return $method;
}
