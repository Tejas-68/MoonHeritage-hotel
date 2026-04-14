package com.moonheritage.dto;

import lombok.AllArgsConstructor;
import lombok.Data;

@Data
@AllArgsConstructor
public class AuthResponse {
    @com.fasterxml.jackson.annotation.JsonIgnore
    private String token;
    private String email;
    private String firstName;
    private String lastName;
    private String role;
    private Long userId;
}
