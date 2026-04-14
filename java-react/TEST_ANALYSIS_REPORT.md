# MoonHeritage Code Analysis & Testing Report

As requested, I performed a static code analysis and logic review on the newly migrated Java Spring Boot backend and React frontend. Everything is running, but the code reveals several critical vulnerabilities, business logic oversights, and areas for improvement before this can be considered production-ready.

---

## 1. Critical Business Logic Vulnerabilities

**Infinite Hotel Overbooking (`BookingService.java`)**
*   **The Flaw**: The `createBooking` method calculates subtotal and tax, but it **never checks `hotel.getAvailableRooms()`**. Additionally, it never decrements the available room count upon successful booking. 
*   **Impact**: A user can book 1,000 rooms when only 10 are available, completely breaking inventory management.
*   **Fix Required**: Add validation `if (request.getRoomsCount() > hotel.getAvailableRooms()) throw Exception` and decrement the inventory: `hotel.setAvailableRooms(hotel.getAvailableRooms() - request.getRoomsCount())`.

**Time Traveling Bookings (`BookingService.java`)**
*   **The Flaw**: The code verifies that `checkOutDate` is after `checkInDate`, but it does not verify if the `checkInDate` is `>= LocalDate.now()`. 
*   **Impact**: Users can create bookings for past dates.

**Review Verification Spoofing (`ReviewController.java`)**
*   **The Flaw**: In `submitReview`, if a user provides an arbitrary `bookingId`, the system sets `review.setVerifiedBooking(true)`. 
*   **Impact**: The logic completely fails to check if that `bookingId` belongs to the requesting user or if that booking was even for the requested hotel. Any user can inspect network traffic, pass a random integer as `bookingId`, and gain a "Verified Stay" badge.
*   **Fix Required**: Validate that the fetched booking's `user.getId()` matches the authenticated user and `hotel.getId()` matches the submitted hotel.

---

## 2. Security & Data Privacy

**Token Storage Vulnerability (`AuthContext.jsx`)**
*   **The Flaw**: The frontend utilizes `localStorage.setItem('mh_token', authData.token)`. 
*   **Impact**: Putting JWTs in `localStorage` makes the application highly vulnerable to Cross-Site Scripting (XSS). If any malicious script executes on the frontend, it can instantly steal the user's session token.
*   **Fix Required**: Migrate to `httpOnly` secure cookies set directly by the Spring Boot backend during the `/api/login` response.

**Permissive CORS Policy (`CorsConfig.java`)**
*   **The Flaw**: Although origins are specified, the configuration allows all headers and all HTTP methods (`*`) globally.
*   **Impact**: Could expose the API to CSRF or pre-flight hijacking in combination with other vulnerabilities.

**Lack of Data Transfer Objects (DTOs) on Output**
*   **The Flaw**: Controllers like `HotelController` and `BookingController` are returning direct JPA Entities (`Hotel`, `Booking`) rather than specific Response DTOs.
*   **Impact**: Although we patched infinite recursion loops using `@JsonIgnoreProperties`, returning raw entities is an anti-pattern. It accidentally exposes internal database designs and risks leaking sensitive data if someone forgets a `@JsonIgnore` flag on new fields in the future.

---

## 3. Error Handling and Resilience

**Global Exception Leaks (Backend)**
*   **The Flaw**: The application misses a `@ControllerAdvice` global exception handler. Current errors are caught in scattered `try/catch` blocks inside controllers.
*   **Impact**: If a database error occurs or Jackson fails to parse JSON, Spring Boot might fall back to its "Whitelabel Error Page" or return a raw `500 Internal Server Error` containing stack traces and internal class names natively to the frontend.

**Silent Frontend Failures (`Home.jsx`, `Hotels.jsx`)**
*   **The Flaw**: The frontend uses `.catch(console.error)` for handling API rejections.
*   **Impact**: If the backend is down or returns a `400 Bad Request`, the user sees an infinite loading spinner or a blank section with no user-centric error message like "Failed to load hotels. Please try again."

---

## Summary

The API infrastructure, JWT integration, and JPA entity mapping are solidly implemented, fulfilling the migration goals seamlessly. However, the exact **hotel capacity mechanics** and **review validations** have dangerous logic gaps. 

I strongly recommend doing a secondary pass focusing on adding stricter parameter validations and adopting a DTO-out pattern before exposing this to a public network.
