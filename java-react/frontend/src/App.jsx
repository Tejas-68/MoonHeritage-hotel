import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider, useAuth } from './context/AuthContext'
import Navbar from './components/Navbar'
import Footer from './components/Footer'
import Home from './pages/Home'
import Hotels from './pages/Hotels'
import HotelDetail from './pages/HotelDetail'
import Booking from './pages/Booking'
import BookingConfirmation from './pages/BookingConfirmation'
import Login from './pages/Login'
import Signup from './pages/Signup'
import Profile from './pages/Profile'
import AdminDashboard from './pages/AdminDashboard'

function ProtectedRoute({ children }) {
  const { isAuthenticated } = useAuth()
  return isAuthenticated ? children : <Navigate to="/login" replace />
}

function AdminRoute({ children }) {
  const { isAuthenticated, isAdmin } = useAuth()
  if (!isAuthenticated) return <Navigate to="/login" replace />
  if (!isAdmin) return <Navigate to="/" replace />
  return children
}

function AppRoutes() {
  return (
    <>
      <Navbar />
      <main>
        <Routes>
          <Route path="/"                    element={<Home />} />
          <Route path="/hotels"              element={<Hotels />} />
          <Route path="/hotels/:id"          element={<HotelDetail />} />
          <Route path="/login"               element={<Login />} />
          <Route path="/signup"              element={<Signup />} />
          <Route path="/book/:hotelId"       element={<ProtectedRoute><Booking /></ProtectedRoute>} />
          <Route path="/booking/:bookingId"  element={<ProtectedRoute><BookingConfirmation /></ProtectedRoute>} />
          <Route path="/profile"             element={<ProtectedRoute><Profile /></ProtectedRoute>} />
          <Route path="/admin"               element={<AdminRoute><AdminDashboard /></AdminRoute>} />
          <Route path="*"                    element={<Navigate to="/" replace />} />
        </Routes>
      </main>
      <Footer />
    </>
  )
}

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <AppRoutes />
      </BrowserRouter>
    </AuthProvider>
  )
}
