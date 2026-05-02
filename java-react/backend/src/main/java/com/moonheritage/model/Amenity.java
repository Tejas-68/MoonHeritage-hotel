package com.moonheritage.model;

import lombok.Data;
import lombok.NoArgsConstructor;
import lombok.AllArgsConstructor;

/**
 * Embedded subdocument inside Hotel.
 */
@Data
@NoArgsConstructor
@AllArgsConstructor
public class Amenity {

    private String id;
    private String name;
    private String icon;
    private String category;
}
