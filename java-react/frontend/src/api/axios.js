import axios from 'axios'

/**
 * In production (Vercel), VITE_API_URL must be set to your Render backend URL.
 * Example: VITE_API_URL=https://moonheritage-backend.onrender.com/api
 *
 * In development, the Vite dev proxy forwards /api → localhost:8081/api
 * so VITE_API_URL can be left empty.
 */
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  withCredentials: true,
  headers: { 'Content-Type': 'application/json' }
})

// Redirect to login on 401 (expired/missing token)
api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401 && window.location.pathname !== '/login') {
      window.location.href = '/login'
    }
    return Promise.reject(err)
  }
)

export default api
