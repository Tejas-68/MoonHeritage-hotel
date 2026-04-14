import { useState, useEffect } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import api from '../api/axios'
import HotelCard from '../components/HotelCard'

export default function Home() {
  const [featured, setFeatured] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [search, setSearch] = useState('')
  const navigate = useNavigate()

  useEffect(() => {
    api.get('/hotels/featured')
      .then(r => setFeatured(r.data))
      .catch(err => setError(err.response?.data?.error || err.message || 'Failed to load hotels'))
      .finally(() => setLoading(false))
  }, [])

  const handleSearch = (e) => {
    e.preventDefault()
    if (search.trim()) navigate(`/hotels?search=${encodeURIComponent(search)}`)
    else navigate('/hotels')
  }

  return (
    <div className="pt-16">
      {/* Hero */}
      <section className="relative min-h-[85vh] flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1920&q=80')] bg-cover bg-center" />
        <div className="absolute inset-0 bg-gradient-to-b from-luxury-950/70 via-luxury-950/60 to-luxury-950" />

        <div className="relative z-10 text-center px-4 max-w-4xl mx-auto animate-fade-in">
          <div className="badge-gold mb-6 mx-auto">Luxury Hotel Bookings</div>
          <h1 className="text-5xl sm:text-6xl md:text-7xl font-bold text-white mb-6 leading-tight">
            Find Your <span className="text-transparent bg-clip-text bg-gold-gradient">Perfect</span> Stay
          </h1>
          <p className="text-slate-300 text-lg sm:text-xl mb-10 max-w-2xl mx-auto leading-relaxed">
            Discover curated luxury hotels, villas, and resorts for unforgettable experiences worldwide.
          </p>

          {/* Search */}
          <form onSubmit={handleSearch} className="flex gap-2 max-w-2xl mx-auto">
            <div className="relative flex-1">
              <svg className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                type="text"
                value={search}
                onChange={e => setSearch(e.target.value)}
                placeholder="Search hotels, cities..."
                className="input pl-12 text-base h-14 rounded-2xl"
              />
            </div>
            <button type="submit" className="btn-primary px-8 h-14 rounded-2xl text-base whitespace-nowrap">
              Search
            </button>
          </form>
        </div>

        {/* Scroll indicator */}
        <div className="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
          <div className="w-6 h-10 border-2 border-white/30 rounded-full flex justify-center">
            <div className="w-1 h-3 bg-gold-400 rounded-full mt-2" />
          </div>
        </div>
      </section>

      {/* Stats */}
      <section className="py-16 bg-luxury-900/50 border-y border-white/5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-3 gap-8 text-center">
            {[
              { label: 'Properties', value: '200+' },
              { label: 'Destinations', value: '50+' },
              { label: 'Happy Guests', value: '10K+' },
            ].map(s => (
              <div key={s.label}>
                <div className="text-3xl sm:text-4xl font-bold text-gold-400 mb-1">{s.value}</div>
                <div className="text-slate-400 text-sm">{s.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Featured Hotels */}
      <section className="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-12">
          <div className="badge-gold mb-4 mx-auto">Handpicked for you</div>
          <h2 className="text-3xl sm:text-4xl font-bold text-white">Featured Properties</h2>
          <p className="text-slate-400 mt-3 max-w-xl mx-auto">Our top-rated luxury stays, updated in real time from our collection</p>
        </div>

        {loading ? (
          <div className="flex justify-center py-20">
            <div className="w-10 h-10 border-2 border-gold-400 border-t-transparent rounded-full animate-spin" />
          </div>
        ) : error ? (
          <div className="text-center py-20">
             <div className="bg-red-500/10 text-red-400 border border-red-500/20 p-4 rounded-xl max-w-lg mx-auto">
               <p className="font-semibold">Failed to load featured properties</p>
               <p className="text-sm mt-1">{error}</p>
             </div>
          </div>
        ) : featured.length === 0 ? (
          <div className="text-center py-20 text-slate-400">
            <p>No featured hotels available. Add some from the admin panel.</p>
            <Link to="/hotels" className="btn-outline mt-4 inline-block">Browse All Hotels</Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 animate-slide-up">
            {featured.map(h => <HotelCard key={h.id} hotel={h} />)}
          </div>
        )}

        {featured.length > 0 && (
          <div className="text-center mt-10">
            <Link to="/hotels" className="btn-outline">View All Properties</Link>
          </div>
        )}
      </section>

      {/* CTA */}
      <section className="py-20 mx-4 sm:mx-6 lg:mx-8 mb-8">
        <div className="max-w-4xl mx-auto card p-12 text-center relative overflow-hidden">
          <div className="absolute inset-0 bg-gold-gradient opacity-5" />
          <div className="relative z-10">
            <h2 className="text-3xl font-bold text-white mb-4">Ready to Book Your Stay?</h2>
            <p className="text-slate-400 mb-8 max-w-lg mx-auto">Join thousands of travellers discovering luxury properties worldwide.</p>
            <div className="flex gap-4 justify-center flex-wrap">
              <Link to="/hotels" className="btn-primary">Explore Hotels</Link>
              <Link to="/signup" className="btn-outline">Create Account</Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
