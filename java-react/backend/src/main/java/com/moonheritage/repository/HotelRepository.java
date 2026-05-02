package com.moonheritage.repository;

import com.moonheritage.model.Hotel;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.mongodb.repository.MongoRepository;
import org.springframework.data.mongodb.repository.Query;

import java.math.BigDecimal;
import java.util.List;
import java.util.Optional;

public interface HotelRepository extends MongoRepository<Hotel, String> {

    Optional<Hotel> findBySlug(String slug);

    List<Hotel> findByFeaturedTrueAndStatusOrderByViewCountDesc(Hotel.HotelStatus status);

    // MongoDB query using field names (case-insensitive regex via Spring Data)
    Page<Hotel> findByStatusAndCityContainingIgnoreCaseAndCategoryAndPricePerNightBetween(
        Hotel.HotelStatus status,
        String city,
        Hotel.Category category,
        BigDecimal minPrice,
        BigDecimal maxPrice,
        Pageable pageable
    );

    Page<Hotel> findByStatus(Hotel.HotelStatus status, Pageable pageable);

    @Query("{ 'status': 'active', $or: [ " +
           "{ 'name': { $regex: ?0, $options: 'i' } }, " +
           "{ 'city': { $regex: ?0, $options: 'i' } }, " +
           "{ 'country': { $regex: ?0, $options: 'i' } } ] }")
    Page<Hotel> searchByKeyword(String keyword, Pageable pageable);

    @Query("{ 'status': 'active', 'city': { $regex: ?0, $options: 'i' }, " +
           "'pricePerNight': { $gte: ?1, $lte: ?2 } }")
    Page<Hotel> searchByCityAndPrice(String city, BigDecimal minPrice, BigDecimal maxPrice, Pageable pageable);
}
