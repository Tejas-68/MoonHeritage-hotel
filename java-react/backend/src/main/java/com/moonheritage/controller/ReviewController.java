package com.moonheritage.controller;

import com.moonheritage.dto.ReviewRequest;
import com.moonheritage.model.*;
import com.moonheritage.repository.*;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.*;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.web.bind.annotation.*;

import java.util.Map;

@RestController
@RequestMapping("/reviews")
@RequiredArgsConstructor
public class ReviewController {

    private final ReviewRepository reviewRepository;
    private final UserRepository userRepository;
    private final HotelRepository hotelRepository;
    private final BookingRepository bookingRepository;

    // MongoDB uses String ObjectId path variables
    @GetMapping("/hotel/{hotelId}")
    public ResponseEntity<Page<Review>> getHotelReviews(
            @PathVariable String hotelId,
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "10") int size
    ) {
        Pageable pageable = PageRequest.of(page, size, Sort.by("createdAt").descending());
        return ResponseEntity.ok(reviewRepository.findByHotelIdAndStatus(
            hotelId, Review.ReviewStatus.approved, pageable));
    }

    @PostMapping
    public ResponseEntity<?> submitReview(
            @Valid @RequestBody ReviewRequest request,
            @AuthenticationPrincipal UserDetails userDetails
    ) {
        try {
            User user = userRepository.findByEmail(userDetails.getUsername())
                    .orElseThrow(() -> new RuntimeException("User not found"));
            Hotel hotel = hotelRepository.findById(request.getHotelId())
                    .orElseThrow(() -> new RuntimeException("Hotel not found"));

            Review review = new Review();
            review.setUserId(user.getId());
            review.setHotelId(hotel.getId());
            // Denormalize user display info
            review.setUserFirstName(user.getFirstName());
            review.setUserLastName(user.getLastName());
            review.setUserProfileImage(user.getProfileImage());

            review.setRating(request.getRating());
            review.setTitle(request.getTitle());
            review.setComment(request.getComment());
            review.setCleanlinessRating(request.getCleanlinessRating());
            review.setServiceRating(request.getServiceRating());
            review.setLocationRating(request.getLocationRating());
            review.setValueRating(request.getValueRating());
            review.setStatus(Review.ReviewStatus.pending);

            if (request.getBookingId() != null) {
                bookingRepository.findById(request.getBookingId()).ifPresent(b -> {
                    if (b.getUserId().equals(user.getId()) && b.getHotelId().equals(hotel.getId())) {
                        review.setBookingId(b.getId());
                        review.setVerifiedBooking(true);
                    }
                });
            }

            reviewRepository.save(review);
            return ResponseEntity.ok(Map.of("message", "Review submitted for approval"));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }
}
