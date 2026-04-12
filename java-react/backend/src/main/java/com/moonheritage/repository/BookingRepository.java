package com.moonheritage.repository;

import com.moonheritage.model.Booking;
import com.moonheritage.model.User;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;
import java.util.Optional;

public interface BookingRepository extends JpaRepository<Booking, Long> {
    List<Booking> findByUserOrderByCreatedAtDesc(User user);
    Optional<Booking> findByBookingNumber(String bookingNumber);
}
