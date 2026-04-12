package com.moonheritage.dto;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.Data;
import java.math.BigDecimal;

@Data
public class HotelRequest {

    @NotBlank
    private String name;

    @NotBlank
    private String address;

    @NotBlank
    private String city;

    private String state;

    @NotBlank
    private String country;

    private String postalCode;

    private String description;
    private String shortDescription;

    private String phone;
    private String email;
    private String website;

    private String category = "hotel";

    private BigDecimal starRating = BigDecimal.ZERO;

    @NotNull
    private BigDecimal pricePerNight;

    private BigDecimal originalPrice;
    private Integer discountPercentage = 0;
    private Boolean featured = false;
    private Integer totalRooms = 0;
    private Integer availableRooms = 0;
    private String status = "active";
}
