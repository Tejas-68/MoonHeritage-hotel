package com.moonheritage.controller;

import com.moonheritage.dto.UserDto;
import com.moonheritage.model.User;
import com.moonheritage.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.annotation.AuthenticationPrincipal;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.web.bind.annotation.*;

import java.util.Map;

@RestController
@RequestMapping("/users")
@RequiredArgsConstructor
public class UserController {

    private final UserRepository userRepository;

    @GetMapping("/profile")
    public ResponseEntity<UserDto> getProfile(@AuthenticationPrincipal UserDetails userDetails) {
        User user = userRepository.findByEmail(userDetails.getUsername())
                .orElseThrow(() -> new RuntimeException("User not found"));
        return ResponseEntity.ok(UserDto.fromEntity(user));
    }

    @PutMapping("/profile")
    public ResponseEntity<UserDto> updateProfile(
            @RequestBody Map<String, String> updates,
            @AuthenticationPrincipal UserDetails userDetails
    ) {
        User user = userRepository.findByEmail(userDetails.getUsername())
                .orElseThrow(() -> new RuntimeException("User not found"));

        if (updates.containsKey("firstName")) user.setFirstName(updates.get("firstName"));
        if (updates.containsKey("lastName"))  user.setLastName(updates.get("lastName"));
        if (updates.containsKey("phone"))     user.setPhone(updates.get("phone"));
        if (updates.containsKey("address"))   user.setAddress(updates.get("address"));
        if (updates.containsKey("city"))      user.setCity(updates.get("city"));
        if (updates.containsKey("country"))   user.setCountry(updates.get("country"));

        userRepository.save(user);
        return ResponseEntity.ok(UserDto.fromEntity(user));
    }
}
