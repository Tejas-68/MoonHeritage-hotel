<!-- Footer -->
<footer class="bg-black text-white py-8">
    <div class="container mx-auto px-6">
        
        <!-- Main Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
            
            <!-- Brand Info -->
            <div>
                <div class="flex items-center space-x-2 mb-3">
                    <i class="fas fa-moon text-xl"></i>
                    <h3 class="text-xl font-bold">MoonHeritage</h3>
                </div>
                <p class="text-gray-400 text-sm mb-4">
                    Find your best staycation with us.
                </p>
                
                <!-- Social Links -->
                <div class="flex space-x-3">
                    <a href="https://www.linkedin.com/in/tejas-n-c" target="_blank" class="text-gray-400 hover:text-white">
                        <i class="fab fa-linkedin text-xl"></i>
                    </a>
                    <a href="https://github.com/Tejas-68" target="_blank" class="text-gray-400 hover:text-white">
                        <i class="fab fa-github text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                </div>
            </div>

            <!-- Newsletter -->
            <div>
                <h4 class="font-bold mb-3">Subscribe to Newsletter</h4>
                <p class="text-gray-400 text-sm mb-3">Get exclusive offers and deals.</p>
                <form id="newsletterForm">
                    <input type="email" name="email" required placeholder="Enter your email" 
                           class="w-full px-4 py-2 rounded bg-gray-800 text-white mb-2 focus:outline-none">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="text-center mb-6">
            <p class="text-gray-400 text-sm mb-3">We Accept</p>
            <div class="flex justify-center space-x-4 text-gray-400">
                <i class="fab fa-cc-visa text-3xl"></i>
                <i class="fab fa-cc-mastercard text-3xl"></i>
                <i class="fab fa-cc-paypal text-3xl"></i>
            </div>
        </div>

        <!-- Copyright -->
        <div class="text-center border-t border-gray-800 pt-4">
            <p class="text-gray-400 text-sm">
                © <?php echo date('Y'); ?> MoonHeritage. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<script>
// Newsletter form
document.getElementById('newsletterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var email = this.email.value;
    var button = this.querySelector('button');
    
    button.textContent = 'Subscribing...';
    button.disabled = true;
    
    fetch('api/newsletter.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({email: email})
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Successfully subscribed!');
            document.getElementById('newsletterForm').reset();
        } else {
            alert('Subscription failed. Please try again.');
        }
        button.textContent = 'Subscribe';
        button.disabled = false;
    })
    .catch(error => {
        alert('Error occurred. Please try again.');
        button.textContent = 'Subscribe';
        button.disabled = false;
    });
});
</script>