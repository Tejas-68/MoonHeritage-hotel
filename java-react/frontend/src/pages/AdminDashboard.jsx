import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import api from '../api/axios'
import { hotelImg } from '../utils/imageUtil'

const EMPTY_HOTEL = {
  name: '', address: '', city: '', state: '', country: '',
  description: '', shortDescription: '', phone: '', email: '',
  category: 'hotel', starRating: '4', pricePerNight: '', originalPrice: '',
  discountPercentage: '0', featured: false, totalRooms: '0',
  availableRooms: '0', status: 'active',
}

export default function AdminDashboard() {
  const [tab, setTab] = useState('hotels')
  const [stats, setStats] = useState(null)
  const [hotels, setHotels] = useState([])
  const [totalPages, setTotalPages] = useState(0)
  const [page, setPage] = useState(0)
  const [loading, setLoading] = useState(true)
  const [formOpen, setFormOpen] = useState(false)
  const [editing, setEditing] = useState(null)
  const [form, setForm] = useState(EMPTY_HOTEL)
  const [imgFile, setImgFile] = useState(null)
  const [saving, setSaving] = useState(false)
  const [msg, setMsg] = useState('')

  useEffect(() => {
    api.get('/admin/stats').then(r => setStats(r.data)).catch(console.error)
  }, [])

  useEffect(() => {
    setLoading(true)
    api.get(`/admin/hotels?page=${page}&size=10`)
      .then(r => { setHotels(r.data.hotels); setTotalPages(r.data.totalPages) })
      .finally(() => setLoading(false))
  }, [page])

  const openAdd = () => { setEditing(null); setForm(EMPTY_HOTEL); setImgFile(null); setFormOpen(true) }
  const openEdit = (hotel) => {
    setEditing(hotel)
    setForm({
      name: hotel.name || '', address: hotel.address || '', city: hotel.city || '',
      state: hotel.state || '', country: hotel.country || '',
      description: hotel.description || '', shortDescription: hotel.shortDescription || '',
      phone: hotel.phone || '', email: hotel.email || '',
      category: hotel.category || 'hotel',
      starRating: hotel.starRating?.toString() || '4',
      pricePerNight: hotel.pricePerNight?.toString() || '',
      originalPrice: hotel.originalPrice?.toString() || '',
      discountPercentage: hotel.discountPercentage?.toString() || '0',
      featured: hotel.featured || false,
      totalRooms: hotel.totalRooms?.toString() || '0',
      availableRooms: hotel.availableRooms?.toString() || '0',
      status: hotel.status || 'active',
    })
    setImgFile(null)
    setFormOpen(true)
  }

  const handleDelete = async (id) => {
    if (!confirm('Delete this hotel? This cannot be undone.')) return
    try {
      await api.delete(`/admin/hotels/${id}`)
      setHotels(hs => hs.filter(h => h.id !== id))
      setMsg('Hotel deleted.')
      setTimeout(() => setMsg(''), 3000)
    } catch { setMsg('Delete failed.') }
  }

  const handleSave = async (e) => {
    e.preventDefault()
    setSaving(true); setMsg('')
    try {
      const payload = {
        ...form,
        starRating: parseFloat(form.starRating),
        pricePerNight: parseFloat(form.pricePerNight),
        originalPrice: form.originalPrice ? parseFloat(form.originalPrice) : null,
        discountPercentage: parseInt(form.discountPercentage),
        totalRooms: parseInt(form.totalRooms),
        availableRooms: parseInt(form.availableRooms),
      }
      let saved
      if (editing) {
        const res = await api.put(`/admin/hotels/${editing.id}`, payload)
        saved = res.data
        setHotels(hs => hs.map(h => h.id === editing.id ? saved : h))
      } else {
        const res = await api.post('/admin/hotels', payload)
        saved = res.data
        setHotels(hs => [saved, ...hs])
      }

      // Upload image if selected
      if (imgFile && saved?.id) {
        const fd = new FormData()
        fd.append('file', imgFile)
        await api.post(`/admin/hotels/${saved.id}/image`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
        // Refresh to get updated mainImage
        const updated = await api.get(`/hotels/${saved.id}`)
        setHotels(hs => hs.map(h => h.id === saved.id ? updated.data : h))
      }

      setMsg(editing ? 'Hotel updated!' : 'Hotel created!')
      setFormOpen(false)
      setTimeout(() => setMsg(''), 3000)
    } catch (err) {
      setMsg(err.response?.data?.message || 'Save failed.')
    } finally {
      setSaving(false)
    }
  }

  const set = key => e => setForm(f => ({
    ...f, [key]: e.target.type === 'checkbox' ? e.target.checked : e.target.value
  }))

  const TABS = ['hotels', 'stats']

  return (
    <div className="pt-20 min-h-screen">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <div className="badge-gold mb-2">Administration</div>
            <h1 className="text-2xl font-bold text-white">Admin Dashboard</h1>
          </div>
          <Link to="/" className="btn-ghost text-sm">← Back to site</Link>
        </div>

        {/* Message */}
        {msg && (
          <div className={`text-sm rounded-xl px-4 py-3 mb-6 ${msg.includes('failed') || msg.includes('Failed') ? 'bg-red-400/10 border border-red-400/30 text-red-400' : 'bg-green-400/10 border border-green-400/30 text-green-400'}`}>
            {msg}
          </div>
        )}

        {/* Tabs */}
        <div className="flex gap-1 bg-luxury-800 rounded-xl p-1 mb-6 w-fit">
          {TABS.map(t => (
            <button key={t} onClick={() => setTab(t)}
              className={`py-2 px-6 text-sm font-medium rounded-lg transition-all capitalize ${
                tab === t ? 'bg-gold-400 text-luxury-900' : 'text-slate-400 hover:text-white'
              }`}>
              {t}
            </button>
          ))}
        </div>

        {/* Stats tab */}
        {tab === 'stats' && stats && (
          <div className="animate-fade-in">
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
              {[
                ['Total Hotels', stats.totalHotels, 'text-blue-400'],
                ['Total Bookings', stats.totalBookings, 'text-green-400'],
                ['Total Users', stats.totalUsers, 'text-purple-400'],
                ['Revenue', `$${parseFloat(stats.totalRevenue).toFixed(0)}`, 'text-gold-400'],
              ].map(([label, value, clr]) => (
                <div key={label} className="card p-5 text-center">
                  <div className={`text-3xl font-bold mb-1 ${clr}`}>{value}</div>
                  <div className="text-slate-400 text-sm">{label}</div>
                </div>
              ))}
            </div>

            {stats.recentBookings?.length > 0 && (
              <>
                <h3 className="text-white font-semibold mb-4">Recent Bookings</h3>
                <div className="card overflow-hidden">
                  <table className="w-full text-sm">
                    <thead className="bg-luxury-700">
                      <tr>
                        {['Reference', 'Hotel', 'Dates', 'Amount', 'Status'].map(h => (
                          <th key={h} className="px-4 py-3 text-left text-slate-400 font-medium text-xs uppercase">{h}</th>
                        ))}
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-white/5">
                      {stats.recentBookings.map(b => (
                        <tr key={b.id} className="hover:bg-white/2">
                          <td className="px-4 py-3 text-slate-300 font-mono text-xs">{b.bookingNumber}</td>
                          <td className="px-4 py-3 text-white">{b.hotel?.name}</td>
                          <td className="px-4 py-3 text-slate-400">{b.checkInDate} → {b.checkOutDate}</td>
                          <td className="px-4 py-3 text-gold-400 font-semibold">${b.totalAmount}</td>
                          <td className="px-4 py-3 capitalize">
                            <span className={b.bookingStatus === 'confirmed' ? 'badge-success' : b.bookingStatus === 'cancelled' ? 'badge-danger' : 'badge-gold'}>
                              {b.bookingStatus}
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </>
            )}
          </div>
        )}

        {/* Hotels tab */}
        {tab === 'hotels' && (
          <div className="animate-fade-in">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-white font-semibold">All Hotels ({hotels.length})</h2>
              <button onClick={openAdd} className="btn-primary text-sm px-4 py-2">+ Add Hotel</button>
            </div>

            {loading ? (
              <div className="flex justify-center py-20">
                <div className="w-10 h-10 border-2 border-gold-400 border-t-transparent rounded-full animate-spin" />
              </div>
            ) : (
              <div className="card overflow-hidden">
                <table className="w-full text-sm">
                  <thead className="bg-luxury-700">
                    <tr>
                      {['Image', 'Hotel', 'City', 'Price', 'Status', 'Actions'].map(h => (
                        <th key={h} className="px-4 py-3 text-left text-slate-400 font-medium text-xs uppercase">{h}</th>
                      ))}
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-white/5">
                    {hotels.map(hotel => (
                      <tr key={hotel.id} className="hover:bg-white/2">
                        <td className="px-4 py-3">
                          <img src={hotelImg(hotel.mainImage)} alt={hotel.name}
                            onError={e => { e.target.src = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=100&q=80' }}
                            className="w-12 h-10 object-cover rounded-lg" />
                        </td>
                        <td className="px-4 py-3">
                          <div className="text-white font-medium">{hotel.name}</div>
                          <div className="text-slate-500 text-xs capitalize">{hotel.category}</div>
                        </td>
                        <td className="px-4 py-3 text-slate-400">{hotel.city}, {hotel.country}</td>
                        <td className="px-4 py-3 text-gold-400 font-semibold">${hotel.pricePerNight}</td>
                        <td className="px-4 py-3">
                          <span className={hotel.status === 'active' ? 'badge-success' : 'badge-muted'}>{hotel.status}</span>
                        </td>
                        <td className="px-4 py-3">
                          <div className="flex gap-2">
                            <button onClick={() => openEdit(hotel)}
                              className="text-xs text-gold-400 hover:text-gold-300 font-medium transition-colors">Edit</button>
                            <button onClick={() => handleDelete(hotel.id)}
                              className="text-xs text-red-400 hover:text-red-300 font-medium transition-colors">Delete</button>
                          </div>
                        </td>
                      </tr>
                    ))}
                    {hotels.length === 0 && (
                      <tr><td colSpan={6} className="px-4 py-12 text-center text-slate-400">No hotels yet. Click "Add Hotel" to create one.</td></tr>
                    )}
                  </tbody>
                </table>
              </div>
            )}

            {/* Pagination */}
            {totalPages > 1 && (
              <div className="flex items-center justify-center gap-4 mt-6">
                <button className="btn-ghost border border-white/10 rounded-xl px-4 py-2 text-sm disabled:opacity-30"
                  disabled={page === 0} onClick={() => setPage(p => p - 1)}>Previous</button>
                <span className="text-slate-400 text-sm">Page {page + 1} of {totalPages}</span>
                <button className="btn-ghost border border-white/10 rounded-xl px-4 py-2 text-sm disabled:opacity-30"
                  disabled={page >= totalPages - 1} onClick={() => setPage(p => p + 1)}>Next</button>
              </div>
            )}
          </div>
        )}
      </div>

      {/* Modal */}
      {formOpen && (
        <div className="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-start justify-center py-8 px-4 overflow-y-auto">
          <div className="w-full max-w-2xl card p-8 animate-slide-up">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-xl font-bold text-white">{editing ? 'Edit Hotel' : 'Add New Hotel'}</h2>
              <button onClick={() => setFormOpen(false)} className="text-slate-400 hover:text-white text-xl">&times;</button>
            </div>

            <form onSubmit={handleSave} className="space-y-4">
              {/* Image upload */}
              <div>
                <label className="label">Hotel Image</label>
                {editing?.mainImage && !imgFile && (
                  <img src={hotelImg(editing.mainImage)} className="w-full h-32 object-cover rounded-xl mb-2"
                    onError={e => { e.target.style.display = 'none' }} />
                )}
                {imgFile && (
                  <img src={URL.createObjectURL(imgFile)} className="w-full h-32 object-cover rounded-xl mb-2" />
                )}
                <input type="file" accept="image/*" onChange={e => setImgFile(e.target.files[0])}
                  className="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-gold-400/20 file:text-gold-400 hover:file:bg-gold-400/30 cursor-pointer" />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="label">Hotel Name *</label>
                  <input className="input" required value={form.name} onChange={set('name')} />
                </div>
                <div>
                  <label className="label">Category</label>
                  <select className="input" value={form.category} onChange={set('category')}>
                    {['hotel','villa','resort','apartment','cottage'].map(c => (
                      <option key={c} value={c}>{c.charAt(0).toUpperCase()+c.slice(1)}</option>
                    ))}
                  </select>
                </div>
              </div>

              <div>
                <label className="label">Short Description</label>
                <input className="input" placeholder="One-line summary" value={form.shortDescription} onChange={set('shortDescription')} />
              </div>
              <div>
                <label className="label">Full Description</label>
                <textarea className="input resize-none" rows={3} value={form.description} onChange={set('description')} />
              </div>

              <div className="grid grid-cols-3 gap-4">
                <div>
                  <label className="label">City *</label>
                  <input className="input" required value={form.city} onChange={set('city')} />
                </div>
                <div>
                  <label className="label">Country *</label>
                  <input className="input" required value={form.country} onChange={set('country')} />
                </div>
                <div>
                  <label className="label">State</label>
                  <input className="input" value={form.state} onChange={set('state')} />
                </div>
              </div>

              <div>
                <label className="label">Address *</label>
                <input className="input" required value={form.address} onChange={set('address')} />
              </div>

              <div className="grid grid-cols-3 gap-4">
                <div>
                  <label className="label">Price/Night ($) *</label>
                  <input className="input" type="number" required min="1" step="0.01" value={form.pricePerNight} onChange={set('pricePerNight')} />
                </div>
                <div>
                  <label className="label">Original Price ($)</label>
                  <input className="input" type="number" min="1" step="0.01" value={form.originalPrice} onChange={set('originalPrice')} />
                </div>
                <div>
                  <label className="label">Discount (%)</label>
                  <input className="input" type="number" min="0" max="100" value={form.discountPercentage} onChange={set('discountPercentage')} />
                </div>
              </div>

              <div className="grid grid-cols-3 gap-4">
                <div>
                  <label className="label">Star Rating</label>
                  <select className="input" value={form.starRating} onChange={set('starRating')}>
                    {[1,2,3,4,5].map(s => <option key={s} value={s}>{s} Star</option>)}
                  </select>
                </div>
                <div>
                  <label className="label">Total Rooms</label>
                  <input className="input" type="number" min="0" value={form.totalRooms} onChange={set('totalRooms')} />
                </div>
                <div>
                  <label className="label">Available</label>
                  <input className="input" type="number" min="0" value={form.availableRooms} onChange={set('availableRooms')} />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="label">Phone</label>
                  <input className="input" type="tel" value={form.phone} onChange={set('phone')} />
                </div>
                <div>
                  <label className="label">Email</label>
                  <input className="input" type="email" value={form.email} onChange={set('email')} />
                </div>
              </div>

              <div className="flex gap-4">
                <div className="flex-1">
                  <label className="label">Status</label>
                  <select className="input" value={form.status} onChange={set('status')}>
                    {['active','inactive','maintenance'].map(s => <option key={s} value={s}>{s}</option>)}
                  </select>
                </div>
                <div className="flex items-center gap-3 pt-6">
                  <input type="checkbox" id="featured" checked={form.featured} onChange={set('featured')}
                    className="w-4 h-4 accent-gold-400" />
                  <label htmlFor="featured" className="text-slate-300 text-sm cursor-pointer">Featured Hotel</label>
                </div>
              </div>

              <div className="flex gap-3 pt-2">
                <button type="submit" disabled={saving} className="btn-primary flex-1 text-center">
                  {saving ? 'Saving...' : editing ? 'Update Hotel' : 'Create Hotel'}
                </button>
                <button type="button" onClick={() => setFormOpen(false)} className="btn-ghost border border-white/10 rounded-xl px-6">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}
