package com.moonheritage.service;

import com.moonheritage.dto.BookingRequest;
import com.moonheritage.model.*;
import com.moonheritage.repository.*;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.math.BigDecimal;
import java.math.RoundingMode;
import java.time.LocalDateTime;
import java.time.temporal.ChronoUnit;
import java.util.List;
import java.util.UUID;

@Service
@RequiredArgsConstructor
public class BookingService {

    private final BookingRepository bookingRepository;
    private final HotelRepository hotelRepository;
    private final UserRepository userRepository;

    private static final BigDecimal TAX_RATE = new BigDecimal("0.10");

    @Transactional
    public Booking createBooking(BookingRequest request, String userEmail) {
        User user = userRepository.findByEmail(userEmail)
                .orElseThrow(() -> new RuntimeException("User not found"));

        Hotel hotel = hotelRepository.findById(request.getHotelId())
                .orElseThrow(() -> new RuntimeException("Hotel not found"));

        if (request.getCheckInDate().isBefore(java.time.LocalDate.now())) {
            throw new RuntimeException("Check-in date cannot be in the past");
        }

        long nights = ChronoUnit.DAYS.between(request.getCheckInDate(), request.getCheckOutDate());
        if (nights < 1) throw new RuntimeException("Check-out must be after check-in");

        if (request.getRoomsCount() > hotel.getAvailableRooms()) {
            throw new RuntimeException("Not enough rooms available. Only " + hotel.getAvailableRooms() + " left.");
        }

        BigDecimal pricePerNight = hotel.getPricePerNight();
        BigDecimal subtotal = pricePerNight
                .multiply(BigDecimal.valueOf(nights))
                .multiply(BigDecimal.valueOf(request.getRoomsCount()));
        BigDecimal tax = subtotal.multiply(TAX_RATE).setScale(2, RoundingMode.HALF_UP);
        BigDecimal total = subtotal.add(tax);

        Booking booking = new Booking();
        booking.setBookingNumber("MH-" + UUID.randomUUID().toString().substring(0, 8).toUpperCase());
        booking.setUser(user);
        booking.setHotel(hotel);
        booking.setCheckInDate(request.getCheckInDate());
        booking.setCheckOutDate(request.getCheckOutDate());
        booking.setTotalNights((int) nights);
        booking.setGuestsAdults(request.getGuestsAdults());
        booking.setGuestsChildren(request.getGuestsChildren());
        booking.setRoomsCount(request.getRoomsCount());
        booking.setSubtotal(subtotal);
        booking.setTaxAmount(tax);
        booking.setDiscountAmount(BigDecimal.ZERO);
        booking.setTotalAmount(total);
        booking.setPaymentMethod(request.getPaymentMethod());
        booking.setPaymentStatus(Booking.PaymentStatus.pending);
        booking.setBookingStatus(Booking.BookingStatus.confirmed);
        booking.setSpecialRequests(request.getSpecialRequests());

        // Decrement inventory
        hotel.setAvailableRooms(hotel.getAvailableRooms() - request.getRoomsCount());
        hotelRepository.save(hotel);

        return bookingRepository.save(booking);
    }

    public List<Booking> getUserBookings(String userEmail) {
        User user = userRepository.findByEmail(userEmail)
                .orElseThrow(() -> new RuntimeException("User not found"));
        return bookingRepository.findByUserOrderByCreatedAtDesc(user);
    }

    @Transactional
    public Booking cancelBooking(Long bookingId, String userEmail, String reason) {
        Booking booking = bookingRepository.findById(bookingId)
                .orElseThrow(() -> new RuntimeException("Booking not found"));

        if (!booking.getUser().getEmail().equals(userEmail)) {
            throw new RuntimeException("Not authorized to cancel this booking");
        }
        if (booking.getBookingStatus() == Booking.BookingStatus.cancelled) {
            throw new RuntimeException("Booking already cancelled");
        }

        booking.setBookingStatus(Booking.BookingStatus.cancelled);
        booking.setCancellationReason(reason);
        booking.setCancelledAt(LocalDateTime.now());
        return bookingRepository.save(booking);
    }
}
