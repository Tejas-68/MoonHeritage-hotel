package com.moonheritage.repository;

import com.moonheritage.model.Review;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.mongodb.repository.Aggregation;
import org.springframework.data.mongodb.repository.MongoRepository;

import java.util.Optional;

public interface ReviewRepository extends MongoRepository<Review, String> {

    Page<Review> findByHotelIdAndStatus(String hotelId, Review.ReviewStatus status, Pageable pageable);

    long countByHotelIdAndStatus(String hotelId, Review.ReviewStatus status);

    @Aggregation(pipeline = {
        "{ $match: { hotelId: ?0, status: 'approved' } }",
        "{ $group: { _id: null, avg: { $avg: '$rating' } } }"
    })
    Optional<Double> getAverageRatingForHotel(String hotelId);
}
