package com.moonheritage.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import jakarta.persistence.*;
import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;
import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
@Entity
@JsonIgnoreProperties({"hibernateLazyInitializer", "handler"})
@Table(name = "reviews")
public class Review {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "user_id", nullable = false)
    @JsonIgnoreProperties({"password", "handler", "hibernateLazyInitializer"})
    private User user;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "hotel_id", nullable = false)
    @JsonIgnoreProperties({"rooms", "images", "amenities", "handler", "hibernateLazyInitializer"})
    private Hotel hotel;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "booking_id")
    @JsonIgnoreProperties({"user", "hotel", "room", "handler", "hibernateLazyInitializer"})
    private Booking booking;

    @Column(nullable = false, precision = 2, scale = 1)
    private BigDecimal rating;

    @Column(length = 200)
    private String title;

    @Column(nullable = false, columnDefinition = "TEXT")
    private String comment;

    @Column(name = "cleanliness_rating", precision = 2, scale = 1)
    private BigDecimal cleanlinessRating;

    @Column(name = "service_rating", precision = 2, scale = 1)
    private BigDecimal serviceRating;

    @Column(name = "location_rating", precision = 2, scale = 1)
    private BigDecimal locationRating;

    @Column(name = "value_rating", precision = 2, scale = 1)
    private BigDecimal valueRating;

    @Column(name = "verified_booking")
    private Boolean verifiedBooking = false;

    @Column(name = "helpful_count")
    private Integer helpfulCount = 0;

    @Enumerated(EnumType.STRING)
    @Column(columnDefinition = "ENUM('approved','pending','rejected') DEFAULT 'pending'")
    private ReviewStatus status = ReviewStatus.pending;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
        updatedAt = LocalDateTime.now();
    }

    @PreUpdate
    protected void onUpdate() {
        updatedAt = LocalDateTime.now();
    }

    public enum ReviewStatus { approved, pending, rejected }
}
