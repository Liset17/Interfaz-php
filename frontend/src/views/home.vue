<template>
  <div>
    <nav>
      <div class="nav-content">
        <img class="mask" src="/mask.webp" alt="" />
        <div class="text-group">
          <h1>Academia de Teatro</h1>
          <p>Profesores</p>
        </div>
        <div class="nav-user">
          <span v-if="currentProfile" class="hola">
            Hola, {{ currentProfile.nombre }}
          </span>
          <button class="btn-logout" @click="onLogout">Cerrar sesi&oacute;n</button>
        </div>
      </div>
    </nav>

    <main>
      <h1>Selecciona una secci&oacute;n</h1>

      <div class="grid">
        <div class="card">
          <i class="fas fa-users"></i>
          <h2>Alumnos</h2>
          <p>Gesti&oacute;n de alumnos</p>
          <button class="btn" @click="$router.push('/alumnos')">Acceder</button>
        </div>

        <div class="card">
          <i class="fas fa-calendar-check"></i>
          <h2>Asistencia</h2>
          <p>Control de asistencia</p>
          <button class="btn" @click="$router.push('/asistencia')">Acceder</button>
        </div>

        <div class="card">
          <i class="fas fa-chalkboard"></i>
          <h2>Clases</h2>
          <p>Gesti&oacute;n de clases</p>
          <button class="btn" @click="$router.push('/clases')">Acceder</button>
        </div>

        <div class="card">
          <i class="fas fa-layer-group"></i>
          <h2>Grupos</h2>
          <p>Organizaci&oacute;n de grupos</p>
          <button class="btn" @click="$router.push('/grupos')">Acceder</button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { currentProfile, logout } from '../services/auth'

const router = useRouter()

const onLogout = async () => {
  await logout()
  router.push('/login')
}
</script>

<style scoped>
.nav-user {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #fff;
}
.hola { font-size: 0.95rem; opacity: 0.9; }
.btn-logout {
  background: rgba(255,255,255,0.15);
  color: #fff;
  border: 1px solid rgba(255,255,255,0.35);
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
}
.btn-logout:hover { background: rgba(255,255,255,0.25); }
</style>
