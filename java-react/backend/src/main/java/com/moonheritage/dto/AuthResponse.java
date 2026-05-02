package com.moonheritage.dto;

import com.fasterxml.jackson.annotation.JsonIgnore;
import lombok.AllArgsConstructor;
import lombok.Data;

@Data
@AllArgsConstructor
public class AuthResponse {
    @JsonIgnore
    private String token;
    private String email;
    private String firstName;
    private String lastName;
    private String role;
    private String userId;  // Changed Long → String for MongoDB
}
