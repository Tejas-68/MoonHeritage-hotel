import { useState, useEffect } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import api from '../api/axios'
import { useAuth } from '../context/AuthContext'
import { hotelImg } from '../utils/imageUtil'

const TABS = ['overview', 'rooms', 'amenities', 'reviews']

export default function HotelDetail() {
  const { id } = useParams()
  const navigate = useNavigate()
  const { isAuthenticated } = useAuth()
  const [hotel, setHotel] = useState(null)
  const [reviews, setReviews] = useState([])
  const [loading, setLoading] = useState(true)
  const [activeImg, setActiveImg] = useState(0)
  const [activeTab, setActiveTab] = useState('overview')

  useEffect(() => {
    setLoading(true)
    Promise.all([
      api.get(`/hotels/${id}`),
      api.get(`/reviews/hotel/${id}`),
    ]).then(([hotelRes, reviewRes]) => {
      setHotel(hotelRes.data)
      setReviews(reviewRes.data.content || [])
    }).catch(() => navigate('/hotels'))
      .finally(() => setLoading(false))
  }, [id, navigate])

  if (loading) return (
    <div className="pt-16 min-h-screen flex items-center justify-center">
      <div className="w-10 h-10 border-2 border-gold-400 border-t-transparent rounded-full animate-spin" />
    </div>
  )
  if (!hotel) return null

  const images = hotel.images?.length
    ? hotel.images.map(i => hotelImg(i.imagePath))
    : [hotelImg(hotel.mainImage)]

  return (
    <div className="pt-20 min-h-screen">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <button onClick={() => navigate(-1)} className="btn-ghost mb-6 flex items-center gap-2 text-sm">
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
          Back
        </button>

        {/* Gallery */}
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-3 mb-8 rounded-2xl overflow-hidden">
          <div className="lg:col-span-3">
            <img src={images[activeImg]} alt={hotel.name}
              onError={e => { e.target.src = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=80' }}
              className="w-full h-72 sm:h-96 object-cover" />
          </div>
          {images.length > 1 && (
            <div className="flex lg:flex-col gap-2">
              {images.slice(0, 4).map((img, i) => (
                <button key={i} onClick={() => setActiveImg(i)}
                  className={`flex-1 lg:flex-none overflow-hidden rounded-lg border-2 transition-all ${i === activeImg ? 'border-gold-400' : 'border-transparent'}`}>
                  <img src={img} alt="" className="w-full h-20 object-cover" />
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Layout */}
        <div className="flex flex-col lg:flex-row gap-8">
          {/* Left */}
          <div className="flex-1 min-w-0">
            {/* Header */}
            <div className="mb-6">
              <span className="badge-gold text-xs capitalize mb-3 inline-block">{hotel.category}</span>
              <h1 className="text-3xl font-bold text-white mb-2">{hotel.name}</h1>
              <p className="text-slate-400 flex items-center gap-1.5 mb-3">
                <svg className="w-4 h-4 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clipRule="evenodd" />
                </svg>
                {hotel.address}, {hotel.city}, {hotel.country}
              </p>
              <div className="flex items-center gap-3">
                <div className="flex items-center gap-1">
                  {[1,2,3,4,5].map(s => (
                    <svg key={s} className={`w-4 h-4 ${s <= hotel.starRating ? 'text-gold-400' : 'text-slate-600'}`} fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                  ))}
                </div>
                <span className="text-slate-400 text-sm">({reviews.length} reviews)</span>
              </div>
            </div>

            {/* Tabs */}
            <div className="flex gap-1 mb-6 bg-luxury-800 rounded-xl p-1">
              {TABS.map(tab => (
                <button key={tab} onClick={() => setActiveTab(tab)}
                  className={`flex-1 py-2 text-sm font-medium rounded-lg transition-all capitalize ${
                    activeTab === tab ? 'bg-gold-400 text-luxury-900' : 'text-slate-400 hover:text-white'
                  }`}>
                  {tab}
                </button>
              ))}
            </div>

            {/* Tab content */}
            {activeTab === 'overview' && (
              <div className="animate-fade-in space-y-4">
                <p className="text-slate-300 leading-relaxed">{hotel.description || hotel.shortDescription}</p>
                <div className="grid grid-cols-2 gap-3">
                  {[
                    ['Check-in', '14:00'],
                    ['Check-out', '11:00'],
                    ['Available rooms', hotel.availableRooms],
                    ['Phone', hotel.phone || 'On request'],
                  ].map(([k,v]) => (
                    <div key={k} className="card p-4">
                      <div className="text-slate-400 text-xs mb-1">{k}</div>
                      <div className="text-white text-sm font-medium">{v}</div>
                    </div>
                  ))}
                </div>
              </div>
            )}

            {activeTab === 'rooms' && (
              <div className="animate-fade-in space-y-3">
                {hotel.rooms?.length > 0
                  ? hotel.rooms.map(room => (
                    <div key={room.id} className="card p-5 flex items-center justify-between gap-4">
                      <div>
                        <h4 className="text-white font-semibold mb-1">{room.roomType}</h4>
                        <p className="text-slate-400 text-sm mb-2">{room.description}</p>
                        <div className="flex gap-3 text-xs text-slate-400">
                          <span>Up to {room.maxOccupancy} guests</span>
                          {room.bedType && <span>• {room.bedType}</span>}
                          {room.sizeSqm && <span>• {room.sizeSqm} m²</span>}
                        </div>
                      </div>
                      <div className="text-right shrink-0">
                        <div className="text-gold-400 font-bold text-lg">${room.pricePerNight}</div>
                        <div className="text-slate-400 text-xs">/night</div>
                      </div>
                    </div>
                  ))
                  : <p className="text-slate-400">Room details not available.</p>
                }
              </div>
            )}

            {activeTab === 'amenities' && (
              <div className="animate-fade-in">
                {hotel.amenities?.length > 0 ? (
                  <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    {hotel.amenities.map(a => (
                      <div key={a.id} className="card p-3 flex items-center gap-3 text-sm text-slate-300">
                        <span className="text-gold-400 text-base">✓</span>
                        {a.name}
                      </div>
                    ))}
                  </div>
                ) : <p className="text-slate-400">Amenities data not available.</p>}
              </div>
            )}

            {activeTab === 'reviews' && (
              <div className="animate-fade-in space-y-4">
                {reviews.length === 0
                  ? <p className="text-slate-400">No reviews yet. Be the first!</p>
                  : reviews.map(r => (
                    <div key={r.id} className="card p-5">
                      <div className="flex items-center justify-between mb-3">
                        <div className="flex items-center gap-3">
                          <div className="w-9 h-9 rounded-full bg-gold-400/20 flex items-center justify-center text-gold-400 font-semibold text-sm">
                            {r.user?.firstName?.[0] || 'G'}
                          </div>
                          <div>
                            <div className="text-white text-sm font-medium">{r.user?.firstName} {r.user?.lastName}</div>
                            <div className="text-slate-500 text-xs">{new Date(r.createdAt).toLocaleDateString()}</div>
                          </div>
                        </div>
                        <div className="flex items-center gap-1">
                          <svg className="w-4 h-4 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                          </svg>
                          <span className="text-sm text-white">{r.rating}</span>
                        </div>
                      </div>
                      {r.title && <h5 className="text-white text-sm font-medium mb-1">{r.title}</h5>}
                      <p className="text-slate-400 text-sm">{r.comment}</p>
                    </div>
                  ))
                }
              </div>
            )}
          </div>

          {/* Booking sidebar */}
          <div className="w-full lg:w-72 shrink-0">
            <div className="card p-6 sticky top-24">
              <div className="mb-4">
                {hotel.originalPrice > hotel.pricePerNight && (
                  <div className="text-slate-400 line-through text-sm">${hotel.originalPrice}</div>
                )}
                <div className="flex items-baseline gap-1">
                  <span className="text-3xl font-bold text-gold-400">${hotel.pricePerNight}</span>
                  <span className="text-slate-400 text-sm">/night</span>
                </div>
                {hotel.discountPercentage > 0 && (
                  <div className="badge-gold text-xs mt-2">{hotel.discountPercentage}% OFF</div>
                )}
              </div>

              <button onClick={() => { if (!isAuthenticated) navigate('/login'); else navigate(`/book/${hotel.id}`) }}
                className="btn-primary w-full text-center mb-4">
                {isAuthenticated ? 'Reserve Now' : 'Login to Book'}
              </button>

              <div className="space-y-2 text-sm text-slate-400">
                <div className="flex items-center gap-2"><span className="text-gold-400">✓</span> Free cancellation available</div>
                <div className="flex items-center gap-2"><span className="text-gold-400">✓</span> Secure & encrypted booking</div>
                <div className="flex items-center gap-2"><span className="text-gold-400">✓</span> Best price guarantee</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
