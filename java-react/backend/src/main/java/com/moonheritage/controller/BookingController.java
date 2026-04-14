package com.moonheritage.controller;

import com.moonheritage.dto.BookingRequest;
import com.moonheritage.model.Booking;
import com.moonheritage.service.BookingService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/bookings")
@RequiredArgsConstructor
public class BookingController {

    private final BookingService bookingService;

    @PostMapping
    public ResponseEntity<?> createBooking(
            @Valid @RequestBody BookingRequest request,
            @AuthenticationPrincipal UserDetails userDetails
    ) {
        try {
            Booking booking = bookingService.createBooking(request, userDetails.getUsername());
            return ResponseEntity.ok(com.moonheritage.dto.BookingDto.fromEntity(booking));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }

    @GetMapping("/my")
    public ResponseEntity<List<com.moonheritage.dto.BookingDto>> getMyBookings(
            @AuthenticationPrincipal UserDetails userDetails
    ) {
        List<com.moonheritage.dto.BookingDto> bookings = bookingService.getUserBookings(userDetails.getUsername())
                .stream()
                .map(com.moonheritage.dto.BookingDto::fromEntity)
                .toList();
        return ResponseEntity.ok(bookings);
    }

    @PutMapping("/{id}/cancel")
    public ResponseEntity<?> cancelBooking(
            @PathVariable Long id,
            @RequestBody Map<String, String> body,
            @AuthenticationPrincipal UserDetails userDetails
    ) {
        try {
            Booking booking = bookingService.cancelBooking(id, userDetails.getUsername(),
                    body.getOrDefault("reason", "Cancelled by user"));
            return ResponseEntity.ok(com.moonheritage.dto.BookingDto.fromEntity(booking));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }
}
