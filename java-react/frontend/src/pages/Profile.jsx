import { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import api from '../api/axios'
import { useAuth } from '../context/AuthContext'

const STATUS_BADGE = {
  confirmed: 'badge-success',
  completed: 'badge-success',
  pending:   'badge-gold',
  cancelled: 'badge-danger',
}

export default function Profile() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [activeTab, setActiveTab] = useState('overview')
  const [profile, setProfile] = useState(null)
  const [bookings, setBookings] = useState([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [msg, setMsg] = useState('')

  useEffect(() => {
    Promise.all([api.get('/users/profile'), api.get('/bookings/my')])
      .then(([p, b]) => { setProfile(p.data); setBookings(b.data) })
      .finally(() => setLoading(false))
  }, [])

  const handleSave = async (e) => {
    e.preventDefault()
    setSaving(true); setMsg('')
    try {
      const res = await api.put('/users/profile', profile)
      setProfile(res.data); setMsg('Profile updated successfully!')
    } catch { setMsg('Failed to update profile.') }
    finally { setSaving(false); setTimeout(() => setMsg(''), 3000) }
  }

  const handleCancel = async (id) => {
    if (!confirm('Cancel this booking?')) return
    try {
      await api.put(`/bookings/${id}/cancel`, { reason: 'Cancelled by user' })
      setBookings(bs => bs.map(b => b.id === id ? { ...b, bookingStatus: 'cancelled' } : b))
    } catch { alert('Failed to cancel.') }
  }

  if (loading) return (
    <div className="pt-16 min-h-screen flex items-center justify-center">
      <div className="w-10 h-10 border-2 border-gold-400 border-t-transparent rounded-full animate-spin" />
    </div>
  )

  const TABS = ['overview', 'bookings', 'settings']

  return (
    <div className="pt-20 min-h-screen">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Profile header */}
        <div className="card p-6 flex items-center gap-5 mb-6">
          <div className="w-16 h-16 rounded-full bg-gold-400/20 border border-gold-400/30 flex items-center justify-center text-gold-400 font-bold text-xl">
            {profile?.firstName?.[0]}{profile?.lastName?.[0]}
          </div>
          <div className="flex-1">
            <h1 className="text-xl font-bold text-white">{profile?.firstName} {profile?.lastName}</h1>
            <p className="text-slate-400 text-sm">{profile?.email}</p>
            <span className="badge-gold text-xs mt-1 inline-block">{profile?.role?.toUpperCase()}</span>
          </div>
          <button onClick={() => { logout(); navigate('/') }} className="btn-ghost text-sm text-red-400">
            Sign Out
          </button>
        </div>

        {/* Tabs */}
        <div className="flex gap-1 bg-luxury-800 rounded-xl p-1 mb-6">
          {TABS.map(tab => (
            <button key={tab} onClick={() => setActiveTab(tab)}
              className={`flex-1 py-2 text-sm font-medium rounded-lg transition-all capitalize ${
                activeTab === tab ? 'bg-gold-400 text-luxury-900' : 'text-slate-400 hover:text-white'
              }`}>
              {tab}
            </button>
          ))}
        </div>

        {/* Overview */}
        {activeTab === 'overview' && (
          <div className="animate-fade-in">
            <div className="grid grid-cols-3 gap-4 mb-8">
              {[
                ['Total Bookings', bookings.length],
                ['Confirmed', bookings.filter(b => b.bookingStatus === 'confirmed' || b.bookingStatus === 'completed').length],
                ['Total Spent', `$${bookings.reduce((s, b) => s + parseFloat(b.totalAmount || 0), 0).toFixed(0)}`],
              ].map(([label, value]) => (
                <div key={label} className="card p-5 text-center">
                  <div className="text-2xl font-bold text-gold-400 mb-1">{value}</div>
                  <div className="text-slate-400 text-sm">{label}</div>
                </div>
              ))}
            </div>
            <h3 className="text-white font-semibold mb-4">Recent Bookings</h3>
            {bookings.slice(0, 3).map(b => <BookingRow key={b.id} booking={b} onCancel={handleCancel} />)}
          </div>
        )}

        {/* Bookings */}
        {activeTab === 'bookings' && (
          <div className="animate-fade-in space-y-3">
            {bookings.length === 0
              ? <div className="card p-12 text-center text-slate-400">
                  <p className="text-lg mb-2">No bookings yet</p>
                  <p className="text-sm">Explore our hotels to make your first reservation.</p>
                </div>
              : bookings.map(b => <BookingRow key={b.id} booking={b} onCancel={handleCancel} />)
            }
          </div>
        )}

        {/* Settings */}
        {activeTab === 'settings' && (
          <div className="animate-fade-in">
            <form onSubmit={handleSave} className="card p-6 space-y-5">
              {msg && (
                <div className={`text-sm rounded-xl px-4 py-3 ${msg.includes('success') ? 'bg-green-400/10 border border-green-400/30 text-green-400' : 'bg-red-400/10 border border-red-400/30 text-red-400'}`}>
                  {msg}
                </div>
              )}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="label">First Name</label>
                  <input className="input" value={profile?.firstName || ''} onChange={e => setProfile(p => ({ ...p, firstName: e.target.value }))} />
                </div>
                <div>
                  <label className="label">Last Name</label>
                  <input className="input" value={profile?.lastName || ''} onChange={e => setProfile(p => ({ ...p, lastName: e.target.value }))} />
                </div>
              </div>
              <div>
                <label className="label">Phone</label>
                <input className="input" value={profile?.phone || ''} onChange={e => setProfile(p => ({ ...p, phone: e.target.value }))} />
              </div>
              <div>
                <label className="label">Address</label>
                <input className="input" value={profile?.address || ''} onChange={e => setProfile(p => ({ ...p, address: e.target.value }))} />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="label">City</label>
                  <input className="input" value={profile?.city || ''} onChange={e => setProfile(p => ({ ...p, city: e.target.value }))} />
                </div>
                <div>
                  <label className="label">Country</label>
                  <input className="input" value={profile?.country || ''} onChange={e => setProfile(p => ({ ...p, country: e.target.value }))} />
                </div>
              </div>
              <button type="submit" className="btn-primary" disabled={saving}>
                {saving ? 'Saving...' : 'Save Changes'}
              </button>
            </form>
          </div>
        )}
      </div>
    </div>
  )
}

function BookingRow({ booking, onCancel }) {
  return (
    <div className="card p-5 flex items-center gap-4">
      <div className="flex-1 min-w-0">
        <h4 className="text-white font-medium mb-1 truncate">{booking.hotel?.name}</h4>
        <p className="text-slate-400 text-sm">{booking.checkInDate} → {booking.checkOutDate} · {booking.totalNights} nights</p>
        <p className="text-slate-500 text-xs mt-1">#{booking.bookingNumber}</p>
      </div>
      <div className="flex flex-col items-end gap-2 shrink-0">
        <span className={STATUS_BADGE[booking.bookingStatus] || 'badge-muted'}>{booking.bookingStatus}</span>
        <span className="text-gold-400 font-bold">${booking.totalAmount}</span>
        {booking.bookingStatus !== 'cancelled' && booking.bookingStatus !== 'completed' && (
          <button onClick={() => onCancel(booking.id)} className="text-xs text-red-400 hover:text-red-300 transition-colors">
            Cancel
          </button>
        )}
      </div>
    </div>
  )
}
