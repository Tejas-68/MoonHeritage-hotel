// MoonHeritage - Main JavaScript File

// Wait for DOM to be fully loaded
document.addEventListener("DOMContentLoaded", function () {
  initializeApp();
});

// Initialize all app functions
function initializeApp() {
  initNavbar();
  initSearchBox();
  initFilters();
  initHotelCards();
  initDatePicker();
  initScrollAnimations();
  initLazyLoading();
  initMobileMenu();
}

// Navbar scroll effect
function initNavbar() {
  const navbar = document.querySelector("nav");
  let lastScroll = 0;

  window.addEventListener("scroll", () => {
    const currentScroll = window.pageYOffset;

    if (currentScroll > 100) {
      navbar.classList.add("scrolled");
    } else {
      navbar.classList.remove("scrolled");
    }

    lastScroll = currentScroll;
  });
}

// Search box functionality
function initSearchBox() {
  const searchForm = document.querySelector(".search-form");
  const locationInput = document.querySelector(
    'input[placeholder="Add destination"]'
  );
  const dateInput = document.querySelector('input[type="date"]');
  const guestInput = document.querySelector(
    'input[placeholder="1 guest, 1 room"]'
  );

  // Set minimum date to today
  if (dateInput) {
    const today = new Date().toISOString().split("T")[0];
    dateInput.setAttribute("min", today);
  }

  // Guest input dropdown
  if (guestInput) {
    guestInput.addEventListener("click", function () {
      showGuestSelector(this);
    });
  }

  // Search functionality
  const searchForms = document.querySelectorAll(".search-form");
  searchForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      performSearch();
    });
  });
}

// Guest selector modal
function showGuestSelector(input) {
  const modal = document.createElement("div");
  modal.className = "absolute bg-white shadow-lg rounded-lg p-6 mt-2 z-50 w-80";
  modal.innerHTML = `
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="font-semibold">Adults</span>
                <div class="flex items-center gap-3">
                    <button class="guest-minus bg-gray-200 w-8 h-8 rounded-full hover:bg-gray-300" data-type="adults">-</button>
                    <span class="guest-count" data-type="adults">1</span>
                    <button class="guest-plus bg-gray-200 w-8 h-8 rounded-full hover:bg-gray-300" data-type="adults">+</button>
                </div>
            </div>
            <div class="flex justify-between items-center">
                <span class="font-semibold">Children</span>
                <div class="flex items-center gap-3">
                    <button class="guest-minus bg-gray-200 w-8 h-8 rounded-full hover:bg-gray-300" data-type="children">-</button>
                    <span class="guest-count" data-type="children">0</span>
                    <button class="guest-plus bg-gray-200 w-8 h-8 rounded-full hover:bg-gray-300" data-type="children">+</button>
                </div>
            </div>
            <div class="flex justify-between items-center">
                <span class="font-semibold">Rooms</span>
                <div class="flex items-center gap-3">
                    <button class="guest-minus bg-gray-200 w-8 h-8 rounded-full hover:bg-gray-300" data-type="rooms">-</button>
                    <span class="guest-count" data-type="rooms">1</span>
                    <button class="guest-plus bg-gray-200 w-8 h-8 rounded-full hover:bg-gray-300" data-type="rooms">+</button>
                </div>
            </div>
            <button class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 mt-4" id="applyGuests">Apply</button>
        </div>
    `;

  input.parentElement.appendChild(modal);

  // Handle guest counter
  const counts = { adults: 1, children: 0, rooms: 1 };

  modal.querySelectorAll(".guest-plus").forEach((btn) => {
    btn.addEventListener("click", () => {
      const type = btn.dataset.type;
      counts[type]++;
      updateGuestDisplay(modal, counts, input);
    });
  });

  modal.querySelectorAll(".guest-minus").forEach((btn) => {
    btn.addEventListener("click", () => {
      const type = btn.dataset.type;
      if (counts[type] > (type === "children" ? 0 : 1)) {
        counts[type]--;
        updateGuestDisplay(modal, counts, input);
      }
    });
  });

  document.getElementById("applyGuests").addEventListener("click", () => {
    const totalGuests = counts.adults + counts.children;
    input.value = `${totalGuests} guest${totalGuests > 1 ? "s" : ""}, ${
      counts.rooms
    } room${counts.rooms > 1 ? "s" : ""}`;
    modal.remove();
  });

  // Close modal when clicking outside
  setTimeout(() => {
    document.addEventListener("click", function closeModal(e) {
      if (!modal.contains(e.target) && e.target !== input) {
        modal.remove();
        document.removeEventListener("click", closeModal);
      }
    });
  }, 100);
}

function updateGuestDisplay(modal, counts, input) {
  Object.keys(counts).forEach((type) => {
    const countSpan = modal.querySelector(`.guest-count[data-type="${type}"]`);
    if (countSpan) countSpan.textContent = counts[type];
  });
}

// Filter functionality
function initFilters() {
  const filterButtons = document.querySelectorAll(".px-4.py-1, .px-6.py-2");

  filterButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Remove active class from siblings
      const siblings = this.parentElement.querySelectorAll(
        ".px-4.py-1, .px-6.py-2"
      );
      siblings.forEach((sibling) => {
        sibling.classList.remove("bg-blue-600", "text-white");
        sibling.classList.add("bg-gray-200", "text-gray-700");
      });

      // Add active class to clicked button
      this.classList.remove("bg-gray-200", "text-gray-700");
      this.classList.add("bg-blue-600", "text-white");

      // Filter content based on selection
      const filterType = this.textContent.trim();
      filterHotels(filterType);
    });
  });
}

function filterHotels(filterType) {
  // This would filter hotel cards based on the selected type
  console.log("Filtering by:", filterType);
  showToast("Filter applied: " + filterType, "success");
}

// Hotel card interactions
function initHotelCards() {
  const hotelCards = document.querySelectorAll(
    ".bg-white.rounded-lg.shadow-lg"
  );

  hotelCards.forEach((card) => {
    // Wishlist heart icon
    const heartButton = card.querySelector(".fa-heart")?.parentElement;
    if (heartButton) {
      heartButton.addEventListener("click", function (e) {
        e.stopPropagation();
        toggleWishlist(this);
      });
    }

    // Card click to view details
    card.addEventListener("click", function () {
      const hotelName = this.querySelector("h3")?.textContent;
      if (hotelName) {
        viewHotelDetails(hotelName);
      }
    });
  });
}

function toggleWishlist(button) {
  const icon = button.querySelector("i");

  if (icon.classList.contains("far")) {
    icon.classList.remove("far");
    icon.classList.add("fas");
    icon.style.color = "#ef4444";
    showToast("Added to wishlist", "success");
  } else {
    icon.classList.remove("fas");
    icon.classList.add("far");
    icon.style.color = "";
    showToast("Removed from wishlist", "info");
  }
}

function viewHotelDetails(hotelName) {
  console.log("Viewing hotel:", hotelName);
  // Redirect to hotel details page
  window.location.href = `hotel-details.php?name=${encodeURIComponent(hotelName)}`;
}

// Date picker functionality
function initDatePicker() {
  const dateInputs = document.querySelectorAll('input[type="date"]');

  dateInputs.forEach((input) => {
    input.addEventListener("change", function () {
      validateDates();
    });
  });
}

function validateDates() {
  const dateInputs = document.querySelectorAll('input[type="date"]');
  if (dateInputs.length >= 2) {
    const checkIn = new Date(dateInputs[0].value);
    const checkOut = new Date(dateInputs[1].value);

    if (checkOut <= checkIn) {
      showToast("Check-out date must be after check-in date", "error");
      dateInputs[1].value = "";
    }
  }
}

// Perform search
function performSearch() {
  const location = document.querySelector(
    'input[placeholder="Add destination"]'
  )?.value;
  const date = document.querySelector('input[type="date"]')?.value;
  const guests = document.querySelector(
    'input[placeholder="1 guest, 1 room"]'
  )?.value;

  if (!location) {
    showToast("Please enter a destination", "error");
    return;
  }

  if (!date) {
    showToast("Please select a date", "error");
    return;
  }

  // Show loading
  const searchButton = document.querySelector(
    ".bg-blue-600.text-white.px-6.py-3"
  );
  const originalText = searchButton.innerHTML;
  searchButton.innerHTML =
    '<i class="fas fa-spinner fa-spin"></i> Searching...';
  searchButton.disabled = true;

  // Simulate search (replace with actual API call)
  setTimeout(() => {
    searchButton.innerHTML = originalText;
    searchButton.disabled = false;

    // Redirect to search results
    const searchParams = new URLSearchParams({
      location: location,
      date: date,
      guests: guests,
    });

    window.location.href = `search-results.php?${searchParams.toString()}`;
  }, 1500);
}

// Scroll animations
function initScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("fade-in");
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  // Observe all sections
  const sections = document.querySelectorAll("section");
  sections.forEach((section) => {
    observer.observe(section);
  });
}

// Lazy loading images
function initLazyLoading() {
  const images = document.querySelectorAll('img[loading="lazy"]');

  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src || img.src;
          img.classList.add("loaded");
          imageObserver.unobserve(img);
        }
      });
    });

    images.forEach((img) => imageObserver.observe(img));
  }
}

// Mobile menu toggle
function initMobileMenu() {
  const mobileMenuButton = document.querySelector(".mobile-menu-button");
  const mobileMenu = document.querySelector(".mobile-menu");

  if (mobileMenuButton && mobileMenu) {
    mobileMenuButton.addEventListener("click", () => {
      mobileMenu.classList.toggle("hidden");
    });
  }
}

// Toast notification system
function showToast(message, type = "info") {
  const toast = document.createElement("div");
  toast.className = `toast ${type}`;

  const icons = {
    success: "fa-check-circle",
    error: "fa-exclamation-circle",
    info: "fa-info-circle",
    warning: "fa-exclamation-triangle",
  };

  const colors = {
    success: "text-green-600",
    error: "text-red-600",
    info: "text-blue-600",
    warning: "text-yellow-600",
  };

  toast.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fas ${icons[type]} ${colors[type]} text-xl"></i>
            <span class="text-gray-800">${message}</span>
            <button class="ml-4 text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

  document.body.appendChild(toast);

  // Auto remove after 3 seconds
  setTimeout(() => {
    toast.style.animation = "slideOutRight 0.3s ease-out";
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// Newsletter subscription
const newsletterForm = document.querySelector(
  'footer input[type="email"]'
)?.parentElement;
if (newsletterForm) {
  const subscribeButton = newsletterForm.querySelector("button");
  subscribeButton?.addEventListener("click", function (e) {
    e.preventDefault();
    const emailInput = newsletterForm.querySelector('input[type="email"]');
    const email = emailInput.value;

    if (validateEmail(email)) {
      subscribeNewsletter(email);
      emailInput.value = "";
    } else {
      showToast("Please enter a valid email address", "error");
    }
  });
}

function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function subscribeNewsletter(email) {
  // Show loading
  showToast("Subscribing...", "info");

  // Simulate API call
  setTimeout(() => {
    showToast("Successfully subscribed to newsletter!", "success");
    console.log("Subscribed:", email);
  }, 1000);
}

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

// Price formatting
function formatPrice(price) {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 0,
  }).format(price);
}

// Date formatting
function formatDate(date) {
  return new Intl.DateTimeFormat("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  }).format(new Date(date));
}

// Search suggestions
let searchTimeout;
const locationInput = document.querySelector(
  'input[placeholder="Add destination"]'
);

if (locationInput) {
  locationInput.addEventListener("input", function () {
    clearTimeout(searchTimeout);
    const query = this.value;

    if (query.length >= 2) {
      searchTimeout = setTimeout(() => {
        fetchSearchSuggestions(query);
      }, 300);
    }
  });
}

function fetchSearchSuggestions(query) {
  // This would fetch suggestions from backend
  console.log("Fetching suggestions for:", query);

  // Mock suggestions
  const suggestions = [
    "Paris, France",
    "Tokyo, Japan",
    "New York, USA",
    "London, UK",
    "Dubai, UAE",
  ].filter((item) => item.toLowerCase().includes(query.toLowerCase()));

  showSuggestions(suggestions);
}

function showSuggestions(suggestions) {
  const locationInput = document.querySelector(
    'input[placeholder="Add destination"]'
  );
  const existingDropdown = document.querySelector(".suggestions-dropdown");

  if (existingDropdown) {
    existingDropdown.remove();
  }

  if (suggestions.length === 0) return;

  const dropdown = document.createElement("div");
  dropdown.className =
    "suggestions-dropdown absolute bg-white shadow-lg rounded-lg mt-2 w-full z-50";
  dropdown.style.maxHeight = "300px";
  dropdown.style.overflowY = "auto";

  suggestions.forEach((suggestion) => {
    const item = document.createElement("div");
    item.className =
      "px-4 py-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100";
    item.innerHTML = `<i class="fas fa-map-marker-alt text-gray-400 mr-3"></i>${suggestion}`;
    item.addEventListener("click", () => {
      locationInput.value = suggestion;
      dropdown.remove();
    });
    dropdown.appendChild(item);
  });

  locationInput.parentElement.appendChild(dropdown);
}

// Window resize handler
let resizeTimeout;
window.addEventListener("resize", function () {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(() => {
    console.log("Window resized");
    // Handle responsive adjustments
  }, 250);
});

// Page visibility handler
document.addEventListener("visibilitychange", function () {
  if (document.hidden) {
    console.log("Page is hidden");
  } else {
    console.log("Page is visible");
  }
});

// Error handling
window.addEventListener("error", function (e) {
  console.error("Error occurred:", e.error);
  // Log to error tracking service
});

// Console welcome message
console.log(
  "%cMoonHeritage",
  "font-size: 24px; font-weight: bold; color: #3b82f6;"
);
console.log("%cFind Your Best Staycation", "font-size: 14px; color: #6b7280;");

// Export functions for use in other files
window.MoonHeritage = {
  showToast,
  formatPrice,
  formatDate,
  validateEmail,
};
