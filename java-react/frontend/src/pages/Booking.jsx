import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import api from '../api/axios'
import { hotelImg } from '../utils/imageUtil'

export default function Booking() {
  const { hotelId } = useParams()
  const navigate = useNavigate()
  const [hotel, setHotel] = useState(null)
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState('')
  const [form, setForm] = useState({
    checkInDate: '', checkOutDate: '',
    guestsAdults: 1, guestsChildren: 0, roomsCount: 1,
    specialRequests: '', paymentMethod: 'card',
  })

  useEffect(() => {
    api.get(`/hotels/${hotelId}`)
      .then(r => setHotel(r.data))
      .catch(() => navigate('/hotels'))
      .finally(() => setLoading(false))
  }, [hotelId, navigate])

  const nights = form.checkInDate && form.checkOutDate
    ? Math.max(0, (new Date(form.checkOutDate) - new Date(form.checkInDate)) / 86400000) : 0
  const subtotal = hotel ? (hotel.pricePerNight * nights * form.roomsCount).toFixed(2) : 0
  const tax = (subtotal * 0.10).toFixed(2)
  const total = (parseFloat(subtotal) + parseFloat(tax)).toFixed(2)

  const set = key => e => setForm(f => ({ ...f, [key]: e.target.type === 'number' ? +e.target.value : e.target.value }))

  const handleSubmit = async (e) => {
    e.preventDefault()
    if (nights < 1) { setError('Select valid check-in and check-out dates.'); return }
    setError('')
    setSubmitting(true)
    try {
      const res = await api.post('/bookings', { hotelId: +hotelId, ...form })
      navigate(`/booking/${res.data.id}`)
    } catch (err) {
      setError(err.response?.data?.error || 'Booking failed. Please try again.')
    } finally {
      setSubmitting(false)
    }
  }

  if (loading) return (
    <div className="pt-16 min-h-screen flex items-center justify-center">
      <div className="w-10 h-10 border-2 border-gold-400 border-t-transparent rounded-full animate-spin" />
    </div>
  )

  const PAYMENT_OPTIONS = [
    { value: 'card', label: 'Credit / Debit Card' },
    { value: 'paypal', label: 'PayPal' },
    { value: 'bank', label: 'Bank Transfer' },
  ]

  return (
    <div className="pt-20 min-h-screen">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <button onClick={() => navigate(-1)} className="btn-ghost mb-6 flex items-center gap-2 text-sm">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
          Back
        </button>

        <h1 className="text-3xl font-bold text-white mb-1">Complete Booking</h1>
        <p className="text-slate-400 mb-8">{hotel?.name}</p>

        <div className="flex flex-col lg:flex-row gap-8">
          {/* Form */}
          <form onSubmit={handleSubmit} className="flex-1 space-y-6">
            {error && (
              <div className="bg-red-400/10 border border-red-400/30 text-red-400 text-sm rounded-xl px-4 py-3">
                {error}
              </div>
            )}

            <div className="card p-6">
              <h3 className="text-white font-semibold mb-5">Stay Details</h3>
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label className="label">Check-in Date</label>
                  <input className="input" type="date" required value={form.checkInDate}
                    min={new Date().toISOString().split('T')[0]} onChange={set('checkInDate')} />
                </div>
                <div>
                  <label className="label">Check-out Date</label>
                  <input className="input" type="date" required value={form.checkOutDate}
                    min={form.checkInDate || new Date().toISOString().split('T')[0]} onChange={set('checkOutDate')} />
                </div>
              </div>
              <div className="grid grid-cols-3 gap-4">
                {[['Adults', 'guestsAdults', 1, 10], ['Children', 'guestsChildren', 0, 10], ['Rooms', 'roomsCount', 1, 10]].map(([label, key, min, max]) => (
                  <div key={key}>
                    <label className="label">{label}</label>
                    <input className="input" type="number" min={min} max={max}
                      value={form[key]} onChange={set(key)} />
                  </div>
                ))}
              </div>
            </div>

            <div className="card p-6">
              <h3 className="text-white font-semibold mb-5">Payment Method</h3>
              <div className="space-y-3">
                {PAYMENT_OPTIONS.map(opt => (
                  <label key={opt.value}
                    className={`flex items-center gap-3 p-4 rounded-xl border cursor-pointer transition-all ${
                      form.paymentMethod === opt.value
                        ? 'border-gold-400 bg-gold-400/10' : 'border-white/10 hover:border-white/20'
                    }`}>
                    <input type="radio" name="payment" value={opt.value}
                      checked={form.paymentMethod === opt.value} onChange={set('paymentMethod')} className="accent-gold-400" />
                    <span className="text-slate-300 text-sm font-medium">{opt.label}</span>
                  </label>
                ))}
              </div>
            </div>

            <div className="card p-6">
              <h3 className="text-white font-semibold mb-4">Special Requests</h3>
              <textarea className="input resize-none" rows={3}
                placeholder="Any special requests or preferences?"
                value={form.specialRequests} onChange={set('specialRequests')} />
            </div>

            <button type="submit" disabled={submitting} className="btn-primary w-full text-center text-base py-4">
              {submitting ? 'Processing...' : `Confirm Booking · $${total}`}
            </button>
          </form>

          {/* Summary */}
          <div className="w-full lg:w-72 shrink-0">
            <div className="card p-6 sticky top-24">
              <img src={hotelImg(hotel?.mainImage)} alt={hotel?.name}
                onError={e => { e.target.src = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&q=80' }}
                className="w-full h-36 object-cover rounded-xl mb-4" />
              <h4 className="text-white font-semibold mb-1">{hotel?.name}</h4>
              <p className="text-slate-400 text-sm mb-5">{hotel?.city}, {hotel?.country}</p>

              <div className="space-y-2 text-sm">
                {[
                  ['Dates', `${form.checkInDate || '—'} → ${form.checkOutDate || '—'}`],
                  ['Duration', `${nights} night${nights !== 1 ? 's' : ''}`],
                  ['Rooms', form.roomsCount],
                  ['Guests', `${form.guestsAdults} adults, ${form.guestsChildren} children`],
                ].map(([k, v]) => (
                  <div key={k} className="flex justify-between text-slate-400">
                    <span>{k}</span><span className="text-white">{v}</span>
                  </div>
                ))}
              </div>

              <div className="border-t border-white/10 my-4" />
              <div className="space-y-2 text-sm">
                <div className="flex justify-between text-slate-400"><span>Subtotal</span><span>${subtotal}</span></div>
                <div className="flex justify-between text-slate-400"><span>Tax (10%)</span><span>${tax}</span></div>
                <div className="flex justify-between text-white font-bold text-base mt-2">
                  <span>Total</span><span className="text-gold-400">${total}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
