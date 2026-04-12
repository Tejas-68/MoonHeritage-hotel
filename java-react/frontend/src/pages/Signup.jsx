import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import api from '../api/axios'
import { useAuth } from '../context/AuthContext'

export default function Signup() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState({ username: '', email: '', password: '', firstName: '', lastName: '' })
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      const res = await api.post('/auth/signup', form)
      login(res.data)
      navigate('/')
    } catch (err) {
      setError(err.response?.data?.message || 'Registration failed. Try again.')
    } finally {
      setLoading(false)
    }
  }

  const set = key => e => setForm(f => ({ ...f, [key]: e.target.value }))

  return (
    <div className="min-h-screen pt-16 flex items-center justify-center px-4 py-8">
      <div className="w-full max-w-md">
        <div className="text-center mb-8">
          <div className="text-3xl font-bold mb-2">
            <span className="text-gold-400">Moon</span><span className="text-white">Heritage</span>
          </div>
          <h1 className="text-xl font-semibold text-white">Create your account</h1>
          <p className="text-slate-400 text-sm mt-1">Start booking luxury stays today</p>
        </div>

        <div className="card p-8">
          {error && (
            <div className="bg-red-400/10 border border-red-400/30 text-red-400 text-sm rounded-xl px-4 py-3 mb-6">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="label">First Name</label>
                <input className="input" type="text" required value={form.firstName} onChange={set('firstName')} />
              </div>
              <div>
                <label className="label">Last Name</label>
                <input className="input" type="text" required value={form.lastName} onChange={set('lastName')} />
              </div>
            </div>
            <div>
              <label className="label">Username</label>
              <input className="input" type="text" required minLength={3} value={form.username} onChange={set('username')} />
            </div>
            <div>
              <label className="label">Email address</label>
              <input className="input" type="email" required value={form.email} onChange={set('email')} />
            </div>
            <div>
              <label className="label">Password</label>
              <input className="input" type="password" required minLength={6} value={form.password} onChange={set('password')} />
            </div>
            <button type="submit" disabled={loading} className="btn-primary w-full text-center">
              {loading ? 'Creating account...' : 'Create Account'}
            </button>
          </form>

          <p className="text-slate-400 text-sm text-center mt-6">
            Already have an account?{' '}
            <Link to="/login" className="text-gold-400 hover:text-gold-300 font-medium">Sign in</Link>
          </p>
        </div>
      </div>
    </div>
  )
}
