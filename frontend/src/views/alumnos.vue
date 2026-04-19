<template>
  <div class="view-container">
    <div class="view-header">
      <h1>&#128203; Gesti&oacute;n de Alumnos</h1>
      <button class="btn-cerrar" @click="cerrarVentana">&#10006; Cerrar</button>
    </div>

    <button class="btn-volver" @click="irAlPanel">
      &larr; Volver al panel principal
    </button>

    <div class="formulario">
      <h3>&#10133; Agregar nuevo alumno</h3>
      <div class="form-group">
        <input type="text"  v-model="nuevo.nombre"   placeholder="Nombre completo" />
        <input type="email" v-model="nuevo.email"    placeholder="Email" />
        <input type="text"  v-model="nuevo.telefono" placeholder="Tel&eacute;fono" />
        <select v-model.number="nuevo.grupo_id">
          <option :value="null">Sin grupo</option>
          <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
        </select>
        <button class="btn-agregar" @click="agregar">Agregar alumno</button>
      </div>
    </div>

    <div v-if="cargando" class="estado">Cargando...</div>
    <div v-if="error" class="estado error">{{ error }}</div>

    <div class="tabla-container">
      <table class="tabla-alumnos">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Tel&eacute;fono</th>
            <th>Grupo</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in alumnos" :key="a.id">
            <td>{{ a.id }}</td>
            <td>
              <span v-if="!a.editando">{{ a.nombre }}</span>
              <input v-else v-model="a.nombre" class="edit-input" />
            </td>
            <td>
              <span v-if="!a.editando">{{ a.email }}</span>
              <input v-else v-model="a.email" class="edit-input" />
            </td>
            <td>
              <span v-if="!a.editando">{{ a.telefono }}</span>
              <input v-else v-model="a.telefono" class="edit-input" />
            </td>
            <td>
              <span v-if="!a.editando">{{ a.grupo }}</span>
              <select v-else v-model.number="a.grupo_id" class="edit-input">
                <option :value="null">Sin grupo</option>
                <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
              </select>
            </td>
            <td>
              <button v-if="!a.editando" class="btn-editar" @click="a.editando = true">
                &#9999;&#65039; Editar
              </button>
              <button v-else class="btn-guardar" @click="guardar(a)">
                &#128190; Guardar
              </button>
              <button class="btn-eliminar" @click="borrar(a.id)">
                &#128465; Eliminar
              </button>
            </td>
          </tr>
          <tr v-if="!cargando && alumnos.length === 0">
            <td colspan="6" class="sin-datos">No hay alumnos registrados todav&iacute;a.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="mensaje" class="mensaje-flotante">{{ mensaje }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  listarAlumnos, crearAlumno, actualizarAlumno, eliminarAlumno,
} from '../services/alumnos'
import { listarGrupos } from '../services/grupos'

const router   = useRouter()
const alumnos  = ref([])
const grupos   = ref([])
const cargando = ref(false)
const error    = ref('')
const mensaje  = ref('')

const nuevo = ref({ nombre: '', email: '', telefono: '', grupo_id: null })

const mostrar = (txt) => {
  mensaje.value = txt
  setTimeout(() => (mensaje.value = ''), 2500)
}

const cargar = async () => {
  cargando.value = true
  error.value = ''
  try {
    // En paralelo: listar alumnos y grupos
    const [a, g] = await Promise.all([listarAlumnos(), listarGrupos()])
    alumnos.value = a
    grupos.value  = g
  } catch (e) {
    error.value = 'Error cargando datos: ' + e.message
  } finally {
    cargando.value = false
  }
}

const agregar = async () => {
  if (!nuevo.value.nombre) {
    mostrar('\u26a0\ufe0f El nombre es obligatorio')
    return
  }
  try {
    await crearAlumno({
      nombre:   nuevo.value.nombre,
      email:    nuevo.value.email,
      telefono: nuevo.value.telefono,
      grupo_id: nuevo.value.grupo_id,
    })
    nuevo.value = { nombre: '', email: '', telefono: '', grupo_id: null }
    await cargar()
    mostrar('\u2705 Alumno agregado')
  } catch (e) {
    mostrar('\u274c ' + e.message)
  }
}

const guardar = async (a) => {
  try {
    await actualizarAlumno(a.id, {
      nombre:   a.nombre,
      email:    a.email,
      telefono: a.telefono,
      grupo_id: a.grupo_id,
    })
    a.editando = false
    await cargar()
    mostrar('\u2705 Alumno actualizado')
  } catch (e) {
    mostrar('\u274c ' + e.message)
  }
}

const borrar = async (id) => {
  if (!confirm('\u00bfEliminar este alumno?')) return
  try {
    await eliminarAlumno(id)
    alumnos.value = alumnos.value.filter((a) => a.id !== id)
    mostrar('\ud83d\uddd1\ufe0f Alumno eliminado')
  } catch (e) {
    mostrar('\u274c ' + e.message)
  }
}

const cerrarVentana = () => window.close()
const irAlPanel     = () => router.push('/')

onMounted(cargar)
</script>

<style scoped>
.view-container { padding: 20px; max-width: 1200px; margin: 0 auto; font-family: Arial, sans-serif; }
.view-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #42b983; margin-bottom: 20px; padding-bottom: 10px; }
.btn-cerrar { background: #dc3545; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
.btn-volver { background: #6c757d; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-bottom: 20px; }

.formulario { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
.formulario h3 { margin: 0 0 15px; }
.form-group { display: flex; gap: 10px; flex-wrap: wrap; }
.form-group input, .form-group select { flex: 1; min-width: 150px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }

.btn-agregar { background: #ca2924d2; color: #fff; border: none; padding: 8px 20px; border-radius: 4px; cursor: pointer; }

.tabla-container { overflow-x: auto; margin: 20px 0; }
.tabla-alumnos { width: 100%; border-collapse: collapse; }
.tabla-alumnos th, .tabla-alumnos td { border: 1px solid #ddd; padding: 12px; text-align: left; }
.tabla-alumnos th { background: #ca2924d2; color: #fff; }
.tabla-alumnos tr:nth-child(even) { background: #f2f2f2; }

.edit-input { width: 100%; padding: 5px; border: 1px solid #ddd; border-radius: 4px; }

.btn-editar   { background: #ffc107; color: #333; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px; }
.btn-guardar  { background: #007bff; color: #fff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px; }
.btn-eliminar { background: #dc3545; color: #fff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }

.sin-datos { text-align: center; color: #888; font-style: italic; }
.estado { padding: 10px; margin: 10px 0; border-radius: 4px; background: #f0f0f0; }
.estado.error { background: #fde8ea; color: #a71f2b; }

.mensaje-flotante {
  position: fixed; bottom: 20px; right: 20px; background: #ca2924d2;
  color: #fff; padding: 10px 20px; border-radius: 4px; z-index: 1000;
}

button:hover { opacity: 0.85; }
</style>
