<template>
  <div class="view-container">
    <div class="view-header">
      <h1>&#9989; Control de Asistencia</h1>
      <button class="btn-cerrar" @click="cerrarVentana">&#10006; Cerrar</button>
    </div>

    <button class="btn-volver" @click="irAlPanel">
      &larr; Volver al panel principal
    </button>

    <div class="filtros">
      <div class="campo">
        <label>&#128197; Fecha:</label>
        <input type="date" v-model="fecha" @change="cargar" />
      </div>

      <div class="campo">
        <label>&#128101; Grupo:</label>
        <select v-model.number="grupoId" @change="cargar">
          <option :value="0">Todos los grupos</option>
          <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
        </select>
      </div>

      <div class="resumen">
        <strong>Presentes:</strong>
        {{ presentes }} / {{ filtrados.length }}
        ({{ porcentaje }}%)
      </div>
    </div>

    <div v-if="cargando" class="estado">Cargando...</div>
    <div v-if="error" class="estado error">{{ error }}</div>

    <div class="tabla-container">
      <table class="tabla-asistencia">
        <thead>
          <tr>
            <th>Alumno</th>
            <th>Grupo</th>
            <th>Presente</th>
            <th>Observaci&oacute;n</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in filtrados" :key="row.alumno_id">
            <td>{{ row.alumno }}</td>
            <td>{{ row.grupo || '-' }}</td>
            <td class="checkbox-cell">
              <input type="checkbox" v-model="row.presente" />
            </td>
            <td>
              <input
                type="text"
                v-model="row.observacion"
                placeholder="Ej: Lleg&oacute; tarde"
                class="observacion-input"
              />
            </td>
          </tr>
          <tr v-if="!cargando && filtrados.length === 0">
            <td colspan="4" class="sin-datos">
              No hay alumnos para este filtro.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="acciones">
      <button class="btn-marcar-todos"   @click="marcarTodos(true)">&#9989; Marcar todos presentes</button>
      <button class="btn-marcar-ninguno" @click="marcarTodos(false)">&#10060; Marcar todos ausentes</button>
      <button class="btn-guardar"        @click="guardar" :disabled="guardando">
        {{ guardando ? 'Guardando...' : '\uD83D\uDCBE Guardar asistencia' }}
      </button>
    </div>

    <div v-if="mensaje" class="mensaje-flotante">{{ mensaje }}</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { listarAlumnos } from '../services/alumnos'
import { listarGrupos }  from '../services/grupos'
import { listarAsistencia, guardarAsistencia } from '../services/asistencia'

const router   = useRouter()
const fecha    = ref(new Date().toISOString().split('T')[0])
const grupoId  = ref(0)                 // 0 = todos
const alumnos  = ref([])
const grupos   = ref([])
const registros= ref([])                // registros de asistencia existentes
const cargando = ref(false)
const guardando= ref(false)
const error    = ref('')
const mensaje  = ref('')

const mostrar = (txt) => {
  mensaje.value = txt
  setTimeout(() => (mensaje.value = ''), 2500)
}

/**
 * Combina la lista de alumnos con los registros ya guardados para
 * la fecha actual. Asi el checkbox arranca marcado o no segun la BBDD.
 */
const filtrados = computed(() => {
  const lista = grupoId.value
    ? alumnos.value.filter((a) => Number(a.grupo_id) === Number(grupoId.value))
    : alumnos.value

  return lista.map((a) => {
    const reg = registros.value.find((r) => Number(r.alumno_id) === Number(a.id))
    return {
      alumno_id:   a.id,
      alumno:      a.nombre,
      grupo:       a.grupo,
      grupo_id:    a.grupo_id,
      presente:    reg ? !!Number(reg.presente) : false,
      observacion: reg ? (reg.observacion || '') : '',
    }
  })
})

const presentes  = computed(() => filtrados.value.filter((r) => r.presente).length)
const porcentaje = computed(() => {
  const t = filtrados.value.length
  return t ? Math.round((presentes.value / t) * 100) : 0
})

const cargar = async () => {
  cargando.value = true
  error.value = ''
  try {
    // Cargamos alumnos y grupos siempre (por si cambian)
    const [a, g, asis] = await Promise.all([
      listarAlumnos(),
      listarGrupos(),
      listarAsistencia({ fecha: fecha.value }),
    ])
    alumnos.value = a
    grupos.value  = g
    registros.value = asis
  } catch (e) {
    error.value = 'Error cargando asistencia: ' + e.message
  } finally {
    cargando.value = false
  }
}

const marcarTodos = (estado) => {
  filtrados.value.forEach((r) => (r.presente = estado))
}

const guardar = async () => {
  guardando.value = true
  try {
    // Guardamos en paralelo (upsert por alumno+fecha en el backend)
    await Promise.all(
      filtrados.value.map((r) =>
        guardarAsistencia({
          alumno_id:   r.alumno_id,
          grupo_id:    r.grupo_id,
          fecha:       fecha.value,
          presente:    r.presente ? 1 : 0,
          observacion: r.observacion,
        })
      )
    )
    await cargar()
    mostrar('\u2705 Asistencia guardada')
  } catch (e) {
    mostrar('\u274c ' + e.message)
  } finally {
    guardando.value = false
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

.filtros { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap; }
.campo { display: flex; align-items: center; gap: 10px; }
.campo label { font-weight: bold; }
.campo input, .campo select { padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px; }
.resumen { background: #ca2924d2; color: #fff; padding: 8px 15px; border-radius: 20px; font-size: 14px; }

.estado { padding: 10px; margin: 10px 0; border-radius: 4px; background: #f0f0f0; }
.estado.error { background: #fde8ea; color: #a71f2b; }

.tabla-container { overflow-x: auto; margin: 20px 0; }
.tabla-asistencia { width: 100%; border-collapse: collapse; }
.tabla-asistencia th, .tabla-asistencia td { border: 1px solid #ddd; padding: 12px; text-align: left; }
.tabla-asistencia th { background: #ca2924d2; color: #fff; }
.tabla-asistencia tr:nth-child(even) { background: #f2f2f2; }

.checkbox-cell { text-align: center; }
.checkbox-cell input { width: 20px; height: 20px; cursor: pointer; }
.observacion-input { width: 100%; padding: 5px; border: 1px solid #ddd; border-radius: 4px; }

.sin-datos { text-align: center; color: #888; font-style: italic; }

.acciones { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
.btn-marcar-todos { background: #28a745; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
.btn-marcar-ninguno { background: #ffc107; color: #333; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
.btn-guardar { background: #007bff; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
.btn-guardar:disabled { opacity: 0.6; cursor: not-allowed; }

button:hover { opacity: 0.9; }

.mensaje-flotante { position: fixed; bottom: 20px; right: 20px; background: #ca2924d2; color: #fff; padding: 10px 20px; border-radius: 4px; z-index: 1000; }
</style>
