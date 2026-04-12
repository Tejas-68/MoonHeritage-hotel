export default function StarRating({ rating = 0, size = 'md' }) {
  const stars = []
  const full = Math.floor(rating)
  const half = rating % 1 >= 0.5

  for (let i = 1; i <= 5; i++) {
    if (i <= full) {
      stars.push(<i key={i} className={`fas fa-star star filled ${size}`} />)
    } else if (i === full + 1 && half) {
      stars.push(<i key={i} className={`fas fa-star-half-alt star filled ${size}`} />)
    } else {
      stars.push(<i key={i} className={`far fa-star star ${size}`} />)
    }
  }

  return <div className="stars">{stars}</div>
}
