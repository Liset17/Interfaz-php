// =============================================================
// SERVICIO CLASES
// =============================================================
import { api } from '../lib/api'

export async function listarClases() {
  const r = await api.get('/api/clases.php')
  return r.data
}

export async function obtenerClase(id) {
  const r = await api.get('/api/clases.php', { id })
  return r.data
}

export async function crearClase(payload) {
  const r = await api.post('/api/clases.php', payload)
  return r.data
}

export async function actualizarClase(id, payload) {
  return api.put('/api/clases.php', payload, { id })
}

export async function eliminarClase(id) {
  return api.del('/api/clases.php', { id })
}
