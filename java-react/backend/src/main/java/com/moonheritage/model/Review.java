package com.moonheritage.model;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;
import org.springframework.data.annotation.Id;
import org.springframework.data.annotation.CreatedDate;
import org.springframework.data.annotation.LastModifiedDate;
import org.springframework.data.mongodb.core.mapping.Document;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Data
@NoArgsConstructor
@AllArgsConstructor
@Document(collection = "reviews")
public class Review {

    @Id
    private String id;

    // References by ID
    private String userId;
    private String hotelId;
    private String bookingId;

    // Denormalized user info for display
    private String userFirstName;
    private String userLastName;
    private String userProfileImage;

    private BigDecimal rating;
    private String title;
    private String comment;
    private BigDecimal cleanlinessRating;
    private BigDecimal serviceRating;
    private BigDecimal locationRating;
    private BigDecimal valueRating;
    private Boolean verifiedBooking = false;
    private Integer helpfulCount = 0;
    private ReviewStatus status = ReviewStatus.pending;

    @CreatedDate
    private LocalDateTime createdAt;

    @LastModifiedDate
    private LocalDateTime updatedAt;

    public enum ReviewStatus { approved, pending, rejected }
}
