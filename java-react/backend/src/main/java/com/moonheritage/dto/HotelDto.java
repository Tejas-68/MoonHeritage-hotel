package com.moonheritage.dto;

import com.moonheritage.model.Hotel;
import java.math.BigDecimal;
import java.util.List;

public record HotelDto(
    Long id,
    String name,
    String slug,
    String shortDescription,
    String address,
    String city,
    String state,
    String country,
    BigDecimal starRating,
    BigDecimal pricePerNight,
    BigDecimal originalPrice,
    Integer discountPercentage,
    Boolean featured,
    String mainImage,
    String category,
    Integer availableRooms,
    List<String> amenities
) {
    public static HotelDto fromEntity(Hotel hotel) {
        return new HotelDto(
            hotel.getId(),
            hotel.getName(),
            hotel.getSlug(),
            hotel.getShortDescription(),
            hotel.getAddress(),
            hotel.getCity(),
            hotel.getState(),
            hotel.getCountry(),
            hotel.getStarRating(),
            hotel.getPricePerNight(),
            hotel.getOriginalPrice(),
            hotel.getDiscountPercentage(),
            hotel.getFeatured(),
            hotel.getMainImage(),
            hotel.getCategory() != null ? hotel.getCategory().name() : null,
            hotel.getAvailableRooms(),
            org.hibernate.Hibernate.isInitialized(hotel.getAmenities()) && hotel.getAmenities() != null 
                ? hotel.getAmenities().stream().map(a -> a.getName()).toList() 
                : null
        );
    }
}
