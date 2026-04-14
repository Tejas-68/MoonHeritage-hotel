import { useState, useEffect, useCallback } from 'react'
import { useSearchParams } from 'react-router-dom'
import api from '../api/axios'
import HotelCard from '../components/HotelCard'

const CATEGORIES = ['', 'hotel', 'villa', 'resort', 'apartment', 'cottage']
const SORT_OPTIONS = [
  { value: 'featured',    label: 'Featured' },
  { value: 'price_asc',  label: 'Price: Low to High' },
  { value: 'price_desc', label: 'Price: High to Low' },
  { value: 'rating',     label: 'Top Rated' },
  { value: 'name',       label: 'Name A–Z' },
]

export default function Hotels() {
  const [searchParams] = useSearchParams()
  const [hotels, setHotels] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [totalPages, setTotalPages] = useState(0)
  const [totalItems, setTotalItems] = useState(0)
  const [filters, setFilters] = useState({
    search:   searchParams.get('search')   || '',
    city:     searchParams.get('city')     || '',
    category: searchParams.get('category') || '',
    minPrice: '',
    maxPrice: '',
    sort:     'featured',
    page:     0,
  })

  const fetchHotels = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const params = new URLSearchParams()
      if (filters.search)   params.set('search', filters.search)
      if (filters.city)     params.set('city', filters.city)
      if (filters.category) params.set('category', filters.category)
      if (filters.minPrice) params.set('minPrice', filters.minPrice)
      if (filters.maxPrice) params.set('maxPrice', filters.maxPrice)
      params.set('sort', filters.sort)
      params.set('page', filters.page)
      params.set('size', 9)
      const res = await api.get(`/hotels?${params}`)
      setHotels(res.data.hotels)
      setTotalPages(res.data.totalPages)
      setTotalItems(res.data.totalItems)
    } catch (err) { 
      setError(err.response?.data?.error || err.message || 'Failed to load properties')
    }
    finally { setLoading(false) }
  }, [filters])

  useEffect(() => { fetchHotels() }, [fetchHotels])

  const set = (key, value) => setFilters(f => ({ ...f, [key]: value, page: 0 }))
  const clear = () => setFilters({ search:'', city:'', category:'', minPrice:'', maxPrice:'', sort:'featured', page:0 })

  return (
    <div className="pt-20 min-h-screen">
      {/* Header */}
      <div className="bg-luxury-900/50 border-b border-white/10 py-10">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="badge-gold mb-3">Explore our collection</div>
          <h1 className="text-3xl font-bold text-white">Luxury Properties</h1>
          <p className="text-slate-400 mt-1">{totalItems} properties available</p>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex gap-8">
        {/* Filters sidebar */}
        <aside className="hidden lg:block w-64 shrink-0">
          <div className="card p-5 sticky top-24 space-y-5">
            <h3 className="font-semibold text-white">Filters</h3>

            {/* Search */}
            <div>
              <label className="label">Keyword</label>
              <input className="input text-sm" type="text" placeholder="Hotel or city..."
                value={filters.search} onChange={e => set('search', e.target.value)} />
            </div>

            {/* City */}
            <div>
              <label className="label">City</label>
              <input className="input text-sm" type="text" placeholder="e.g. Paris"
                value={filters.city} onChange={e => set('city', e.target.value)} />
            </div>

            {/* Category */}
            <div>
              <label className="label">Category</label>
              <div className="flex flex-wrap gap-2">
                {CATEGORIES.map(cat => (
                  <button key={cat || 'all'}
                    onClick={() => set('category', cat)}
                    className={`text-xs px-3 py-1 rounded-full border transition-all ${
                      filters.category === cat
                        ? 'bg-gold-400 text-luxury-900 border-gold-400 font-semibold'
                        : 'border-white/15 text-slate-400 hover:border-gold-400/50'
                    }`}>
                    {cat || 'All'}
                  </button>
                ))}
              </div>
            </div>

            {/* Price */}
            <div>
              <label className="label">Price / Night</label>
              <div className="flex gap-2">
                <input className="input text-sm" type="number" placeholder="Min"
                  value={filters.minPrice} onChange={e => set('minPrice', e.target.value)} />
                <input className="input text-sm" type="number" placeholder="Max"
                  value={filters.maxPrice} onChange={e => set('maxPrice', e.target.value)} />
              </div>
            </div>

            <button onClick={clear} className="w-full btn-ghost text-sm border border-white/10 rounded-xl py-2">
              Clear Filters
            </button>
          </div>
        </aside>

        {/* Main */}
        <div className="flex-1 min-w-0">
          {/* Toolbar */}
          <div className="flex items-center justify-between mb-6">
            <span className="text-slate-400 text-sm">{totalItems} results</span>
            <select className="input text-sm w-auto py-2 px-3"
              value={filters.sort} onChange={e => set('sort', e.target.value)}>
              {SORT_OPTIONS.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
            </select>
          </div>

          {loading ? (
            <div className="flex justify-center py-20">
              <div className="w-10 h-10 border-2 border-gold-400 border-t-transparent rounded-full animate-spin" />
            </div>
          ) : error ? (
            <div className="text-center py-20">
               <div className="bg-red-500/10 text-red-400 border border-red-500/20 p-4 rounded-xl max-w-lg mx-auto">
                 <p className="font-semibold">Failed to load properties</p>
                 <p className="text-sm mt-1">{error}</p>
                 <button onClick={fetchHotels} className="mt-4 px-4 py-2 border border-red-500/30 rounded-lg text-sm hover:bg-red-500/10">Retry</button>
               </div>
            </div>
          ) : hotels.length === 0 ? (
            <div className="text-center py-20 text-slate-400">
              <p className="text-xl mb-2">No properties found</p>
              <p className="text-sm">Try adjusting your filters</p>
              <button onClick={clear} className="btn-outline mt-4">Clear Filters</button>
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 animate-fade-in">
              {hotels.map(h => <HotelCard key={h.id} hotel={h} />)}
            </div>
          )}

          {/* Pagination */}
          {totalPages > 1 && (
            <div className="flex items-center justify-center gap-4 mt-10">
              <button className="btn-ghost border border-white/10 rounded-xl px-4 py-2 text-sm disabled:opacity-30"
                disabled={filters.page === 0}
                onClick={() => setFilters(f => ({ ...f, page: f.page - 1 }))}>
                Previous
              </button>
              <span className="text-slate-400 text-sm">Page {filters.page + 1} of {totalPages}</span>
              <button className="btn-ghost border border-white/10 rounded-xl px-4 py-2 text-sm disabled:opacity-30"
                disabled={filters.page >= totalPages - 1}
                onClick={() => setFilters(f => ({ ...f, page: f.page + 1 }))}>
                Next
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
