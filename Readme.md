# MoonHeritage — Luxury Hotel Booking Platform

A full-stack hotel booking web application built with **Java Spring Boot 3** REST API and **React 18 + Vite** SPA, styled with **Tailwind CSS v3**. Migrated from a legacy PHP/MySQL monolith with zero data loss.

![Java](https://img.shields.io/badge/Java-25-ED8B00?style=for-the-badge&logo=openjdk&logoColor=white)
![Spring Boot](https://img.shields.io/badge/Spring_Boot-3.2.5-6DB33F?style=for-the-badge&logo=spring-boot&logoColor=white)
![React](https://img.shields.io/badge/React-18-61DAFB?style=for-the-badge&logo=react&logoColor=black)
![Vite](https://img.shields.io/badge/Vite-5-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JWT](https://img.shields.io/badge/JWT-Auth-000000?style=for-the-badge&logo=jsonwebtokens&logoColor=white)

---

## Features

- **JWT Authentication** — stateless login/signup with BCrypt password hashing and automatic admin redirect
- **Hotel Search and Filter** — paginated results filterable by city, category, price range, and keyword with multi-field sorting
- **Live Booking Engine** — real-time price calculation (subtotal + 10% tax server-side) with date validation
- **User Dashboard** — profile management, full booking history with status tracking, one-click cancellation
- **Reviews System** — rated reviews with verified booking badges and moderation workflow
- **Admin Panel** — full hotel CRUD, image upload to XAMPP images folder, dashboard with revenue and booking stats
- **Real Hotel Images** — images stored as URLs or filenames in the database; served directly, no hardcoded placeholders
- **Responsive UI** — dark luxury design system built entirely with Tailwind CSS utility classes

---

## Tech Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Frontend** | React 18 + Vite 5 | SPA with React Router v6, Axios, AuthContext |
| **Styling** | Tailwind CSS v3 + PostCSS | Utility-first dark luxury design system |
| **Backend** | Java Spring Boot 3.2.5 | REST API with MVC pattern |
| **Auth** | Spring Security 6 + JWT (JJWT 0.12.x) | Stateless authentication |
| **ORM** | Spring Data JPA + Hibernate 6 | Database abstraction layer |
| **Database** | MySQL 8 via XAMPP | Relational data persistence |
| **Build** | Maven 3.9 / npm | Dependency and build management |

---

## Architecture

```
┌─────────────────────────────────────────┐
│         MySQL 8  (port 3306)            │
│      database: moonheritage             │
└──────────────────┬──────────────────────┘
                   │ JDBC / HikariCP
┌──────────────────▼──────────────────────┐
│  Java Spring Boot REST API (port 8081)  │
│  Context path: /api                     │
│  Spring Security (JWT) + JPA/Hibernate  │
│  AdminController + image upload         │
└──────────────────┬──────────────────────┘
                   │ REST / JSON  (Axios)
┌──────────────────▼──────────────────────┐
│  React + Vite SPA  (port 3000)          │
│  Tailwind CSS v3 (via PostCSS)          │
│  Vite proxy: /api → localhost:8081      │
│  React Router + AuthContext             │
└─────────────────────────────────────────┘
```

---

## Project Structure

```
MoonHeritage/
├── database.sql                       ← MySQL schema + full seed data (12 hotels, rooms, reviews)
├── admin-reset-password.html          ← Standalone BCrypt password reset tool (open directly in browser)
├── images/                            ← Hotel images uploaded via admin panel (served by XAMPP Apache)
└── java-react/
    ├── README.md
    ├── backend/                       ← Spring Boot (Maven)
    │   ├── pom.xml
    │   └── src/main/
    │       ├── java/com/moonheritage/
    │       │   ├── config/            ← SecurityConfig, CorsConfig, JacksonConfig
    │       │   ├── controller/        ← AuthController, HotelController, BookingController,
    │       │   │                         ReviewController, UserController, AdminController
    │       │   ├── dto/               ← LoginRequest, SignupRequest, AuthResponse,
    │       │   │                         BookingRequest, ReviewRequest, HotelRequest
    │       │   ├── model/             ← User, Hotel, Room, Booking, Review,
    │       │   │                         Amenity, HotelImage (JPA entities)
    │       │   ├── repository/        ← JpaRepository interfaces with JPQL queries
    │       │   ├── security/          ← JwtUtil, JwtFilter, UserDetailsServiceImpl
    │       │   └── service/           ← AuthService, HotelService, BookingService
    │       └── resources/
    │           └── application.properties
    └── frontend/                      ← React + Vite
        ├── tailwind.config.js         ← Luxury dark theme (gold, slate, luxury palette)
        ├── postcss.config.js          ← Tailwind + Autoprefixer
        ├── vite.config.js             ← Proxy /api → localhost:8081
        └── src/
            ├── index.css              ← Tailwind directives + component layer
            ├── App.jsx                ← Router, ProtectedRoute, AdminRoute
            ├── api/axios.js           ← Axios instance + JWT interceptor
            ├── context/AuthContext.jsx
            ├── utils/imageUtil.js     ← Builds real image URL from DB path/URL
            ├── components/            ← Navbar, Footer, HotelCard
            └── pages/                 ← Home, Hotels, HotelDetail, Booking,
                                          BookingConfirmation, Login, Signup,
                                          Profile, AdminDashboard
```

---

## Getting Started

### Prerequisites

| Tool | Version |
|------|---------|
| Java JDK | 17+ |
| Maven | 3.9+ |
| Node.js | 18+ |
| XAMPP | MySQL 8.0+ |

### 1 — Import the Database

1. Open XAMPP Control Panel and start **MySQL** and **Apache**
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Import the schema: **Import** tab → select `MoonHeritage/database.sql` → **Go**

This creates 12 fully seeded hotel properties with real images, room types, amenities, and reviews.

### 2 — Start the Backend

```bash
cd java-react/backend
export PATH="/opt/homebrew/bin:$PATH"   # macOS Homebrew
mvn spring-boot:run
```

Success indicator:
```
Tomcat started on port 8081 with context path '/api'
Started MoonHeritageApplication in X.XXX seconds
```

### 3 — Start the Frontend

```bash
cd java-react/frontend
export PATH="/opt/homebrew/bin:$PATH"
npm install   # first time only
npm run dev
```

Open in browser: **http://localhost:3000**

---

## Seed Data

The database includes 12 curated properties seeded in `database.sql`:

| Property | Type | Destination | Price/Night |
|----------|------|-------------|-------------|
| Aegean Pearl Hotel | Hotel (5-star) | Mykonos, Greece | $380 |
| Maison Lumiere | Hotel (5-star) | Paris, France | $420 |
| Pura Vida Villa Ubud | Villa | Bali, Indonesia | $275 |
| Turquoise Horizon Resort | Resort (5-star) | Maldives | $850 |
| Caldera Sunset Villas | Villa (5-star) | Santorini, Greece | $620 |
| Villa Toscana Estates | Villa (5-star) | Tuscany, Italy | $520 |
| Skyline Tower Hotel Dubai | Hotel (5-star) | Dubai, UAE | $495 |
| Positano Grand Coastal Hotel | Hotel (5-star) | Amalfi Coast, Italy | $560 |
| Alpine Summit Lodge | Resort | Grindelwald, Switzerland | $480 |
| Backwater Palace Kerala | Resort | Alleppey, India | $310 |
| Cotswolds Stone Cottage Retreat | Cottage | Cotswolds, UK | $240 |
| Manhattan Heights Hotel | Hotel (4.5-star) | New York City, USA | $345 |

Each property includes: 2 room types, amenity mappings, gallery images, and guest reviews. All images are Unsplash URLs stored directly in the database.

---

## REST API Reference

Base URL: `http://localhost:8081/api`

### Auth
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/auth/login` | No | Login — returns JWT token |
| `POST` | `/auth/signup` | No | Register new user |

### Hotels
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/hotels` | No | Search, filter, and paginate hotels |
| `GET` | `/hotels/featured` | No | Featured hotel list |
| `GET` | `/hotels/{id}` | No | Hotel detail with rooms, amenities, images |
| `GET` | `/hotels/slug/{slug}` | No | Hotel by URL slug |

### Bookings
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/bookings` | Yes | Create booking with server-side price calculation |
| `GET` | `/bookings/my` | Yes | Current user's bookings |
| `PUT` | `/bookings/{id}/cancel` | Yes | Cancel with reason |

### Users and Reviews
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/users/profile` | Yes | Get logged-in user profile |
| `PUT` | `/users/profile` | Yes | Update profile fields |
| `POST` | `/reviews` | Yes | Submit hotel review |
| `GET` | `/reviews/hotel/{id}` | No | Get approved reviews for a hotel |

### Admin (ROLE_ADMIN only)
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/admin/stats` | Admin | Dashboard stats: revenue, bookings, users |
| `GET` | `/admin/hotels` | Admin | All hotels with pagination |
| `POST` | `/admin/hotels` | Admin | Create new hotel |
| `PUT` | `/admin/hotels/{id}` | Admin | Update hotel details |
| `DELETE` | `/admin/hotels/{id}` | Admin | Delete hotel |
| `POST` | `/admin/hotels/{id}/image` | Admin | Upload main hotel image |
| `POST` | `/admin/hotels/{id}/images` | Admin | Upload gallery image |

> Authorization header format: `Authorization: Bearer <token>`

---

## Image Handling

Hotel images are stored in the database as either:

- **Full URLs** — e.g. `https://images.unsplash.com/photo-...` (default for seeded data)
- **Filenames** — e.g. `hotel-name-main.jpg` (uploaded via admin panel, served by XAMPP Apache at `http://localhost/MoonHeritage/images/`)

The `imageUtil.js` helper resolves both:

```js
export function hotelImg(path) {
  if (!path) return FALLBACK_URL
  if (path.startsWith('http')) return path
  return `http://localhost/MoonHeritage/images/${path}`
}
```

Uploaded images are saved to `/Applications/XAMPP/xamppfiles/htdocs/MoonHeritage/images/` (configured in `application.properties` as `app.upload.dir`).

---

## Security Design

- **Stateless JWT** — no server-side sessions; token stored in `localStorage` under key `mh_token`
- **BCrypt strength 12** — compatible with PHP's `PASSWORD_BCRYPT` (`$2y$` prefix maps to `$2a$`)
- **Per-request filter** — `JwtFilter extends OncePerRequestFilter` validates every protected request
- **Role-based access** — `ROLE_USER`, `ROLE_ADMIN`, `ROLE_HOTEL_OWNER` enforced via URL pattern matching in `SecurityConfig`
- **Admin routes** — `/admin/**` requires `ROLE_ADMIN`; frontend also enforces via `AdminRoute` component
- **CORS** — `CorsFilter` bean with `@Order(HIGHEST_PRECEDENCE)` ensures preflight requests clear before Spring Security

---

## Admin Password Reset

A standalone utility `admin-reset-password.html` is included at the project root.

**Location:** `http://localhost/MoonHeritage/admin-reset-password.html`

**How it works:**
1. Enter and confirm the new password — the page generates a BCrypt hash entirely in the browser (no server contact)
2. The tool outputs a ready-to-run SQL UPDATE statement
3. Paste it into phpMyAdmin `moonheritage` → SQL tab → Go

This file is independent of the web application and does not require the backend to be running.

Default credentials seeded by `database.sql`:

| Field | Value |
|-------|-------|
| Email | `admin@moonheritage.com` |
| Password | `admin123` |
| Role | `admin` |

---

## Key Engineering Decisions

| Decision | Rationale |
|----------|-----------|
| Migrated PHP to Java, kept MySQL | Zero data migration; existing hotel records immediately available |
| Tailwind CSS v3 via PostCSS | No `@tailwindcss/vite` plugin needed; works cleanly with Vite through `postcss.config.js` |
| Unsplash URLs stored in DB | No local file dependency for seed data; admin can replace with uploaded files anytime |
| Vite proxy `/api` to port 8081 | Avoids CORS in development; mirrors production reverse-proxy setup |
| `@JsonIgnoreProperties` on bidirectional JPA relations | Prevents Jackson infinite recursion and N+1 serialization |
| `jackson-datatype-hibernate6` | Correctly serializes Hibernate proxy objects — no empty `{}` responses |
| UUID booking references (`MH-A1B2C3D4`) | Human-readable reference without exposing sequential database IDs |
| Tax calculated server-side | Prevents client-side price manipulation |
| `$2y$` → `$2a$` BCrypt prefix | Spring Security uses `$2a$`; PHP uses `$2y$` — same algorithm, different identifier |

---

## Pages

| URL | Page | Auth Required |
|-----|------|---------------|
| `/` | Home — hero search, featured hotels | No |
| `/hotels` | Hotel listing — filter sidebar, sort, paginate | No |
| `/hotels/:id` | Hotel detail — gallery, rooms, amenities, reviews | No |
| `/login` | Login — redirects admin to `/admin` | No |
| `/signup` | Register | No |
| `/book/:hotelId` | Booking form with live price calculator | Yes |
| `/booking/:id` | Booking confirmation with booking number | Yes |
| `/profile` | Dashboard — stats, booking history, settings | Yes |
| `/admin` | Admin panel — hotel CRUD, image upload, stats | Admin only |

---

## Configuration

`backend/src/main/resources/application.properties`:

```properties
# Database
spring.datasource.url=jdbc:mysql://localhost:3306/moonheritage?useSSL=false&serverTimezone=UTC
spring.datasource.username=root
spring.datasource.password=

# Server
server.port=8081
server.servlet.context-path=/api

# JWT
jwt.secret=MoonHeritage-Super-Secret-Key-2024-Must-Be-At-Least-256-Bits-Long!
jwt.expiration=86400000

# File Upload (images saved to XAMPP htdocs for Apache to serve)
app.upload.dir=/Applications/XAMPP/xamppfiles/htdocs/MoonHeritage/images/
```

---

## Troubleshooting

| Error | Fix |
|-------|-----|
| `Port 8081 already in use` | `kill $(lsof -t -i:8081)` then restart backend |
| `Communications link failure` | Start MySQL in XAMPP Control Panel |
| `moonheritage DB not found` | Import `database.sql` via phpMyAdmin |
| `No plugin found for prefix 'spring-boot'` | Run `mvn spring-boot:run` from `java-react/backend/`, not from `java-react/` |
| `Access denied` on `localhost:8081/api` | Expected — the UI is at `localhost:3000`, not the raw API root |
| `node: No such file or directory` | `export PATH="/opt/homebrew/bin:$PATH"` |
| `Cannot find package '@tailwindcss/vite'` | That is Tailwind v4 only. This project uses v3 via PostCSS — remove any `@tailwindcss/vite` import from `vite.config.js` |
| All hotel cards show the same image | Old seed data is still in the database — re-import `database.sql` via phpMyAdmin |
| `bcrypt is not defined` in admin reset tool | File must be opened via XAMPP (`http://localhost/MoonHeritage/admin-reset-password.html`), not as a local `file://` path |

---

## Author

**Tejas N C**

Built as a full-stack portfolio project demonstrating Java Spring Boot REST API design, React SPA architecture, JWT security, JPA/Hibernate ORM, and Tailwind CSS. Includes a real-world migration challenge: replacing a PHP backend while preserving MySQL data and BCrypt password compatibility.
