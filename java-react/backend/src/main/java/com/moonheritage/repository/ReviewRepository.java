package com.moonheritage.repository;

import com.moonheritage.model.Review;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

public interface ReviewRepository extends JpaRepository<Review, Long> {

    Page<Review> findByHotelIdAndStatus(Long hotelId, Review.ReviewStatus status, Pageable pageable);

    @Query("SELECT AVG(r.rating) FROM Review r WHERE r.hotel.id = :hotelId AND r.status = 'approved'")
    Double getAverageRatingForHotel(@Param("hotelId") Long hotelId);

    long countByHotelIdAndStatus(Long hotelId, Review.ReviewStatus status);
}
