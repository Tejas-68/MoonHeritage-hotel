package com.moonheritage.service;

import com.moonheritage.model.Hotel;
import com.moonheritage.repository.HotelRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.*;
import org.springframework.data.mongodb.core.MongoTemplate;
import org.springframework.data.mongodb.core.query.Criteria;
import org.springframework.data.mongodb.core.query.Query;
import org.springframework.stereotype.Service;

import java.math.BigDecimal;
import java.util.ArrayList;
import java.util.List;

@Service
@RequiredArgsConstructor
public class HotelService {

    private final HotelRepository hotelRepository;
    private final MongoTemplate mongoTemplate;

    public Page<Hotel> searchHotels(String search, String city, String category,
                                    BigDecimal minPrice, BigDecimal maxPrice,
                                    int page, int size, String sortBy) {

        Sort sort = switch (sortBy != null ? sortBy : "featured") {
            case "price_asc"  -> Sort.by("pricePerNight").ascending();
            case "price_desc" -> Sort.by("pricePerNight").descending();
            case "rating"     -> Sort.by("starRating").descending();
            case "name"       -> Sort.by("name").ascending();
            default           -> Sort.by("featured").descending().and(Sort.by("viewCount").descending());
        };

        Pageable pageable = PageRequest.of(page, size, sort);

        // Build a flexible Criteria query
        List<Criteria> criteriaList = new ArrayList<>();
        criteriaList.add(Criteria.where("status").is(Hotel.HotelStatus.active));

        if (city != null && !city.isBlank()) {
            criteriaList.add(Criteria.where("city").regex(city, "i"));
        }
        if (category != null && !category.isBlank()) {
            try {
                criteriaList.add(Criteria.where("category").is(Hotel.Category.valueOf(category.toLowerCase())));
            } catch (IllegalArgumentException ignored) {}
        }
        if (minPrice != null) {
            criteriaList.add(Criteria.where("pricePerNight").gte(minPrice));
        }
        if (maxPrice != null) {
            criteriaList.add(Criteria.where("pricePerNight").lte(maxPrice));
        }
        if (search != null && !search.isBlank()) {
            criteriaList.add(new Criteria().orOperator(
                Criteria.where("name").regex(search, "i"),
                Criteria.where("city").regex(search, "i"),
                Criteria.where("country").regex(search, "i")
            ));
        }

        Criteria combined = new Criteria().andOperator(criteriaList.toArray(new Criteria[0]));
        Query query = new Query(combined).with(pageable);
        long total = mongoTemplate.count(new Query(combined), Hotel.class);
        List<Hotel> hotels = mongoTemplate.find(query, Hotel.class);

        return new PageImpl<>(hotels, pageable, total);
    }

    public Hotel getHotelById(String id) {
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
