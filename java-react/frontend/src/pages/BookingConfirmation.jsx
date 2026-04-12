import { useState, useEffect } from 'react'
import { useParams, useNavigate, Link } from 'react-router-dom'
import api from '../api/axios'

export default function BookingConfirmation() {
  const { bookingId } = useParams()
  const navigate = useNavigate()
  const [booking, setBooking] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api.get('/bookings/my')
      .then(r => {
        const found = r.data.find(b => b.id === +bookingId)
        if (found) setBooking(found)
        else navigate('/profile')
      })
      .catch(() => navigate('/profile'))
      .finally(() => setLoading(false))
  }, [bookingId, navigate])

  if (loading) return (
    <div className="pt-16 min-h-screen flex items-center justify-center">
      <div className="w-10 h-10 border-2 border-gold-400 border-t-transparent rounded-full animate-spin" />
    </div>
  )
  if (!booking) return null

  return (
    <div className="pt-24 min-h-screen px-4">
      <div className="max-w-lg mx-auto">
        <div className="card p-8 text-center animate-slide-up">
          <div className="w-16 h-16 rounded-full bg-green-400/20 flex items-center justify-center mx-auto mb-5">
            <svg className="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>

          <h1 className="text-2xl font-bold text-white mb-2">Booking Confirmed!</h1>
          <p className="text-slate-400 text-sm mb-6">Your reservation has been successfully placed.</p>

          <div className="bg-luxury-700 rounded-xl p-4 mb-6">
            <div className="text-slate-400 text-xs mb-1">Booking Reference</div>
            <div className="text-gold-400 font-bold text-xl tracking-wider">{booking.bookingNumber}</div>
          </div>

          <div className="space-y-3 text-sm mb-6">
            {[
              ['Hotel', booking.hotel?.name],
              ['Check-in', booking.checkInDate],
              ['Check-out', booking.checkOutDate],
              ['Nights', booking.totalNights],
              ['Guests', `${booking.guestsAdults} adults, ${booking.guestsChildren} children`],
              ['Rooms', booking.roomsCount],
            ].map(([k, v]) => (
              <div key={k} className="flex justify-between py-2 border-b border-white/5">
                <span className="text-slate-400">{k}</span>
                <span className="text-white font-medium">{v}</span>
              </div>
            ))}
            <div className="flex justify-between py-2">
              <span className="text-slate-300 font-semibold">Total</span>
              <span className="text-gold-400 font-bold text-lg">${booking.totalAmount}</span>
            </div>
          </div>

          <div className="flex gap-3">
            <Link to="/profile" className="btn-outline flex-1 text-center text-sm">View Bookings</Link>
            <Link to="/hotels" className="btn-primary flex-1 text-center text-sm">Explore More</Link>
          </div>
        </div>
      </div>
    </div>
  )
}
