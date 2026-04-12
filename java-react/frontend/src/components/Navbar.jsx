import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Navbar() {
  const { isAuthenticated, isAdmin, user, logout } = useAuth()
  const navigate = useNavigate()
  const [menuOpen, setMenuOpen] = useState(false)

  const handleLogout = () => {
    logout()
    navigate('/')
    setMenuOpen(false)
  }

  return (
    <nav className="fixed top-0 left-0 right-0 z-50 bg-luxury-900/90 backdrop-blur-md border-b border-white/10">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link to="/" className="flex items-center gap-2 font-bold text-xl">
            <span className="text-gold-400">Moon</span>
            <span className="text-white">Heritage</span>
          </Link>

          {/* Desktop Nav */}
          <div className="hidden md:flex items-center gap-6">
            <Link to="/hotels" className="text-slate-300 hover:text-white transition-colors text-sm font-medium">Hotels</Link>
            {isAdmin && (
              <Link to="/admin" className="text-gold-400 hover:text-gold-300 transition-colors text-sm font-medium">Admin</Link>
            )}
          </div>

          {/* Desktop Auth */}
          <div className="hidden md:flex items-center gap-3">
            {isAuthenticated ? (
              <>
                <Link to="/profile" className="text-slate-300 hover:text-white text-sm font-medium transition-colors">
                  {user?.firstName}
                </Link>
                <button onClick={handleLogout} className="btn-outline text-sm px-4 py-2">Sign Out</button>
              </>
            ) : (
              <>
                <Link to="/login" className="text-slate-300 hover:text-white text-sm font-medium transition-colors">Sign In</Link>
                <Link to="/signup" className="btn-primary text-sm px-4 py-2">Get Started</Link>
              </>
            )}
          </div>

          {/* Mobile toggle */}
          <button className="md:hidden btn-ghost p-2" onClick={() => setMenuOpen(o => !o)}>
            <div className="w-5 h-0.5 bg-white mb-1" />
            <div className="w-5 h-0.5 bg-white mb-1" />
            <div className="w-5 h-0.5 bg-white" />
          </button>
        </div>
      </div>

      {/* Mobile menu */}
      {menuOpen && (
        <div className="md:hidden border-t border-white/10 bg-luxury-900 px-4 py-4 flex flex-col gap-3">
          <Link to="/hotels" className="text-slate-300 text-sm font-medium" onClick={() => setMenuOpen(false)}>Hotels</Link>
          {isAdmin && <Link to="/admin" className="text-gold-400 text-sm font-medium" onClick={() => setMenuOpen(false)}>Admin</Link>}
          {isAuthenticated ? (
            <>
              <Link to="/profile" className="text-slate-300 text-sm font-medium" onClick={() => setMenuOpen(false)}>Profile</Link>
              <button onClick={handleLogout} className="text-left text-sm text-red-400 font-medium">Sign Out</button>
            </>
          ) : (
            <>
              <Link to="/login" className="text-slate-300 text-sm font-medium" onClick={() => setMenuOpen(false)}>Sign In</Link>
              <Link to="/signup" className="btn-primary text-sm text-center" onClick={() => setMenuOpen(false)}>Get Started</Link>
            </>
          )}
        </div>
      )}
    </nav>
  )
}
