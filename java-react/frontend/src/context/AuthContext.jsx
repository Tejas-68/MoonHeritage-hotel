import { createContext, useContext, useState, useEffect, useCallback } from 'react'
import api from '../api/axios'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  // Validate session on app init using the HttpOnly cookie
  useEffect(() => {
    api.get('/users/profile')
      .then(res => setUser(res.data))
      .catch(() => setUser(null)) // 401 simply means not logged in
      .finally(() => setLoading(false))
  }, [])

  const login = useCallback((authData) => {
    // The mh_token cookie is already set by the backend response
    setUser({
      email: authData.email,
      firstName: authData.firstName,
      lastName: authData.lastName,
      role: authData.role,
      userId: authData.userId
    })
  }, [])

  const logout = useCallback(async () => {
    try {
      await api.post('/auth/logout')
    } catch (err) {
      console.error("Logout failed", err)
    } finally {
      setUser(null)
      window.location.href = '/login'
    }
  }, [])

  const isAuthenticated = !!user
  const isAdmin = user?.role === 'admin'

  if (loading) return null // Prevent brief flash of unauthorized state

  return (
    <AuthContext.Provider value={{ user, login, logout, isAuthenticated, isAdmin }}>
      {children}
    </AuthContext.Provider>
  )
}

export const useAuth = () => {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
