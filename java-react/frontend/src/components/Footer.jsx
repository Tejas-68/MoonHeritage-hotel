import { Link } from 'react-router-dom'

export default function Footer() {
  return (
    <footer className="bg-luxury-900 border-t border-white/10 mt-24">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
          <div>
            <div className="flex items-center gap-2 font-bold text-xl mb-3">
              <span className="text-gold-400">Moon</span>
              <span className="text-white">Heritage</span>
            </div>
            <p className="text-slate-400 text-sm leading-relaxed">
              Luxury hotel bookings curated for the discerning traveller. Experience the world in style.
            </p>
          </div>
          <div>
            <h4 className="font-semibold text-white mb-3 text-sm uppercase tracking-wider">Explore</h4>
            <ul className="space-y-2">
              <li><Link to="/hotels" className="text-slate-400 hover:text-gold-400 text-sm transition-colors">All Hotels</Link></li>
              <li><Link to="/hotels?category=resort" className="text-slate-400 hover:text-gold-400 text-sm transition-colors">Resorts</Link></li>
              <li><Link to="/hotels?category=villa" className="text-slate-400 hover:text-gold-400 text-sm transition-colors">Villas</Link></li>
            </ul>
          </div>
          <div>
            <h4 className="font-semibold text-white mb-3 text-sm uppercase tracking-wider">Account</h4>
            <ul className="space-y-2">
              <li><Link to="/login" className="text-slate-400 hover:text-gold-400 text-sm transition-colors">Sign In</Link></li>
              <li><Link to="/signup" className="text-slate-400 hover:text-gold-400 text-sm transition-colors">Register</Link></li>
              <li><Link to="/profile" className="text-slate-400 hover:text-gold-400 text-sm transition-colors">My Bookings</Link></li>
            </ul>
          </div>
        </div>
        <div className="border-t border-white/10 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p className="text-slate-500 text-sm">© {new Date().getFullYear()} MoonHeritage. All rights reserved.</p>
          <p className="text-slate-600 text-sm">Built with Java Spring Boot + React</p>
        </div>
      </div>
    </footer>
  )
}
