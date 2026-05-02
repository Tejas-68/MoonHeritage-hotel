package com.moonheritage.dto;

import jakarta.validation.constraints.*;
import lombok.Data;
import java.math.BigDecimal;

@Data
public class ReviewRequest {
    @NotNull
    private String hotelId;

    private String bookingId;

    @NotNull @DecimalMin("1.0") @DecimalMax("5.0")
    private BigDecimal rating;

    @Size(max = 200)
    private String title;

    @NotBlank
    private String comment;

    private BigDecimal cleanlinessRating;
    private BigDecimal serviceRating;
    private BigDecimal locationRating;
    private BigDecimal valueRating;
}
