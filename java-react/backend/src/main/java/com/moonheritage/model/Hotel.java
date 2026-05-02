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
import java.time.LocalDateTime;
import java.util.List;

@Data
@NoArgsConstructor
@AllArgsConstructor
@Document(collection = "hotels")
public class Hotel {

    @Id
    private String id;

    private String name;

    @Indexed(unique = true)
    private String slug;

    private String description;
    private String shortDescription;
    private String address;
    private String city;
    private String state;
    private String country;
    private String postalCode;
    private BigDecimal latitude;
    private BigDecimal longitude;
    private String phone;
    private String email;
    private String website;

    private Category category = Category.hotel;
    private BigDecimal starRating = BigDecimal.ZERO;
    private BigDecimal pricePerNight;
    private BigDecimal originalPrice;
    private Integer discountPercentage = 0;
    private Boolean featured = false;
    private Integer totalRooms = 0;
    private Integer availableRooms = 0;
    private String mainImage;
    private HotelStatus status = HotelStatus.active;
    private Integer viewCount = 0;

    // Embedded subdocuments (no joins in MongoDB)
    private List<Room> rooms;
    private List<HotelImage> images;
    private List<Amenity> amenities;

    @CreatedDate
    private LocalDateTime createdAt;

    @LastModifiedDate
    private LocalDateTime updatedAt;

    public enum Category { hotel, villa, apartment, resort, cottage }
    public enum HotelStatus { active, inactive, pending }
}
