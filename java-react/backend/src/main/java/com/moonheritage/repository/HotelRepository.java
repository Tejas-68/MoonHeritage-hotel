package com.moonheritage.repository;

import com.moonheritage.model.Hotel;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.math.BigDecimal;
import java.util.List;
import java.util.Optional;

public interface HotelRepository extends JpaRepository<Hotel, Long> {

    Optional<Hotel> findBySlug(String slug);

    List<Hotel> findByFeaturedTrueAndStatusOrderByViewCountDesc(Hotel.HotelStatus status);

    @Query("SELECT h FROM Hotel h WHERE h.status = 'active' " +
           "AND (:city IS NULL OR LOWER(h.city) LIKE LOWER(CONCAT('%', :city, '%'))) " +
           "AND (:category IS NULL OR h.category = :category) " +
           "AND (:minPrice IS NULL OR h.pricePerNight >= :minPrice) " +
           "AND (:maxPrice IS NULL OR h.pricePerNight <= :maxPrice) " +
           "AND (:search IS NULL OR (LOWER(h.name) LIKE LOWER(CONCAT('%', :search, '%')) " +
           "     OR LOWER(h.city) LIKE LOWER(CONCAT('%', :search, '%')) " +
           "     OR LOWER(h.country) LIKE LOWER(CONCAT('%', :search, '%'))))")
    Page<Hotel> searchHotels(
        @Param("city") String city,
        @Param("category") Hotel.Category category,
        @Param("minPrice") BigDecimal minPrice,
        @Param("maxPrice") BigDecimal maxPrice,
        @Param("search") String search,
        Pageable pageable
    );
}
