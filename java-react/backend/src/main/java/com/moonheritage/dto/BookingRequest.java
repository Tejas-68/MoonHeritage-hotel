package com.moonheritage.dto;

import jakarta.validation.constraints.*;
import lombok.Data;
import java.time.LocalDate;

@Data
public class BookingRequest {
    @NotNull
    private Long hotelId;

    private Long roomId;

    @NotNull
    private LocalDate checkInDate;

    @NotNull
    private LocalDate checkOutDate;

    @Min(1)
    private Integer guestsAdults = 1;

    @Min(0)
    private Integer guestsChildren = 0;

    @Min(1)
    private Integer roomsCount = 1;

    private String specialRequests;

    private String paymentMethod;
}
