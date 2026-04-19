<template>
  <div class="auth-wrap">
    <div class="auth-card">
      <h1>Iniciar sesi&oacute;n</h1>
      <p class="sub">Academia de Teatro &mdash; panel de profesores</p>

      <form @submit.prevent="onSubmit">
        <label>Email</label>
        <input type="email" v-model="email" required autocomplete="email" />

        <label>Contrase&ntilde;a</label>
        <input type="password" v-model="password" required autocomplete="current-password" />

        <button type="submit" class="btn-primary" :disabled="cargando">
          {{ cargando ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p v-if="error" class="error">{{ error }}</p>

      <p class="foot">
        &iquest;No tienes cuenta?
        <router-link to="/register">Registrate aqu&iacute;</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '../services/auth'

const router   = useRouter()
const email    = ref('')
const password = ref('')
const cargando = ref(false)
const error    = ref('')

const onSubmit = async () => {
  error.value = ''
  cargando.value = true
  try {
    await login(email.value, password.value)
    router.push('/')
  } catch (e) {
    error.value = e.message || 'No se pudo iniciar sesi\u00f3n'
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
.auth-wrap {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f4f4f4;
  padding: 20px;
}
.auth-card {
  background: #fff;
  padding: 32px;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  width: 100%;
  max-width: 420px;
}
h1 { margin: 0 0 4px; color: #333; }
.sub { color: #777; margin-bottom: 20px; font-size: 0.9rem; }
label { display: block; margin: 12px 0 4px; color: #444; font-size: 0.9rem; }
input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
  box-sizing: border-box;
}
.btn-primary {
  width: 100%;
  margin-top: 20px;
  padding: 12px;
  background: #ca2924d2;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  cursor: pointer;
}
.btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
.error {
  margin-top: 14px;
  padding: 10px;
  background: #fde8ea;
  color: #a71f2b;
  border-radius: 6px;
  font-size: 0.9rem;
}
.foot { margin-top: 18px; text-align: center; color: #666; font-size: 0.9rem; }
.foot a { color: #ca2924d2; text-decoration: none; font-weight: bold; }
</style>
