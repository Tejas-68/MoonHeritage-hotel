package com.moonheritage.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import jakarta.persistence.*;
import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;
import java.math.BigDecimal;
import java.time.LocalDateTime;
import java.util.List;

@Data
@NoArgsConstructor
@AllArgsConstructor
@Entity
@JsonIgnoreProperties({"hibernateLazyInitializer", "handler"})
@Table(name = "hotels")
public class Hotel {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false, length = 200)
    private String name;

    @Column(unique = true, nullable = false, length = 250)
    private String slug;

    @Column(columnDefinition = "TEXT")
    private String description;

    @Column(name = "short_description", length = 500)
    private String shortDescription;

    @Column(nullable = false, columnDefinition = "TEXT")
    private String address;

    @Column(nullable = false, length = 100)
    private String city;

    @Column(length = 100)
    private String state;

    @Column(nullable = false, length = 100)
    private String country;

    @Column(name = "postal_code", length = 20)
    private String postalCode;

    @Column(precision = 10, scale = 8)
    private BigDecimal latitude;

    @Column(precision = 11, scale = 8)
    private BigDecimal longitude;

    @Column(length = 20)
    private String phone;

    @Column(length = 100)
    private String email;

    @Column(length = 255)
    private String website;

    @Enumerated(EnumType.STRING)
    @Column(columnDefinition = "ENUM('hotel','villa','apartment','resort','cottage') DEFAULT 'hotel'")
    private Category category = Category.hotel;

    @Column(name = "star_rating", precision = 2, scale = 1)
    private BigDecimal starRating = BigDecimal.ZERO;

    @Column(name = "price_per_night", nullable = false, precision = 10, scale = 2)
    private BigDecimal pricePerNight;

    @Column(name = "original_price", precision = 10, scale = 2)
    private BigDecimal originalPrice;

    @Column(name = "discount_percentage")
    private Integer discountPercentage = 0;

    private Boolean featured = false;

    @Column(name = "total_rooms")
    private Integer totalRooms = 0;

    @Column(name = "available_rooms")
    private Integer availableRooms = 0;

    @Column(name = "main_image", length = 255)
    private String mainImage;

    @Enumerated(EnumType.STRING)
    @Column(columnDefinition = "ENUM('active','inactive','pending') DEFAULT 'active'")
    private HotelStatus status = HotelStatus.active;

    @Column(name = "view_count")
    private Integer viewCount = 0;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @Column(name = "updated_at")
    private LocalDateTime updatedAt;

    @OneToMany(mappedBy = "hotel", fetch = FetchType.LAZY)
    @JsonIgnoreProperties("hotel")
    private List<Room> rooms;

    @OneToMany(mappedBy = "hotel", fetch = FetchType.LAZY)
    @JsonIgnoreProperties("hotel")
    private List<HotelImage> images;

    @ManyToMany(fetch = FetchType.LAZY)
    @JoinTable(
        name = "hotel_amenities",
        joinColumns = @JoinColumn(name = "hotel_id"),
        inverseJoinColumns = @JoinColumn(name = "amenity_id")
    )
    @JsonIgnoreProperties("hotels")
    private List<Amenity> amenities;

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
        updatedAt = LocalDateTime.now();
    }

    @PreUpdate
    protected void onUpdate() {
        updatedAt = LocalDateTime.now();
    }

    public enum Category { hotel, villa, apartment, resort, cottage }
    public enum HotelStatus { active, inactive, pending }
}
