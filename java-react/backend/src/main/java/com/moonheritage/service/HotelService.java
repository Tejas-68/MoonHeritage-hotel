package com.moonheritage.service;

import com.moonheritage.model.Hotel;
import com.moonheritage.repository.HotelRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.*;
import org.springframework.stereotype.Service;

import java.math.BigDecimal;
import java.util.List;

@Service
@RequiredArgsConstructor
public class HotelService {

    private final HotelRepository hotelRepository;

    public Page<Hotel> searchHotels(String search, String city, String category,
                                    BigDecimal minPrice, BigDecimal maxPrice,
                                    int page, int size, String sortBy) {

        Hotel.Category cat = null;
        if (category != null && !category.isBlank()) {
            try { cat = Hotel.Category.valueOf(category.toLowerCase()); }
            catch (IllegalArgumentException ignored) {}
        }

        Sort sort = switch (sortBy != null ? sortBy : "featured") {
            case "price_asc"   -> Sort.by("pricePerNight").ascending();
            case "price_desc"  -> Sort.by("pricePerNight").descending();
            case "rating"      -> Sort.by("starRating").descending();
            case "name"        -> Sort.by("name").ascending();
            default            -> Sort.by("featured").descending().and(Sort.by("viewCount").descending());
        };

        Pageable pageable = PageRequest.of(page, size, sort);
        return hotelRepository.searchHotels(
            city != null && !city.isBlank() ? city : null,
            cat,
            minPrice,
            maxPrice,
            search != null && !search.isBlank() ? search : null,
            pageable
        );
    }

    public Hotel getHotelById(Long id) {
        Hotel hotel = hotelRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Hotel not found"));
        hotel.setViewCount(hotel.getViewCount() + 1);
        hotelRepository.save(hotel);
        return hotel;
    }

    public Hotel getHotelBySlug(String slug) {
        Hotel hotel = hotelRepository.findBySlug(slug)
                .orElseThrow(() -> new RuntimeException("Hotel not found"));
        hotel.setViewCount(hotel.getViewCount() + 1);
        hotelRepository.save(hotel);
        return hotel;
    }

    public List<Hotel> getFeaturedHotels() {
        return hotelRepository.findByFeaturedTrueAndStatusOrderByViewCountDesc(Hotel.HotelStatus.active);
    }
}
