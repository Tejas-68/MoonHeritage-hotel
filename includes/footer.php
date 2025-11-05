<footer class="bg-black text-white py-8">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-2xl font-bold mb-2">MoonHeritage</h2>
        <p class="text-gray-400 mb-4">Making travel simple and accessible for everyone.</p>

        <div class="flex justify-center space-x-4 mb-6">
            <a href="https://www.linkedin.com/in/tejas-n-c" target="_blank"
                class="bg-gray-800 p-3 rounded-full hover:bg-gray-700 transition">
                <i class="fab fa-linkedin text-xl"></i>
            </a>
            <a href="https://github.com/Tejas-68" target="_blank"
                class="bg-gray-800 p-3 rounded-full hover:bg-gray-700 transition">
                <i class="fab fa-github text-xl"></i>
            </a>
        </div>

        <p class="text-gray-500 text-sm">© 2025 MoonHeritage. All rights reserved.</p>
    </div>



    <div class="border-t border-gray-800 pt-8 mb-8 text-center">
        <h4 class="text-sm text-gray-400 mb-4">We Accept</h4>
        <div class="flex justify-center flex-wrap gap-4 text-gray-400">
            <i class="fab fa-cc-visa text-3xl"></i>
            <i class="fab fa-cc-mastercard text-3xl"></i>
            <i class="fab fa-cc-amex text-3xl"></i>
            <i class="fab fa-cc-paypal text-3xl"></i>
            <i class="fab fa-cc-stripe text-3xl"></i>
        </div>
    </div>


    <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
        <p class="text-gray-400 text-sm mb-4 md:mb-0">
            © <?php echo date('Y'); ?> MoonHeritage. All rights reserved.
        </p>
    </div>
    </div>
</footer>

<script>
    // Newsletter subscription
    document.getElementById('newsletterForm')?.addEventListener('submit', function (e) {
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