package com.moonheritage.dto;

import com.moonheritage.model.User;

public record UserDto(
    Long id,
    String username,
    String email,
    String firstName,
    String lastName,
    String phone,
    String address,
    String city,
    String country,
    String role,
    String status
) {
    public static UserDto fromEntity(User user) {
        return new UserDto(
            user.getId(),
            user.getUsername(),
            user.getEmail(),
            user.getFirstName(),
            user.getLastName(),
            user.getPhone(),
            user.getAddress(),
            user.getCity(),
            user.getCountry(),
            user.getRole() != null ? user.getRole().name() : null,
            user.getStatus() != null ? user.getStatus().name() : null
        );
    }
}
