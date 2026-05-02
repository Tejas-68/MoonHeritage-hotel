package com.moonheritage.repository;

import com.moonheritage.model.Booking;
import org.springframework.data.mongodb.repository.MongoRepository;
import java.util.List;
import java.util.Optional;

public interface BookingRepository extends MongoRepository<Booking, String> {
    List<Booking> findByUserIdOrderByCreatedAtDesc(String userId);
    Optional<Booking> findByBookingNumber(String bookingNumber);
    List<Booking> findByHotelId(String hotelId);
}
