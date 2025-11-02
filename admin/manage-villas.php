<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = getDB();

// Get all villas
$villas = $db->query("
    SELECT h.*, 
    (SELECT COUNT(*) FROM bookings WHERE hotel_id = h.id) as booking_count
    FROM hotels h 
    WHERE h.category = 'villa'
    ORDER BY h.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Villas - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media (max-width: 768px) {
            body { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/sidebar.php'; ?>

    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manage Villas</h1>
                <p class="text-gray-600">Add, edit or remove luxury villas</p>
            </div>
            <button onclick="openAddModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center">
                <i class="fas fa-plus mr-2"></i>Add New Villa
            </button>
        </div>

        <!-- Villas Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Villa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price/Night</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bookings</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($villas as $villa): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img src="<?php echo getImageUrl($villa['main_image']); ?>" 
                                     class="w-16 h-16 rounded object-cover mr-3">
                                <div>
                                    <div class="font-semibold text-gray-900"><?php echo escape($villa['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $villa['total_rooms']; ?> rooms</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo escape($villa['city'] . ', ' . $villa['country']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                            <?php echo formatPrice($villa['price_per_night']); ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <span class="text-yellow-400 mr-1">★</span>
                                <span class="text-sm font-semibold"><?php echo $villa['star_rating']; ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <?php echo $villa['booking_count']; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'inactive' => 'bg-red-100 text-red-800',
                                'pending' => 'bg-yellow-100 text-yellow-800'
                            ];
                            ?>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$villa['status']]; ?>">
                                <?php echo ucfirst($villa['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <button onclick="editVilla(<?php echo htmlspecialchars(json_encode($villa)); ?>)" 
                                    class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteVilla(<?php echo $villa['id']; ?>, '<?php echo escape($villa['name']); ?>')" 
                                    class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="villaModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">Add New Villa</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form id="villaForm" class="p-6" enctype="multipart/form-data">
                <input type="hidden" id="villaId" name="id">
                
                <div class="grid grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Villa Name *</label>
                        <input type="text" id="name" name="name" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Short Description *</label>
                        <input type="text" id="short_description" name="short_description" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Full Description *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Address *</label>
                        <input type="text" id="address" name="address" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">City *</label>
                        <input type="text" id="city" name="city" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Country *</label>
                        <input type="text" id="country" name="country" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Phone</label>
                        <input type="text" id="phone" name="phone"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Price Per Night *</label>
                        <input type="number" id="price_per_night" name="price_per_night" step="0.01" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Original Price</label>
                        <input type="number" id="original_price" name="original_price" step="0.01"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Star Rating *</label>
                        <select id="star_rating" name="star_rating" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="5.0">5 Star</option>
                            <option value="4.5">4.5 Star</option>
                            <option value="4.0">4 Star</option>
                            <option value="3.5">3.5 Star</option>
                            <option value="3.0">3 Star</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Total Rooms *</label>
                        <input type="number" id="total_rooms" name="total_rooms" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Featured</label>
                        <select id="featured" name="featured"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Status *</label>
                        <select id="status" name="status" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Main Image</label>
                        <input type="file" id="main_image" name="main_image" accept="image/*"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Leave empty to keep existing image</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="closeModal()" 
                            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Save Villa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Villa';
            document.getElementById('villaForm').reset();
            document.getElementById('villaId').value = '';
            document.getElementById('villaModal').classList.remove('hidden');
        }

        function editVilla(villa) {
            document.getElementById('modalTitle').textContent = 'Edit Villa';
            document.getElementById('villaId').value = villa.id;
            document.getElementById('name').value = villa.name;
            document.getElementById('short_description').value = villa.short_description;
            document.getElementById('description').value = villa.description;
            document.getElementById('address').value = villa.address;
            document.getElementById('city').value = villa.city;
            document.getElementById('country').value = villa.country;
            document.getElementById('phone').value = villa.phone || '';
            document.getElementById('price_per_night').value = villa.price_per_night;
            document.getElementById('original_price').value = villa.original_price || '';
            document.getElementById('star_rating').value = villa.star_rating;
            document.getElementById('total_rooms').value = villa.total_rooms;
            document.getElementById('featured').value = villa.featured;
            document.getElementById('status').value = villa.status;
            document.getElementById('villaModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('villaModal').classList.add('hidden');
        }

        function deleteVilla(id, name) {
            if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) return;

            fetch('../api/delete-hotel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Villa deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }

        document.getElementById('villaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('category', 'villa');
            
            fetch('../api/save-hotel.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Villa saved successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred');
                console.error(error);
            });
        });
    </script>
</body>
</html>