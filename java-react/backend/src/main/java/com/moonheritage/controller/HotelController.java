package com.moonheritage.controller;

import com.moonheritage.model.Hotel;
import com.moonheritage.service.HotelService;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.math.BigDecimal;
import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/hotels")
@RequiredArgsConstructor
public class HotelController {

    private final HotelService hotelService;

    @GetMapping
    public ResponseEntity<Map<String, Object>> getHotels(
            @RequestParam(required = false) String search,
            @RequestParam(required = false) String city,
            @RequestParam(required = false) String category,
            @RequestParam(required = false) BigDecimal minPrice,
            @RequestParam(required = false) BigDecimal maxPrice,
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "9") int size,
            @RequestParam(defaultValue = "featured") String sort
    ) {
        Page<Hotel> result = hotelService.searchHotels(
            search, city, category, minPrice, maxPrice, page, size, sort
        );
        return ResponseEntity.ok(Map.of(
            "hotels",      result.getContent(),
            "totalPages",  result.getTotalPages(),
            "totalItems",  result.getTotalElements(),
            "currentPage", result.getNumber()
        ));
    }

    @GetMapping("/featured")
    public ResponseEntity<List<Hotel>> getFeatured() {
        return ResponseEntity.ok(hotelService.getFeaturedHotels());
    }

    @GetMapping("/{id}")
    public ResponseEntity<Hotel> getById(@PathVariable Long id) {
        return ResponseEntity.ok(hotelService.getHotelById(id));
    }

    @GetMapping("/slug/{slug}")
    public ResponseEntity<Hotel> getBySlug(@PathVariable String slug) {
        return ResponseEntity.ok(hotelService.getHotelBySlug(slug));
    }
}
