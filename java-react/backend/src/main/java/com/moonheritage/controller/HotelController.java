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
        List<com.moonheritage.dto.HotelDto> dtos = result.getContent().stream()
            .map(com.moonheritage.dto.HotelDto::fromEntity)
            .toList();
        return ResponseEntity.ok(Map.of(
            "hotels",      dtos,
            "totalPages",  result.getTotalPages(),
            "totalItems",  result.getTotalElements(),
            "currentPage", result.getNumber()
        ));
    }

    @GetMapping("/featured")
    public ResponseEntity<List<com.moonheritage.dto.HotelDto>> getFeatured() {
        return ResponseEntity.ok(hotelService.getFeaturedHotels()
            .stream().map(com.moonheritage.dto.HotelDto::fromEntity).toList());
    }

    // MongoDB uses String ObjectId, not Long
    @GetMapping("/{id}")
    public ResponseEntity<Hotel> getById(@PathVariable String id) {
        return ResponseEntity.ok(hotelService.getHotelById(id));
    }

    @GetMapping("/slug/{slug}")
    public ResponseEntity<Hotel> getBySlug(@PathVariable String slug) {
        return ResponseEntity.ok(hotelService.getHotelBySlug(slug));
    }
}
