const FALLBACK = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800&q=80'

export function hotelImg(path) {
  if (!path) return FALLBACK
  if (path.startsWith('http')) return path
  return `http://localhost/MoonHeritage/images/${path}`
}
