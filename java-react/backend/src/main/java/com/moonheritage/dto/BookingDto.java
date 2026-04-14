package com.moonheritage.dto;

import com.moonheritage.model.Booking;
import java.math.BigDecimal;
import java.time.LocalDate;

public record BookingDto(
    Long id,
    String bookingNumber,
    Long hotelId,
    String hotelName,
    String hotelImage,
    LocalDate checkInDate,
    LocalDate checkOutDate,
    Integer totalNights,
    Integer roomsCount,
    Integer guestsAdults,
    Integer guestsChildren,
    BigDecimal totalAmount,
    String paymentMethod,
    String paymentStatus,
    String bookingStatus,
    String specialRequests
) {
    public static BookingDto fromEntity(Booking booking) {
        return new BookingDto(
            booking.getId(),
            booking.getBookingNumber(),
            booking.getHotel().getId(),
            booking.getHotel().getName(),
            booking.getHotel().getMainImage(),
            booking.getCheckInDate(),
            booking.getCheckOutDate(),
            booking.getTotalNights(),
            booking.getRoomsCount(),
            booking.getGuestsAdults(),
            booking.getGuestsChildren(),
            booking.getTotalAmount(),
            booking.getPaymentMethod(),
            booking.getPaymentStatus() != null ? booking.getPaymentStatus().name() : null,
            booking.getBookingStatus() != null ? booking.getBookingStatus().name() : null,
            booking.getSpecialRequests()
        );
    }
}
