// =============================================================
// SERVICIO GRUPOS
// =============================================================
import { api } from '../lib/api'

export async function listarGrupos() {
  const r = await api.get('/api/grupos.php')
  return r.data
}

export async function obtenerGrupo(id) {
  const r = await api.get('/api/grupos.php', { id })
  return r.data
}

export async function crearGrupo(payload) {
  const r = await api.post('/api/grupos.php', payload)
  return r.data
}

export async function actualizarGrupo(id, payload) {
  return api.put('/api/grupos.php', payload, { id })
}

export async function eliminarGrupo(id) {
  return api.del('/api/grupos.php', { id })
}
