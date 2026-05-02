import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    port: 3000,
    // Dev proxy: forwards /api requests to local Spring Boot backend
    proxy: {
      '/api': {
        target: 'http://localhost:8081',
        changeOrigin: true
      }
    }
  },
  build: {
    outDir: 'dist',
    // Ensure React Router works with client-side routing
    rollupOptions: {
      output: {
        manualChunks: undefined
      }
    }
  }
})
