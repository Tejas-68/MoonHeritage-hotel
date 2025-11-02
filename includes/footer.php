<!-- Footer -->
<footer class="bg-gray-900 text-white pt-16 pb-8">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            <!-- About Section -->
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-moon text-2xl"></i>
                    <span class="text-xl font-bold">MoonHeritage</span>
                </div>
                <p class="text-gray-400 mb-4">
                    Discover your perfect staycation with MoonHeritage. We offer the best hotels, resorts, and accommodations worldwide.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="#" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition">
                        <i class="fab fa-tiktok"></i>
                    </a>
                    <a href="#" class="bg-gray-800 p-2 rounded-full hover:bg-gray-700 transition">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="font-bold text-lg mb-4">Quick Links</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="index.html" class="hover:text-white transition">Home</a></li>
                    <li><a href="hotels.php" class="hover:text-white transition">Hotels</a></li>
                    <li><a href="about.php" class="hover:text-white transition">About Us</a></li>
                    <li><a href="contact.php" class="hover:text-white transition">Contact</a></li>
                    <li><a href="blog.php" class="hover:text-white transition">Blog</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h4 class="font-bold text-lg mb-4">Support</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="help-center.php" class="hover:text-white transition">Help Center</a></li>
                    <li><a href="faq.php" class="hover:text-white transition">FAQs</a></li>
                    <li><a href="cancellation-policy.php" class="hover:text-white transition">Cancellation Policy</a></li>
                    <li><a href="terms.php" class="hover:text-white transition">Terms & Conditions</a></li>
                    <li><a href="privacy.php" class="hover:text-white transition">Privacy Policy</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h4 class="font-bold text-lg mb-4">Newsletter</h4>
                <p class="text-gray-400 mb-4">Subscribe to get exclusive deals and offers</p>
                <form action="api/newsletter.php" method="POST" class="flex" id="newsletterForm">
                    <input type="email" name="email" required
                           placeholder="Enter your email" 
                           class="flex-1 px-4 py-2 rounded-l-lg bg-gray-800 border border-gray-700 focus:outline-none focus:border-blue-500 text-white">
                    <button type="submit" class="bg-blue-600 px-6 py-2 rounded-r-lg hover:bg-blue-700 transition">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
                <div class="mt-4">
                    <p class="text-gray-400 text-sm mb-2">Download Our App</p>
                    <div class="flex gap-2">
                        <a href="#" class="bg-gray-800 px-3 py-2 rounded-lg hover:bg-gray-700 transition text-xs">
                            <i class="fab fa-apple mr-1"></i>App Store
                        </a>
                        <a href="#" class="bg-gray-800 px-3 py-2 rounded-lg hover:bg-gray-700 transition text-xs">
                            <i class="fab fa-google-play mr-1"></i>Play Store
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="border-t border-gray-800 pt-8 mb-8">
            <h4 class="text-sm text-gray-400 mb-4">We Accept</h4>
            <div class="flex flex-wrap gap-4 text-gray-400">
                <i class="fab fa-cc-visa text-3xl"></i>
                <i class="fab fa-cc-mastercard text-3xl"></i>
                <i class="fab fa-cc-amex text-3xl"></i>
                <i class="fab fa-cc-paypal text-3xl"></i>
                <i class="fab fa-cc-stripe text-3xl"></i>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 text-sm mb-4 md:mb-0">
                © <?php echo date('Y'); ?> MoonHeritage. All rights reserved.
            </p>
            <div class="flex space-x-6 text-sm text-gray-400">
                <a href="privacy.php" class="hover:text-white transition">Privacy Policy</a>
                <a href="terms.php" class="hover:text-white transition">Terms of Service</a>
                <a href="sitemap.php" class="hover:text-white transition">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<script>
// Newsletter subscription
document.getElementById('newsletterForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const email = formData.get('email');
    
    fetch('api/newsletter.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Successfully subscribed to newsletter!', 'success');
            } else {
                alert('Successfully subscribed to newsletter!');
            }
            this.reset();
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Subscription failed', 'error');
            } else {
                alert(data.message || 'Subscription failed');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showToast === 'function') {
            showToast('An error occurred', 'error');
        }
    });
});
</script>