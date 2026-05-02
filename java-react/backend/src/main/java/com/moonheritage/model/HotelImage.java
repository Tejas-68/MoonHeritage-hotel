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
public class HotelImage {

    private String id;
    private String imagePath;
    private String caption;
    private Boolean isPrimary = false;
    private Integer displayOrder = 0;
}
