// =============================================================
// SERVICIO ASISTENCIA
// -------------------------------------------------------------
// Filtros opcionales en el listado: fecha (YYYY-MM-DD),
// alumno_id y grupo_id.
// =============================================================
import { api } from '../lib/api'

export async function listarAsistencia(filtros = {}) {
  const r = await api.get('/api/asistencia.php', filtros)
  return r.data
}

export async function obtenerAsistencia(id) {
  const r = await api.get('/api/asistencia.php', { id })
  return r.data
}

/**
 * Crea o actualiza un registro (upsert por alumno+fecha).
 */
export async function guardarAsistencia(payload) {
  const r = await api.post('/api/asistencia.php', payload)
  return r.data
}

export async function actualizarAsistencia(id, payload) {
  return api.put('/api/asistencia.php', payload, { id })
}

export async function eliminarAsistencia(id) {
  return api.del('/api/asistencia.php', { id })
}
