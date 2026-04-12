package com.moonheritage.model;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import jakarta.persistence.*;
import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;
import java.time.LocalDateTime;
import java.util.List;

@Data
@NoArgsConstructor
@AllArgsConstructor
@Entity
@Table(name = "amenities")
public class Amenity {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false, length = 100)
    private String name;

    @Column(length = 50)
    private String icon;

    @Column(length = 50)
    private String category;

    @Column(name = "created_at", updatable = false)
    private LocalDateTime createdAt;

    @ManyToMany(mappedBy = "amenities", fetch = FetchType.LAZY)
    @JsonIgnoreProperties({"rooms", "images", "amenities", "handler", "hibernateLazyInitializer"})
    private List<Hotel> hotels;

    @PrePersist
    protected void onCreate() {
        createdAt = LocalDateTime.now();
    }
}
