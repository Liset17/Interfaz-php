import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// =============================================================
// Vite config para la version PHP
// -------------------------------------------------------------
// En desarrollo el frontend corre en http://localhost:5173 y el
// backend PHP en http://localhost/Interfaz-php/backend/ (XAMPP).
//
// Si llamaramos a otra origin directamente desde fetch, la
// cookie de sesion PHP no viajaria (o se bloquearia por CORS).
// Solucion: montamos un proxy en Vite para que el frontend
// pueda llamar a "/api/..." como si fuese la misma origin, y
// Vite lo reenvia a Apache.
//
// En producción (todo servido por Apache desde XAMPP o similar)
// el proxy no se usa: los archivos se copian al mismo dominio.
// =============================================================
export default defineConfig({
  plugins: [vue()],

  server: {
    port: 5173,
    proxy: {
      // Reenvia cualquier peticion /api/... al backend XAMPP.
      // `changeOrigin` arregla el Host header; `cookieDomainRewrite`
      // reescribe la cookie para que el navegador la acepte en 5173.
      '/api': {
        target: 'http://localhost/Interfaz-php/backend',
        changeOrigin: true,
        cookieDomainRewrite: 'localhost',
        secure: false,
      },
    },
  },
})
