package com.moonheritage.controller;

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
    public ResponseEntity<?> getProfile(@AuthenticationPrincipal UserDetails userDetails) {
        User user = userRepository.findByEmail(userDetails.getUsername())
                .orElseThrow(() -> new RuntimeException("User not found"));
        // Return without password
        user.setPassword(null);
        return ResponseEntity.ok(user);
    }

    @PutMapping("/profile")
    public ResponseEntity<?> updateProfile(
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
        user.setPassword(null);
        return ResponseEntity.ok(user);
    }
}
