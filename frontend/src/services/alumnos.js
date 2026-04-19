// =============================================================
// SERVICIO ALUMNOS
// -------------------------------------------------------------
// Cada funcion devuelve directamente el array/objeto util para
// las vistas (no el envoltorio { ok, data }).
// =============================================================

import { api } from '../lib/api'

export async function listarAlumnos() {
  const r = await api.get('/api/alumnos.php')
  return r.data
}

export async function obtenerAlumno(id) {
  const r = await api.get('/api/alumnos.php', { id })
  return r.data
}

export async function crearAlumno(payload) {
  const r = await api.post('/api/alumnos.php', payload)
  return r.data
}

export async function actualizarAlumno(id, payload) {
  return api.put('/api/alumnos.php', payload, { id })
}

export async function eliminarAlumno(id) {
  return api.del('/api/alumnos.php', { id })
}
