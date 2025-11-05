<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = getDB();


$amenities = $db->query("
    SELECT a.*, 
    (SELECT COUNT(*) FROM hotel_amenities WHERE amenity_id = a.id) as usage_count
    FROM amenities a 
    ORDER BY a.name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Facilities - Admin</title>
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
                <h1 class="text-3xl font-bold text-gray-800">Manage Facilities</h1>
                <p class="text-gray-600">Add, edit or remove amenities/facilities</p>
            </div>
            <button onclick="openAddModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center">
                <i class="fas fa-plus mr-2"></i>Add New Facility
            </button>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($amenities as $amenity): ?>
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition">
                <div class="flex items-start justify-between mb-4">
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas <?php echo $amenity['icon']; ?> text-2xl text-blue-600"></i>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="editAmenity(<?php echo htmlspecialchars(json_encode($amenity)); ?>)" 
                                class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteAmenity(<?php echo $amenity['id']; ?>, '<?php echo escape($amenity['name']); ?>')" 
                                class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <h3 class="font-bold text-lg text-gray-800 mb-2"><?php echo escape($amenity['name']); ?></h3>
                <p class="text-sm text-gray-600 mb-3">
                    <span class="inline-block bg-gray-100 px-2 py-1 rounded text-xs">
                        <?php echo ucfirst($amenity['category']); ?>
                    </span>
                </p>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-hotel mr-1"></i>
                    Used in <?php echo $amenity['usage_count']; ?> properties
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    
    <div id="amenityModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">Add New Facility</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form id="amenityForm" class="p-6">
                <input type="hidden" id="amenityId" name="id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Facility Name *</label>
                    <input type="text" id="name" name="name" required
                           placeholder="e.g., Swimming Pool"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Icon (FontAwesome class) *</label>
                    <div class="relative">
                        <input type="text" id="icon" name="icon" required
                               placeholder="e.g., fa-swimming-pool"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 pr-12">
                        <div id="iconPreview" class="absolute right-3 top-3 text-2xl text-blue-600"></div>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        Find icons at: <a href="https://fontawesome.com/icons" target="_blank" class="text-blue-600 hover:underline">fontawesome.com/icons</a>
                    </p>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Category *</label>
                    <select id="category" name="category" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Select category</option>
                        <option value="connectivity">Connectivity</option>
                        <option value="recreation">Recreation</option>
                        <option value="dining">Dining</option>
                        <option value="service">Service</option>
                        <option value="wellness">Wellness</option>
                        <option value="facilities">Facilities</option>
                        <option value="transport">Transport</option>
                        <option value="policy">Policy</option>
                        <option value="comfort">Comfort</option>
                        <option value="entertainment">Entertainment</option>
                        <option value="room">Room</option>
                        <option value="security">Security</option>
                        <option value="business">Business</option>
                        <option value="family">Family</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeModal()" 
                            class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Save Facility
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Icon preview
        document.getElementById('icon').addEventListener('input', function(e) {
            const iconPreview = document.getElementById('iconPreview');
            const iconClass = e.target.value.trim();
            if (iconClass) {
                iconPreview.innerHTML = `<i class="fas ${iconClass}"></i>`;
            } else {
                iconPreview.innerHTML = '';
            }
        });

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Facility';
            document.getElementById('amenityForm').reset();
            document.getElementById('amenityId').value = '';
            document.getElementById('iconPreview').innerHTML = '';
            document.getElementById('amenityModal').classList.remove('hidden');
        }

        function editAmenity(amenity) {
            document.getElementById('modalTitle').textContent = 'Edit Facility';
            document.getElementById('amenityId').value = amenity.id;
            document.getElementById('name').value = amenity.name;
            document.getElementById('icon').value = amenity.icon;
            document.getElementById('category').value = amenity.category;
            document.getElementById('iconPreview').innerHTML = `<i class="fas ${amenity.icon}"></i>`;
            document.getElementById('amenityModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('amenityModal').classList.add('hidden');
        }

        function deleteAmenity(id, name) {
            if (!confirm(`Are you sure you want to delete "${name}"? This will remove it from all properties.`)) return;

            fetch('../api/delete-amenity.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Facility deleted successfully');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred');
                console.error(error);
            });
        }

        document.getElementById('amenityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                id: formData.get('id'),
                name: formData.get('name'),
                icon: formData.get('icon'),
                category: formData.get('category')
            };
            
            fetch('../api/save-amenity.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Facility saved successfully');
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