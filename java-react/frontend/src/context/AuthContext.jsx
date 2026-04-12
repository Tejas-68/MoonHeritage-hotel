import { createContext, useContext, useState, useCallback } from 'react'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => {
    try { return JSON.parse(localStorage.getItem('mh_user')) } catch { return null }
  })

  const login = useCallback((authData) => {
    localStorage.setItem('mh_token', authData.token)
    localStorage.setItem('mh_user', JSON.stringify({
      email: authData.email,
      firstName: authData.firstName,
      lastName: authData.lastName,
      role: authData.role,
      userId: authData.userId
    }))
    setUser({
      email: authData.email,
      firstName: authData.firstName,
      lastName: authData.lastName,
      role: authData.role,
      userId: authData.userId
    })
  }, [])

  const logout = useCallback(() => {
    localStorage.removeItem('mh_token')
    localStorage.removeItem('mh_user')
    setUser(null)
  }, [])

  const isAuthenticated = !!user
  const isAdmin = user?.role === 'admin'

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
