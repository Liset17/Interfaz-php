// =============================================================
// CLIENTE HTTP PARA LA API PHP
// -------------------------------------------------------------
// Centraliza TODAS las llamadas al backend. Ventajas:
//   - una sola URL base (configurable)
//   - `credentials: include` para que viaje la cookie de sesion
//   - manejo uniforme de errores (lanza Error con el mensaje)
//   - helpers get/post/put/del para no repetir boilerplate
//
// En desarrollo usamos el proxy de Vite, asi que apuntamos a
// rutas relativas tipo "/api/...". En produccion (mismo Apache
// sirviendo frontend + backend) sigue funcionando sin cambios.
// =============================================================

// Base URL: si algun dia el frontend no esta en la misma origin
// que el backend, basta con poner VITE_API_BASE en .env.
const API_BASE = import.meta.env.VITE_API_BASE || ''

/**
 * Llama a un endpoint y devuelve el JSON. Si la respuesta NO es
 * `ok`, lanza un Error con el mensaje del servidor.
 */
async function request(path, { method = 'GET', body, params } = {}) {
  // Query string opcional
  let url = API_BASE + path
  if (params && typeof params === 'object') {
    const qs = new URLSearchParams()
    for (const [k, v] of Object.entries(params)) {
      if (v !== undefined && v !== null && v !== '') qs.append(k, v)
    }
    const s = qs.toString()
    if (s) url += (url.includes('?') ? '&' : '?') + s
  }

  const init = {
    method,
    credentials: 'include',         // manda cookie de sesion PHP
    headers: { Accept: 'application/json' },
  }
  if (body !== undefined) {
    init.headers['Content-Type'] = 'application/json'
    init.body = JSON.stringify(body)
  }

  let res
  try {
    res = await fetch(url, init)
  } catch (e) {
    throw new Error('No se pudo conectar con el servidor. ¿XAMPP/Apache está arrancado?')
  }

  // Intentamos parsear JSON siempre, incluso en errores: el
  // backend devuelve { ok:false, error:"..." } con codigos 4xx/5xx.
  let data = null
  try { data = await res.json() } catch { /* ignore */ }

  if (!res.ok || (data && data.ok === false)) {
    const msg = (data && data.error) || `HTTP ${res.status}`
    throw new Error(msg)
  }
  return data
}

export const api = {
  get:  (p, params)       => request(p, { method: 'GET',    params }),
  post: (p, body)         => request(p, { method: 'POST',   body }),
  put:  (p, body, params) => request(p, { method: 'PUT',    body, params }),
  del:  (p, params)       => request(p, { method: 'DELETE', params }),
}
