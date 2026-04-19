<template>
  <div class="view-container">
    <div class="view-header">
      <h1>&#128218; Gesti&oacute;n de Clases</h1>
      <button class="btn-cerrar" @click="cerrarVentana">&#10006; Cerrar</button>
    </div>

    <button class="btn-volver" @click="irAlPanel">
      &larr; Volver al panel principal
    </button>

    <div class="formulario">
      <h3>&#10133; Agregar nueva clase</h3>
      <div class="form-group">
        <input type="text"   v-model="nuevaClase.nombre"  placeholder="Nombre de la clase" />
        <input type="text"   v-model="nuevaClase.horario" placeholder="Horario (ej: Lunes 18:00)" />
        <input type="number" v-model.number="nuevaClase.cupo" placeholder="Cupo m&aacute;ximo" />
        <button class="btn-agregar" @click="agregarClase">Agregar clase</button>
      </div>
    </div>

    <div v-if="cargando" class="estado">Cargando...</div>
    <div v-if="error" class="estado error">{{ error }}</div>

    <div class="tabla-container">
      <table class="tabla-clases">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Horario</th>
            <th>Profesor</th>
            <th>Cupo</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="clase in clases" :key="clase.id">
            <td>{{ clase.id }}</td>
            <td>
              <span v-if="!clase.editando">{{ clase.nombre }}</span>
              <input v-else v-model="clase.nombre" class="edit-input" />
            </td>
            <td>
              <span v-if="!clase.editando">{{ clase.horario }}</span>
              <input v-else v-model="clase.horario" class="edit-input" />
            </td>
            <td>{{ clase.profesor }}</td>
            <td>
              <span v-if="!clase.editando">{{ clase.cupo }}</span>
              <input v-else v-model.number="clase.cupo" type="number" class="edit-input" />
            </td>
            <td>
              <button v-if="!clase.editando" class="btn-editar" @click="clase.editando = true">
                &#9999;&#65039; Editar
              </button>
              <button v-else class="btn-guardar" @click="guardarEdicion(clase)">
                &#128190; Guardar
              </button>
              <button class="btn-eliminar" @click="borrarClase(clase.id)">
                &#128465; Eliminar
              </button>
            </td>
          </tr>
          <tr v-if="!cargando && clases.length === 0">
            <td colspan="6" class="sin-datos">No hay clases registradas todav&iacute;a.</td>
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
  listarClases, crearClase, actualizarClase, eliminarClase,
} from '../services/clases'

const router = useRouter()
const clases = ref([])
const cargando = ref(false)
const error = ref('')
const mensaje = ref('')

const nuevaClase = ref({ nombre: '', horario: '', cupo: null })

const mostrar = (txt) => {
  mensaje.value = txt
  setTimeout(() => (mensaje.value = ''), 2500)
}

const cargar = async () => {
  cargando.value = true
  error.value = ''
  try {
    clases.value = await listarClases()
  } catch (e) {
    error.value = 'Error cargando clases: ' + e.message
  } finally {
    cargando.value = false
  }
}

const agregarClase = async () => {
  if (!nuevaClase.value.nombre || !nuevaClase.value.horario) {
    mostrar('\u26a0\ufe0f Nombre y horario son obligatorios')
    return
  }
  try {
    await crearClase({
      nombre:  nuevaClase.value.nombre,
      horario: nuevaClase.value.horario,
      cupo:    nuevaClase.value.cupo || 20,
    })
    nuevaClase.value = { nombre: '', horario: '', cupo: null }
    await cargar()
    mostrar('\u2705 Clase agregada')
  } catch (e) {
    mostrar('\u274c ' + e.message)
  }
}

const guardarEdicion = async (clase) => {
  try {
    await actualizarClase(clase.id, {
      nombre:  clase.nombre,
      horario: clase.horario,
      cupo:    clase.cupo,
    })
    clase.editando = false
    mostrar('\u2705 Clase actualizada')
  } catch (e) {
    mostrar('\u274c ' + e.message)
  }
}

const borrarClase = async (id) => {
  if (!confirm('\u00bfEliminar esta clase?')) return
  try {
    await eliminarClase(id)
    clases.value = clases.value.filter((c) => c.id !== id)
    mostrar('\ud83d\uddd1\ufe0f Clase eliminada')
  } catch (e) {
    mostrar('\u274c ' + e.message)
  }
}

const cerrarVentana = () => window.close()
const irAlPanel = () => router.push('/')

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
.form-group input { flex: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }

.btn-agregar { background: #ca2924d2; color: #fff; border: none; padding: 8px 20px; border-radius: 4px; cursor: pointer; }

.tabla-container { overflow-x: auto; margin: 20px 0; }
.tabla-clases { width: 100%; border-collapse: collapse; }
.tabla-clases th, .tabla-clases td { border: 1px solid #ddd; padding: 12px; text-align: left; }
.tabla-clases th { background: #ca2924d2; color: #fff; }
.tabla-clases tr:nth-child(even) { background: #f2f2f2; }

.edit-input { width: 100%; padding: 5px; border: 1px solid #ddd; border-radius: 4px; }

.btn-editar   { background: #ffc107; color: #333; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px; }
.btn-guardar  { background: #007bff; color: #fff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px; }
.btn-eliminar { background: #dc3545; color: #fff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }

.sin-datos { text-align: center; color: #888; font-style: italic; }
.estado { padding: 10px; margin: 10px 0; border-radius: 4px; background: #f0f0f0; }
.estado.error { background: #fde8ea; color: #a71f2b; }

.mensaje-flotante { position: fixed; bottom: 20px; right: 20px; background: #ca2924d2; color: #fff; padding: 10px 20px; border-radius: 4px; z-index: 1000; }

button:hover { opacity: 0.85; }
</style>
