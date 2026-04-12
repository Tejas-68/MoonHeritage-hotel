import { Link } from 'react-router-dom'
import { hotelImg } from '../utils/imageUtil'

export default function HotelCard({ hotel }) {
  const img = hotelImg(hotel.mainImage)

  return (
    <Link to={`/hotels/${hotel.id}`} className="group card overflow-hidden hover:border-gold-400/30 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-black/50 block">
      {/* Image */}
      <div className="relative h-48 overflow-hidden">
        <img
          src={img}
          alt={hotel.name}
          onError={e => { e.target.src = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&q=80' }}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
        />
        {hotel.discountPercentage > 0 && (
          <div className="absolute top-3 left-3 bg-gold-400 text-luxury-900 text-xs font-bold px-2 py-1 rounded-lg">
            {hotel.discountPercentage}% OFF
          </div>
        )}
        {hotel.featured && (
          <div className="absolute top-3 right-3 badge-gold text-xs">Featured</div>
        )}
        <div className="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-luxury-900/80 to-transparent" />
      </div>

      {/* Content */}
      <div className="p-5">
        <div className="flex items-start justify-between gap-2 mb-2">
          <h3 className="font-semibold text-white text-base leading-tight group-hover:text-gold-400 transition-colors line-clamp-1">
            {hotel.name}
          </h3>
          <span className="text-xs text-slate-400 capitalize bg-white/5 px-2 py-0.5 rounded-full shrink-0">
            {hotel.category}
          </span>
        </div>

        <p className="text-slate-400 text-sm mb-3 flex items-center gap-1">
          <svg className="w-3.5 h-3.5 text-gold-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fillRule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clipRule="evenodd" />
          </svg>
          {hotel.city}, {hotel.country}
        </p>

        {hotel.shortDescription && (
          <p className="text-slate-500 text-xs mb-4 line-clamp-2">{hotel.shortDescription}</p>
        )}

        <div className="flex items-center justify-between pt-3 border-t border-white/5">
          <div>
            {hotel.originalPrice && hotel.originalPrice > hotel.pricePerNight && (
              <span className="text-slate-500 line-through text-xs mr-1">${hotel.originalPrice}</span>
            )}
            <span className="text-gold-400 font-bold text-lg">${hotel.pricePerNight}</span>
            <span className="text-slate-400 text-xs">/night</span>
          </div>
          <div className="flex items-center gap-1">
            <svg className="w-4 h-4 text-gold-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span className="text-white text-sm font-medium">{hotel.starRating}</span>
          </div>
        </div>
      </div>
    </Link>
  )
}
