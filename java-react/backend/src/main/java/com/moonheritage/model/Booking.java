package com.moonheritage.model;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;
import org.springframework.data.annotation.Id;
import org.springframework.data.annotation.CreatedDate;
import org.springframework.data.annotation.LastModifiedDate;
import org.springframework.data.mongodb.core.index.Indexed;
import org.springframework.data.mongodb.core.mapping.Document;

import java.math.BigDecimal;
import java.time.LocalDate;
import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
@Document(collection = "bookings")
public class Booking {

    @Id
    private String id;

    @Indexed(unique = true)
    private String bookingNumber;

    // Store only IDs to avoid deep embedding
    private String userId;
    private String hotelId;
    private String roomId;

    // Denormalized snapshot at booking time
    private String hotelName;
    private String hotelCity;
    private String hotelCountry;
    private String hotelMainImage;
    private String roomType;

    private LocalDate checkInDate;
    private LocalDate checkOutDate;
    private Integer totalNights;
    private Integer guestsAdults = 1;
    private Integer guestsChildren = 0;
    private Integer roomsCount = 1;
    private BigDecimal subtotal;
    private BigDecimal taxAmount = BigDecimal.ZERO;
    private BigDecimal discountAmount = BigDecimal.ZERO;
    private BigDecimal totalAmount;

    private PaymentStatus paymentStatus = PaymentStatus.pending;
    private String paymentMethod;
    private BookingStatus bookingStatus = BookingStatus.pending;
    private String specialRequests;
    private String cancellationReason;
    private LocalDateTime cancelledAt;

    @CreatedDate
    private LocalDateTime createdAt;

    @LastModifiedDate
    private LocalDateTime updatedAt;

    public enum PaymentStatus { pending, paid, failed, refunded }
    public enum BookingStatus { confirmed, pending, cancelled, completed }
}
