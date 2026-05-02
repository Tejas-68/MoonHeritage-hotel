package com.moonheritage.controller;

import com.moonheritage.dto.HotelRequest;
import com.moonheritage.model.*;
import com.moonheritage.repository.*;
import lombok.RequiredArgsConstructor;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.data.domain.*;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import jakarta.validation.Valid;
import java.io.File;
import java.io.IOException;
import java.math.BigDecimal;
import java.nio.file.*;
import java.util.*;

@RestController
@RequestMapping("/admin")
@RequiredArgsConstructor
public class AdminController {

    private final HotelRepository hotelRepository;
    private final BookingRepository bookingRepository;
    private final UserRepository userRepository;
    private final ReviewRepository reviewRepository;

    @Value("${app.upload.dir:./uploads/}")
    private String uploadDir;

    // ── Dashboard Stats ──────────────────────────────────────────────

    @GetMapping("/stats")
    public ResponseEntity<Map<String, Object>> getStats() {
        Map<String, Object> stats = new LinkedHashMap<>();
        stats.put("totalHotels",   hotelRepository.count());
        stats.put("totalBookings", bookingRepository.count());
        stats.put("totalUsers",    userRepository.count());
        stats.put("totalReviews",  reviewRepository.count());

        // Revenue total
        List<Booking> allBookings = bookingRepository.findAll();
        BigDecimal revenue = allBookings.stream()
            .filter(b -> b.getBookingStatus() != Booking.BookingStatus.cancelled)
            .map(Booking::getTotalAmount)
            .filter(Objects::nonNull)
            .reduce(BigDecimal.ZERO, BigDecimal::add);
        stats.put("totalRevenue", revenue);

        // Recent bookings (last 5)
        Pageable recent = PageRequest.of(0, 5, Sort.by("createdAt").descending());
        stats.put("recentBookings", bookingRepository.findAll(recent).getContent());

        return ResponseEntity.ok(stats);
    }

    // ── Hotel CRUD ───────────────────────────────────────────────────

    @GetMapping("/hotels")
    public ResponseEntity<?> getAllHotels(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size
    ) {
        Pageable pageable = PageRequest.of(page, size, Sort.by("createdAt").descending());
        Page<Hotel> result = hotelRepository.findAll(pageable);
        return ResponseEntity.ok(Map.of(
            "hotels",      result.getContent(),
            "totalPages",  result.getTotalPages(),
            "totalItems",  result.getTotalElements(),
            "currentPage", result.getNumber()
        ));
    }

    @PostMapping("/hotels")
    public ResponseEntity<?> createHotel(@Valid @RequestBody HotelRequest req) {
        Hotel hotel = mapRequestToHotel(new Hotel(), req);
        hotel.setSlug(generateSlug(req.getName()));
        hotel.setViewCount(0);
        hotelRepository.save(hotel);
        return ResponseEntity.ok(hotel);
    }

    // MongoDB uses String ObjectId, not Long
    @PutMapping("/hotels/{id}")
    public ResponseEntity<?> updateHotel(@PathVariable String id, @Valid @RequestBody HotelRequest req) {
        Hotel hotel = hotelRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Hotel not found"));
        mapRequestToHotel(hotel, req);
        hotelRepository.save(hotel);
        return ResponseEntity.ok(hotel);
    }

    @DeleteMapping("/hotels/{id}")
    public ResponseEntity<?> deleteHotel(@PathVariable String id) {
        hotelRepository.deleteById(id);
        return ResponseEntity.ok(Map.of("message", "Hotel deleted"));
    }

    // ── Image Upload ─────────────────────────────────────────────────

    @PostMapping("/hotels/{id}/image")
    public ResponseEntity<?> uploadMainImage(
            @PathVariable String id,
            @RequestParam("file") MultipartFile file
    ) {
        Hotel hotel = hotelRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Hotel not found"));
        try {
            String filename = saveFile(file, hotel.getSlug() + "-main");
            hotel.setMainImage(filename);
            hotelRepository.save(hotel);
            return ResponseEntity.ok(Map.of("filename", filename));
        } catch (IOException e) {
            return ResponseEntity.badRequest().body(Map.of("error", "Image upload failed: " + e.getMessage()));
        }
    }

    @PostMapping("/hotels/{id}/images")
    public ResponseEntity<?> uploadGalleryImage(
            @PathVariable String id,
            @RequestParam("file") MultipartFile file,
            @RequestParam(defaultValue = "false") boolean isPrimary
    ) {
        Hotel hotel = hotelRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Hotel not found"));
        try {
            String filename = saveFile(file, hotel.getSlug() + "-" + System.currentTimeMillis());
            HotelImage image = new HotelImage();
            image.setId(UUID.randomUUID().toString());
            image.setImagePath(filename);
            image.setIsPrimary(isPrimary);
            image.setDisplayOrder(hotel.getImages() != null ? hotel.getImages().size() : 0);

            if (hotel.getImages() == null) hotel.setImages(new ArrayList<>());
            hotel.getImages().add(image);

            if (isPrimary || hotel.getMainImage() == null) {
                hotel.setMainImage(filename);
            }
            hotelRepository.save(hotel);
            return ResponseEntity.ok(Map.of("filename", filename));
        } catch (IOException e) {
            return ResponseEntity.badRequest().body(Map.of("error", "Image upload failed: " + e.getMessage()));
        }
    }

    // ── Review Moderation ────────────────────────────────────────────

    @PutMapping("/reviews/{id}/approve")
    public ResponseEntity<?> approveReview(@PathVariable String id) {
        Review review = reviewRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Review not found"));
        review.setStatus(Review.ReviewStatus.approved);
        return ResponseEntity.ok(reviewRepository.save(review));
    }

    @PutMapping("/reviews/{id}/reject")
    public ResponseEntity<?> rejectReview(@PathVariable String id) {
        Review review = reviewRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Review not found"));
        review.setStatus(Review.ReviewStatus.rejected);
        return ResponseEntity.ok(reviewRepository.save(review));
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private Hotel mapRequestToHotel(Hotel hotel, HotelRequest req) {
        hotel.setName(req.getName());
        hotel.setAddress(req.getAddress());
        hotel.setCity(req.getCity());
        hotel.setState(req.getState());
        hotel.setCountry(req.getCountry());
        hotel.setPostalCode(req.getPostalCode());
        hotel.setDescription(req.getDescription());
        hotel.setShortDescription(req.getShortDescription());
        hotel.setPhone(req.getPhone());
        hotel.setEmail(req.getEmail());
        hotel.setWebsite(req.getWebsite());
        hotel.setStarRating(req.getStarRating() != null ? req.getStarRating() : BigDecimal.ZERO);
        hotel.setPricePerNight(req.getPricePerNight());
        hotel.setOriginalPrice(req.getOriginalPrice());
        hotel.setDiscountPercentage(req.getDiscountPercentage() != null ? req.getDiscountPercentage() : 0);
        hotel.setFeatured(req.getFeatured() != null ? req.getFeatured() : false);
        hotel.setTotalRooms(req.getTotalRooms() != null ? req.getTotalRooms() : 0);
        hotel.setAvailableRooms(req.getAvailableRooms() != null ? req.getAvailableRooms() : 0);

        if (req.getCategory() != null) {
            try { hotel.setCategory(Hotel.Category.valueOf(req.getCategory().toLowerCase())); }
            catch (IllegalArgumentException ignored) { hotel.setCategory(Hotel.Category.hotel); }
        }
        if (req.getStatus() != null) {
            try { hotel.setStatus(Hotel.HotelStatus.valueOf(req.getStatus().toLowerCase())); }
            catch (IllegalArgumentException ignored) { hotel.setStatus(Hotel.HotelStatus.active); }
        }
        return hotel;
    }

    private String saveFile(MultipartFile file, String baseName) throws IOException {
        String originalFilename = file.getOriginalFilename();
        String ext = (originalFilename != null && originalFilename.contains("."))
                ? originalFilename.substring(originalFilename.lastIndexOf('.'))
                : ".jpg";
        String filename = baseName.replaceAll("[^a-zA-Z0-9-]", "-") + ext;

        Path dir = Paths.get(uploadDir);
        Files.createDirectories(dir);
        Path target = dir.resolve(filename);
        Files.copy(file.getInputStream(), target, StandardCopyOption.REPLACE_EXISTING);
        return filename;
    }

    private String generateSlug(String name) {
        return name.toLowerCase()
                .replaceAll("[^a-z0-9\\s-]", "")
                .replaceAll("\\s+", "-")
                .replaceAll("-+", "-")
                .replaceAll("^-|-$", "")
                + "-" + System.currentTimeMillis();
    }
}
