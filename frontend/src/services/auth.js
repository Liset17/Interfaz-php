// =============================================================
// SERVICIO DE AUTENTICACION
// -------------------------------------------------------------
// Envuelve /api/auth/* y expone un estado reactivo global
// (`currentProfile`) igual que en la version Supabase, asi las
// vistas no cambian casi nada al hacer el port.
// =============================================================

import { ref } from 'vue'
import { api } from '../lib/api'

// Estado global: el profesor logueado, o null
export const currentProfile = ref(null)

/**
 * Llama al backend para saber si hay sesion iniciada.
 * Se usa al arrancar la app y en los guards del router.
 */
export async function fetchCurrentUser() {
  try {
    const r = await api.get('/api/auth/me.php')
    currentProfile.value = r.user || null
    return currentProfile.value
  } catch {
    // Errores de red o 401 => no hay sesion
    currentProfile.value = null
    return null
  }
}

/**
 * Inicia sesion con email y contrasena.
 */
export async function login(email, password) {
  const r = await api.post('/api/auth/login.php', { email, password })
  currentProfile.value = r.user
  return r.user
}

/**
 * Registra un nuevo profesor. Tras registrarse hacemos login
 * automaticamente para que el usuario entre directo al panel.
 */
export async function register(nombre, email, password) {
  await api.post('/api/auth/register.php', { nombre, email, password })
  return login(email, password)
}

/**
 * Cierra la sesion actual y limpia el estado global.
 */
export async function logout() {
  try {
    await api.post('/api/auth/logout.php')
  } catch { /* si falla, igualmente limpiamos el estado */ }
  currentProfile.value = null
}
