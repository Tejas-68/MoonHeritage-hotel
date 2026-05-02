// ═══════════════════════════════════════════════════════════════════
// MoonHeritage — MongoDB Atlas Seed Script
// (Data migrated from database.sql)
// ═══════════════════════════════════════════════════════════════════

use("moonheritage");

print("Starting MoonHeritage database seed (from SQL data)...\n");

db.users.drop();
db.hotels.drop();
db.bookings.drop();
db.reviews.drop();

// Indexes
db.users.createIndex({ email: 1 }, { unique: true });
db.users.createIndex({ username: 1 }, { unique: true });
db.hotels.createIndex({ slug: 1 }, { unique: true });

// 1. Users
const users = [
  {
    username: "admin",
    email: "admin@moonheritage.com",
    password: "$2a$12$2CgEbAfGYQKnxm8qfckJIOeK0FJ7dMTiNQzxXhCUjUx.dS3uuxiAW", // admin123
    firstName: "Admin",
    lastName: "User",
    role: "admin",
    emailVerified: true,
    status: "active",
    createdAt: new Date(),
    updatedAt: new Date()
  },
  {
    username: "teajs",
    email: "tejas@gmail.com",
    password: "$2a$12$2CgEbAfGYQKnxm8qfckJIOeK0FJ7dMTiNQzxXhCUjUx.dS3uuxiAW", 
    firstName: "tejas",
    lastName: "yalvar",
    role: "user",
    emailVerified: true,
    status: "active",
    createdAt: new Date(),
    updatedAt: new Date()
  },
  {
    username: "manju",
    email: "manju@example.com",
    password: "$2a$12$2CgEbAfGYQKnxm8qfckJIOeK0FJ7dMTiNQzxXhCUjUx.dS3uuxiAW",
    firstName: ",manju",
    lastName: "nath",
    role: "user",
    emailVerified: true,
    status: "active",
    createdAt: new Date(),
    updatedAt: new Date()
  },
  {
    username: "adarsh",
    email: "adi@example.com",
    password: "$2a$12$2CgEbAfGYQKnxm8qfckJIOeK0FJ7dMTiNQzxXhCUjUx.dS3uuxiAW",
    firstName: "adi",
    lastName: "ganig",
    role: "user",
    emailVerified: true,
    status: "active",
    createdAt: new Date(),
    updatedAt: new Date()
  }
];

const userResult = db.users.insertMany(users);
const userIds = Object.values(userResult.insertedIds).map(id => id.toString());
const adminId = userIds[0];
const tejasId = userIds[1];
const manjuId = userIds[2];
const adiId = userIds[3];

// 2. Hotels
const hotels = [
  {
    name: "Aegean Pearl Hotel",
    slug: "aegean-pearl-hotel",
    description: "Perched on a clifftop above the Aegean Sea, Aegean Pearl Hotel delivers an extraordinary blend of Cycladic architecture and modern luxury. Whitewashed walls, cobalt-blue domes, and infinity pools merge seamlessly with the horizon.",
    shortDescription: "Clifftop luxury hotel with infinity pools and Aegean sea views in Mykonos.",
    address: "12 Agios Stefanos Road",
    city: "Mykonos", country: "Greece",
    phone: "+30 22890 12345", email: "stay@aegeanpearl.com",
    category: "hotel", starRating: 5.0,
    pricePerNight: 380.00, originalPrice: 480.00, discountPercentage: 21,
    featured: true, totalRooms: 48, availableRooms: 42,
    mainImage: "https://images.unsplash.com/photo-1613395877344-13d4a8e0d49e?w=1200&q=80",
    status: "active", viewCount: 0,
    amenities: [
      { id: "a1", name: "Free WiFi", icon: "fa-wifi", category: "connectivity" },
      { id: "a2", name: "Swimming Pool", icon: "fa-swimming-pool", category: "recreation" },
      { id: "a4", name: "Restaurant", icon: "fa-utensils", category: "dining" },
      { id: "a5", name: "Bar & Lounge", icon: "fa-cocktail", category: "dining" },
      { id: "a6", name: "Room Service", icon: "fa-concierge-bell", category: "service" },
      { id: "a7", name: "Spa & Wellness", icon: "fa-spa", category: "wellness" }
    ],
    rooms: [
      { id: "r1", roomType: "Cycladic Suite", description: "Open-plan suite with whitewashed walls.", pricePerNight: 380.00, maxOccupancy: 2, sizeSqm: 65, bedType: "King", totalRooms: 20, availableRooms: 15 },
      { id: "r2", roomType: "Infinity Pool Villa", description: "Private villa with a personal infinity pool.", pricePerNight: 620.00, maxOccupancy: 4, sizeSqm: 120, bedType: "King + Twin", totalRooms: 5, availableRooms: 2 }
    ],
    images: [
      { id: "i1", imagePath: "https://images.unsplash.com/photo-1613395877344-13d4a8e0d49e?w=1200&q=80", caption: "Infinity pool sunset", isPrimary: true, displayOrder: 0 },
      { id: "i2", imagePath: "https://images.unsplash.com/photo-1602632188808-f21f5a25f50f?w=1200&q=80", caption: "Suite terrace", isPrimary: false, displayOrder: 1 }
    ],
    createdAt: new Date(), updatedAt: new Date()
  },
  {
    name: "Maison Lumière",
    slug: "maison-lumiere-paris",
    description: "Nestled steps from the Champs-Élysées, Maison Lumière is an intimate Haussmann-era boutique hotel celebrating Parisian elegance.",
    shortDescription: "Intimate Parisian boutique hotel with Eiffel Tower rooftop views and Michelin bistro.",
    address: "18 Avenue George V",
    city: "Paris", state: "Île-de-France", country: "France",
    phone: "+33 1 47 23 55 00", email: "bonjour@maisonlumiere.fr",
    category: "hotel", starRating: 5.0,
    pricePerNight: 420.00, originalPrice: 420.00, discountPercentage: 0,
    featured: true, totalRooms: 32, availableRooms: 28,
    mainImage: "https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1200&q=80",
    status: "active", viewCount: 0,
    amenities: [
      { id: "a1", name: "Free WiFi", icon: "fa-wifi", category: "connectivity" },
      { id: "a4", name: "Restaurant", icon: "fa-utensils", category: "dining" }
    ],
    rooms: [
      { id: "r1", roomType: "Classic Parisian Room", description: "Elegant room with parquet floors.", pricePerNight: 280.00, maxOccupancy: 2, sizeSqm: 28, bedType: "Queen", totalRooms: 20, availableRooms: 10 }
    ],
    images: [
      { id: "i1", imagePath: "https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1200&q=80", caption: "Hotel façade", isPrimary: true, displayOrder: 0 }
    ],
    createdAt: new Date(), updatedAt: new Date()
  },
  {
    name: "Pura Vida Villa Ubud",
    slug: "pura-vida-villa-ubud",
    description: "Hidden within Ubud's sacred jungle, Pura Vida Villa offers an authentic Balinese retreat unlike any other.",
    shortDescription: "Lush private villas with plunge pools and traditional Balinese experiences in Ubud.",
    address: "99 Jalan Bisma, Ubud",
    city: "Ubud", state: "Bali", country: "Indonesia",
    phone: "+62 361 975 080", email: "villa@puravida-ubud.com",
    category: "villa", starRating: 4.5,
    pricePerNight: 275.00, originalPrice: 350.00, discountPercentage: 21,
    featured: true, totalRooms: 16, availableRooms: 14,
    mainImage: "https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=1200&q=80",
    status: "active", viewCount: 0,
    amenities: [
      { id: "a1", name: "Free WiFi", icon: "fa-wifi", category: "connectivity" },
      { id: "a13", name: "Private Pool", icon: "fa-water", category: "recreation" }
    ],
    rooms: [
      { id: "r1", roomType: "Garden Pool Villa", description: "Private villa with plunge pool.", pricePerNight: 275.00, maxOccupancy: 2, sizeSqm: 80, bedType: "King", totalRooms: 10, availableRooms: 5 }
    ],
    images: [
      { id: "i1", imagePath: "https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=1200&q=80", caption: "Jungle pool villa", isPrimary: true, displayOrder: 0 }
    ],
    createdAt: new Date(), updatedAt: new Date()
  },
  {
    name: "Turquoise Horizon Resort",
    slug: "turquoise-horizon-resort",
    description: "Turquoise Horizon Resort is the pinnacle of barefoot luxury in the Maldives. Overwater bungalows rise above crystal-clear lagoons.",
    shortDescription: "All-inclusive overwater bungalow resort with private reefs and seaplane transfers.",
    address: "North Malé Atoll",
    city: "Malé Atoll", country: "Maldives",
    phone: "+960 664 2000", email: "reservations@turquoisehorizon.mv",
    category: "resort", starRating: 5.0,
    pricePerNight: 850.00, originalPrice: 1200.00, discountPercentage: 29,
    featured: true, totalRooms: 65, availableRooms: 58,
    mainImage: "https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=1200&q=80",
    status: "active", viewCount: 0,
    amenities: [
      { id: "a12", name: "Beach Access", icon: "fa-umbrella-beach", category: "recreation" }
    ],
    rooms: [
      { id: "r1", roomType: "Lagoon Bungalow", description: "Overwater bungalow.", pricePerNight: 850.00, maxOccupancy: 2, sizeSqm: 90, bedType: "King", totalRooms: 30, availableRooms: 15 }
    ],
    images: [
      { id: "i1", imagePath: "https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=1200&q=80", caption: "Overwater bungalow", isPrimary: true, displayOrder: 0 }
    ],
    createdAt: new Date(), updatedAt: new Date()
  },
  {
    name: "Manhattan Heights Hotel",
    slug: "manhattan-heights-hotel-nyc",
    description: "Manhattan Heights Hotel puts the pulse of New York City at your fingertips.",
    shortDescription: "Art Deco Midtown Manhattan hotel with Central Park suites and a rooftop restaurant.",
    address: "345 Park Avenue",
    city: "New York City", state: "New York", country: "USA",
    phone: "+1 212 555 8900", email: "nyc@manhattanheights.com",
    category: "hotel", starRating: 4.5,
    pricePerNight: 345.00, originalPrice: 420.00, discountPercentage: 18,
    featured: false, totalRooms: 200, availableRooms: 175,
    mainImage: "https://images.unsplash.com/photo-1534430480872-3498386e7856?w=1200&q=80",
    status: "active", viewCount: 0,
    amenities: [
      { id: "a1", name: "Free WiFi", icon: "fa-wifi", category: "connectivity" },
      { id: "a15", name: "Rooftop Terrace", icon: "fa-city", category: "recreation" }
    ],
    rooms: [
      { id: "r1", roomType: "Central Park Suite", description: "Corner suite on floor 40.", pricePerNight: 790.00, maxOccupancy: 3, sizeSqm: 95, bedType: "King", totalRooms: 10, availableRooms: 2 }
    ],
    images: [
      { id: "i1", imagePath: "https://images.unsplash.com/photo-1534430480872-3498386e7856?w=1200&q=80", caption: "NYC skyline view", isPrimary: true, displayOrder: 0 }
    ],
    createdAt: new Date(), updatedAt: new Date()
  }
];

const hotelResult = db.hotels.insertMany(hotels);
const hotelIds = Object.values(hotelResult.insertedIds).map(id => id.toString());

// 3. Reviews
const reviews = [
  {
    userId: manjuId,
    hotelId: hotelIds[0], // Aegean Pearl
    userFirstName: ",manju",
    userLastName: "nath",
    rating: 5.0,
    title: "A dream in the Aegean",
    comment: "Woke up to a sunrise over the sea from our private terrace. The infinity pool felt like swimming in the sky.",
    serviceRating: 5.0, cleanlinessRating: 5.0, locationRating: 5.0, valueRating: 4.5,
    verifiedBooking: true,
    status: "approved",
    createdAt: new Date(), updatedAt: new Date()
  },
  {
    userId: adiId,
    hotelId: hotelIds[0],
    userFirstName: "adi",
    userLastName: "ganig",
    rating: 4.5,
    title: "Stunning but pricey",
    comment: "Perfect setting and faultless rooms. Worth every cent for a special occasion.",
    serviceRating: 4.5, cleanlinessRating: 5.0, locationRating: 5.0, valueRating: 4.0,
    verifiedBooking: true,
    status: "approved",
    createdAt: new Date(), updatedAt: new Date()
  },
  {
    userId: tejasId,
    hotelId: hotelIds[3], // Turquoise Horizon
    userFirstName: "tejas",
    userLastName: "yalvar",
    rating: 5.0,
    title: "Heaven on water",
    comment: "Staying in an overwater bungalow with manta rays beneath your feet is surreal.",
    serviceRating: 5.0, cleanlinessRating: 5.0, locationRating: 5.0, valueRating: 4.0,
    verifiedBooking: true,
    status: "approved",
    createdAt: new Date(), updatedAt: new Date()
  }
];

db.reviews.insertMany(reviews);

print("\n✅ Seeded exactly matching database.sql data!");
print(`Users: ${db.users.countDocuments()}`);
print(`Hotels: ${db.hotels.countDocuments()}`);
print(`Reviews: ${db.reviews.countDocuments()}\n`);
