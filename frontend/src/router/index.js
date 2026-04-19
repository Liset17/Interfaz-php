// =============================================================
// ROUTER CON GUARDS DE AUTENTICACION
// -------------------------------------------------------------
// Rutas publicas: /login, /register
// Rutas protegidas: todas las demas (requieren sesion PHP)
//
// Antes de cada navegacion preguntamos al backend (solo si no
// sabemos ya quien esta logueado) y redirigimos segun el caso.
// =============================================================

import { createRouter, createWebHistory } from 'vue-router'
import { currentProfile, fetchCurrentUser } from '../services/auth'

import Home       from '../views/home.vue'
import Login      from '../views/login.vue'
import Register   from '../views/register.vue'
import Alumnos    from '../views/alumnos.vue'
import Asistencia from '../views/asistencia.vue'
import Clases     from '../views/clases.vue'
import Grupos     from '../views/grupos.vue'

const routes = [
  { path: '/login',      name: 'Login',      component: Login,      meta: { public: true } },
  { path: '/register',   name: 'Register',   component: Register,   meta: { public: true } },
  { path: '/',           name: 'Home',       component: Home },
  { path: '/alumnos',    name: 'Alumnos',    component: Alumnos },
  { path: '/asistencia', name: 'Asistencia', component: Asistencia },
  { path: '/clases',     name: 'Clases',     component: Clases },
  { path: '/grupos',     name: 'Grupos',     component: Grupos },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Guard global: comprueba sesion solo si aun no se cargo.
router.beforeEach(async (to) => {
  if (currentProfile.value === null) {
    await fetchCurrentUser()
  }
  const logged = !!currentProfile.value

  if (to.meta.public) {
    // Si ya esta logueado, no tiene sentido ver login/register
    if (logged) return { path: '/' }
    return true
  }

  // Ruta protegida sin sesion -> al login
  if (!logged) return { path: '/login' }
  return true
})

export default router
