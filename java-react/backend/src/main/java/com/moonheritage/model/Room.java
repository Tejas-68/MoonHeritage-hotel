package com.moonheritage.model;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;

import java.math.BigDecimal;

/**
 * Embedded subdocument inside Hotel – not a top-level collection.
 */
@Data
@NoArgsConstructor
@AllArgsConstructor
public class Room {

    private String id;
    private String roomType;
    private String description;
    private BigDecimal pricePerNight;
    private Integer maxOccupancy = 2;
    private BigDecimal sizeSqm;
    private String bedType;
    private Integer totalRooms = 1;
    private Integer availableRooms = 1;
}
