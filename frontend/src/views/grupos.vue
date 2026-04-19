<template>
  <div class="view-container">
    <div class="view-header">
      <h1>&#128101; Gesti&oacute;n de Grupos</h1>
      <button class="btn-cerrar" @click="cerrarVentana">&#10006; Cerrar</button>
    </div>

    <button class="btn-volver" @click="irAlPanel">
      &larr; Volver al panel principal
    </button>

    <div class="formulario">
      <h3>&#10133; Crear nuevo grupo</h3>
      <div class="form-group">
        <input type="text"   v-model="nuevo.nombre"       placeholder="Nombre del grupo" />
        <input type="text"   v-model="nuevo.horario"      placeholder="Horario (ej: Martes 19:00)" />
        <input type="number" v-model.number="nuevo.cupo"  placeholder="Cupo m&aacute;ximo" />
        <button class="btn-agregar" @click="agregar">Crear grupo</button>
      </div>
    </div>

    <div class="filtros">
      <div class="campo">
        <label>&#128269; Buscar grupo:</label>
        <input type="text" v-model="busqueda" placeholder="Nombre del grupo..." />
      </div>
      <div class="resumen">
        <strong>Total grupos:</strong> {{ filtrados.length }} |
        <strong>Total alumnos:</strong> {{ totalAlumnos }}
      </div>
    </div>

    <div v-if="cargando" class="estado">Cargando...</div>
    <div v-if="error" class="estado error">{{ error }}</div>

    <div class="grupos-grid">
      <div v-for="g in filtrados" :key="g.id" class="grupo-card">
        <div class="card-header">
          <h2 v-if="!g.editando">{{ g.nombre }}</h2>
          <input v-else v-model="g.nombre" class="edit-input edit-title" />
          <div class="acciones-card">
            <button v-if="!g.editando" class="btn-editar"   @click="g.editando = true">&#9999;&#65039;</button>
            <button v-else            class="btn-guardar"  @click="guardar(g)">&#128190;</button>
            <button                   class="btn-eliminar" @click="borrar(g.id)">&#128465;</button>
          </div>
        </div>

        <div class="card-body">
          <div class="info-grupo">
            <p>
              <strong>&#128100; Profesor:</strong>
              {{ g.profesor || 'Sin asignar' }}
            </p>
            <p>
              <strong>&#9200; Horario:</strong>
              <span v-if="!g.editando">{{ g.horario || 'No definido' }}</span>
              <input v-else v-model="g.horario" class="edit-input" />
            </p>
            <p>
              <strong>&#128202; Cupo m&aacute;ximo:</strong>
              <span v-if="!g.editando">{{ g.cupo }}</span>
              <input v-else v-model.number="g.cupo" type="number" class="edit-input" />
            </p>
            <p>
              <strong>&#128101; Alumnos actuales:</strong>
              {{ g.total_alumnos || 0 }} / {{ g.cupo }}
            </p>
          </div>

          <div class="progress-bar">
            <div
              class="progress-fill"
              :style="{ width: porcentaje(g) + '%' }"
              :class="claseProgreso(g)"
            >
              {{ porcentaje(g) }}%
            </div>
          </div>
        </div>
      </div>

      <div v-if="!cargando && filtrados.length === 0" class="sin-datos">
        No hay grupos registrados todav&iacute;a.
      </div>
    </div>

    <div v-if="mensaje" class="mensaje-flotante">{{ mensaje }}</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  listarGrupos, crearGrupo, actualizarGrupo, eliminarGrupo,
} from '../services/grupos'

const router   = useRouter()
const grupos   = ref([])
const busqueda = ref('')
const cargando = ref(false)
const error    = ref('')
const mensaje  = ref('')

const nuevo = ref({ nombre: '', horario: '', cupo: null })

const mostrar = (txt) => {
  mensaje.value = txt
  setTimeout(() => (mensaje.value = ''), 2500)
}

const cargar = async () => {
  cargando.value = true
  error.value = ''
  try {
    grupos.value = await listarGrupos()
  } catch (e) {
    error.value = 'Error cargando grupos: ' + e.message
  } finally {
    cargando.value = false
  }
}

const filtrados = computed(() => {
  if (!busqueda.value) return grupos.value
  const q = busqueda.value.toLowerCase()
  return grupos.value.filter((g) => g.nombre.toLowerCase().includes(q))
})

const totalAlumnos = computed(() =>
  grupos.value.reduce((s, g) => s + Number(g.total_alumnos || 0), 0)
)

const porcentaje = (g) => {
  const cupo = Number(g.cupo) || 1
  const act  = Number(g.total_alumnos) || 0
  return Math.min(100, Math.round((act / cupo) * 100))
}

const claseProgreso = (g) => {
  const p = porcentaje(g)
  if (p >= 90) return 'progreso-lleno'
  if (p >= 70) return 'progreso-alto'
  return 'progreso-normal'
}

const agregar = async () => {
  if (!nuevo.value.nombre) {
    mostrar('\u26a0\ufe0f El nombre es obligatorio')
    return
  }
  try {
    await crearGrupo({
      nombre:  nuevo.value.nombre,
      horario: nuevo.value.horario,
      cupo:    nuevo.value.cupo || 20,
    })
    nuevo.value = { nombre: '', horario: '', cupo: null }
    await cargar()
    mostrar('\u2705 Grupo creado')
  } catch (e) {
    mostrar('\u274c ' + e.message)
  }
}

const guardar = async (g) => {
  try {
    await actualizarGrupo(g.id, {
      nombre:  g.nombre,
      horario: g.horario,
      cupo:    g.cupo,
    })
    g.editando = false
    mostrar('\u2705 Grupo actualizado')
  } catch (e) {
    mostrar('\u274c ' + e.message)
  }
}

const borrar = async (id) => {
  if (!confirm('\u00bfEliminar este grupo?')) return
  try {
    await eliminarGrupo(id)
    grupos.value = grupos.value.filter((g) => g.id !== id)
    mostrar('\ud83d\uddd1\ufe0f Grupo eliminado')
  } catch (e) {
    mostrar('\u274c ' + e.message)
  }
}

const cerrarVentana = () => window.close()
const irAlPanel     = () => router.push('/')

onMounted(cargar)
</script>

<style scoped>
.view-container { padding: 20px; max-width: 1400px; margin: 0 auto; font-family: Arial, sans-serif; }
.view-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #42b983; margin-bottom: 20px; padding-bottom: 10px; }
.btn-cerrar { background: #dc3545; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
.btn-volver { background: #6c757d; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-bottom: 20px; }

.formulario { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
.formulario h3 { margin: 0 0 15px; }
.form-group { display: flex; gap: 10px; flex-wrap: wrap; }
.form-group input { flex: 1; min-width: 150px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; }
.btn-agregar { background: #ca2924d2; color: #fff; border: none; padding: 8px 20px; border-radius: 4px; cursor: pointer; }

.filtros { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.campo { display: flex; align-items: center; gap: 10px; }
.campo input { padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; }
.resumen { background: #ca2924d2; color: #fff; padding: 8px 15px; border-radius: 20px; font-size: 14px; }

.estado { padding: 10px; margin: 10px 0; border-radius: 4px; background: #f0f0f0; }
.estado.error { background: #fde8ea; color: #a71f2b; }

.grupos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; margin-top: 20px; }
.grupo-card { background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.08); transition: transform 0.2s; }
.grupo-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.12); }
.card-header { background: #ca2924d2; color: #fff; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
.card-header h2 { margin: 0; font-size: 1.3rem; }
.acciones-card { display: flex; gap: 8px; }
.btn-editar, .btn-guardar, .btn-eliminar { background: rgba(255,255,255,0.2); border: none; color: #fff; cursor: pointer; padding: 5px 10px; border-radius: 4px; font-size: 14px; }
.btn-editar:hover, .btn-guardar:hover, .btn-eliminar:hover { background: rgba(255,255,255,0.35); }
.card-body { padding: 15px; }
.info-grupo p { margin: 8px 0; }
.edit-input { padding: 4px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; }
.edit-title { background: rgba(255,255,255,0.9); color: #333; font-weight: bold; font-size: 1.1rem; }

.progress-bar { background: #e0e0e0; border-radius: 10px; overflow: hidden; margin: 15px 0; height: 28px; }
.progress-fill { height: 100%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; font-weight: bold; transition: width 0.3s; }
.progreso-normal { background: #28a745; }
.progreso-alto { background: #ffc107; color: #333; }
.progreso-lleno { background: #dc3545; }

.sin-datos { grid-column: 1 / -1; text-align: center; color: #888; font-style: italic; padding: 40px; }

.mensaje-flotante { position: fixed; bottom: 20px; right: 20px; background: #ca2924d2; color: #fff; padding: 10px 20px; border-radius: 4px; z-index: 1000; }

button:hover { opacity: 0.9; }

@media (max-width: 768px) { .grupos-grid { grid-template-columns: 1fr; } .form-group { flex-direction: column; } .filtros { flex-direction: column; align-items: stretch; } }
</style>
