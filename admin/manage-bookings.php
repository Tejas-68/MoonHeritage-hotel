<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = getDB();


$status = sanitize($_GET['status'] ?? 'all');
$search = sanitize($_GET['search'] ?? '');


$where = ["1=1"];
$params = [];

if ($status !== 'all') {
    $where[] = "b.booking_status = ?";
    $params[] = $status;
}

if ($search) {
    $where[] = "(b.booking_number LIKE ? OR h.name LIKE ? OR u.email LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$whereClause = implode(' AND ', $where);


$bookings = $db->prepare("
    SELECT b.*, h.name as hotel_name, h.city, h.country,
           u.email as user_email, u.first_name, u.last_name, u.phone
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    JOIN users u ON b.user_id = u.id
    WHERE $whereClause
    ORDER BY b.created_at DESC
");
$bookings->execute($params);
$allBookings = $bookings->fetchAll();


$stats = $db->query("
    SELECT 
        COUNT(CASE WHEN booking_status = 'pending' THEN 1 END) as pending,
        COUNT(CASE WHEN booking_status = 'confirmed' THEN 1 END) as confirmed,
        COUNT(CASE WHEN booking_status = 'cancelled' THEN 1 END) as cancelled,
        COUNT(CASE WHEN booking_status = 'completed' THEN 1 END) as completed
    FROM bookings
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
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
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Manage Bookings</h1>
            <p class="text-gray-600">Review and manage all property bookings</p>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-yellow-50 rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-600 text-sm font-semibold">Pending</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['pending']; ?></h3>
                    </div>
                    <i class="fas fa-clock text-4xl text-yellow-500"></i>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-600 text-sm font-semibold">Confirmed</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['confirmed']; ?></h3>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-green-500"></i>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-600 text-sm font-semibold">Completed</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['completed']; ?></h3>
                    </div>
                    <i class="fas fa-flag-checkered text-4xl text-blue-500"></i>
                </div>
            </div>

            <div class="bg-red-50 rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-600 text-sm font-semibold">Cancelled</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['cancelled']; ?></h3>
                    </div>
                    <i class="fas fa-times-circle text-4xl text-red-500"></i>
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="<?php echo escape($search); ?>" 
                           placeholder="Search by booking #, hotel, or guest..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="manage-bookings.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </form>
        </div>

        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booking #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hotel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In/Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guests</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($allBookings as $booking): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">#<?php echo $booking['booking_number']; ?></div>
                                <div class="text-xs text-gray-500"><?php echo formatDate($booking['created_at']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo escape($booking['first_name'] . ' ' . $booking['last_name']); ?>
                                </div>
                                <div class="text-xs text-gray-500"><?php echo escape($booking['user_email']); ?></div>
                                <?php if ($booking['phone']): ?>
                                <div class="text-xs text-gray-500"><?php echo escape($booking['phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo escape($booking['hotel_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo escape($booking['city'] . ', ' . $booking['country']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-calendar-alt text-blue-500 mr-1"></i>
                                    <?php echo formatDate($booking['check_in_date'], 'M d'); ?>
                                </div>
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-calendar-check text-green-500 mr-1"></i>
                                    <?php echo formatDate($booking['check_out_date'], 'M d'); ?>
                                </div>
                                <div class="text-xs text-gray-500"><?php echo $booking['total_nights']; ?> nights</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <i class="fas fa-user text-gray-400 mr-1"></i><?php echo $booking['guests_adults']; ?>
                                <?php if ($booking['guests_children'] > 0): ?>
                                <br><i class="fas fa-child text-gray-400 mr-1"></i><?php echo $booking['guests_children']; ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900"><?php echo formatPrice($booking['total_amount']); ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php
                                    $paymentColors = [
                                        'paid' => 'text-green-600',
                                        'pending' => 'text-yellow-600',
                                        'failed' => 'text-red-600'
                                    ];
                                    $paymentClass = $paymentColors[$booking['payment_status']] ?? 'text-gray-600';
                                    ?>
                                    <span class="<?php echo $paymentClass; ?>">
                                        <i class="fas fa-circle text-xs mr-1"></i><?php echo ucfirst($booking['payment_status']); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'completed' => 'bg-blue-100 text-blue-800'
                                ];
                                $statusClass = $statusColors[$booking['booking_status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($booking['booking_status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($booking['booking_status'] === 'pending'): ?>
                                <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'confirmed')" 
                                        class="text-green-600 hover:text-green-800 mr-2" title="Accept">
                                    <i class="fas fa-check-circle text-lg"></i>
                                </button>
                                <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'cancelled')" 
                                        class="text-red-600 hover:text-red-800 mr-2" title="Reject">
                                    <i class="fas fa-times-circle text-lg"></i>
                                </button>
                                <?php endif; ?>
                                <button onclick="viewBooking(<?php echo htmlspecialchars(json_encode($booking)); ?>)" 
                                        class="text-blue-600 hover:text-blue-800" title="View Details">
                                    <i class="fas fa-eye text-lg"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h2 class="text-2xl font-bold text-gray-800">Booking Details</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div id="bookingDetails" class="p-6"></div>
        </div>
    </div>

    <script>
        function updateBookingStatus(bookingId, status) {
            const action = status === 'confirmed' ? 'accept' : 'reject';
            if (!confirm(`Are you sure you want to ${action} this booking?`)) return;

            fetch('../api/update-booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ booking_id: bookingId, status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Booking updated successfully');
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

        function viewBooking(booking) {
            const details = `
                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Guest Information</h3>
                        <p><strong>Name:</strong> ${booking.first_name} ${booking.last_name}</p>
                        <p><strong>Email:</strong> ${booking.user_email}</p>
                        ${booking.phone ? `<p><strong>Phone:</strong> ${booking.phone}</p>` : ''}
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Booking Information</h3>
                        <p><strong>Booking #:</strong> ${booking.booking_number}</p>
                        <p><strong>Hotel:</strong> ${booking.hotel_name}</p>
                        <p><strong>Location:</strong> ${booking.city}, ${booking.country}</p>
                        <p><strong>Check-in:</strong> ${booking.check_in_date}</p>
                        <p><strong>Check-out:</strong> ${booking.check_out_date}</p>
                        <p><strong>Nights:</strong> ${booking.total_nights}</p>
                        <p><strong>Guests:</strong> ${booking.guests_adults} adults, ${booking.guests_children} children</p>
                        <p><strong>Rooms:</strong> ${booking.rooms_count}</p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Payment Details</h3>
                        <p><strong>Subtotal:</strong> $${parseFloat(booking.subtotal).toFixed(2)}</p>
                        <p><strong>Tax:</strong> $${parseFloat(booking.tax_amount).toFixed(2)}</p>
                        <p><strong>Discount:</strong> -$${parseFloat(booking.discount_amount).toFixed(2)}</p>
                        <p class="text-xl font-bold mt-2"><strong>Total:</strong> $${parseFloat(booking.total_amount).toFixed(2)}</p>
                        <p><strong>Payment Status:</strong> <span class="capitalize">${booking.payment_status}</span></p>
                    </div>
                    
                    ${booking.special_requests ? `
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Special Requests</h3>
                        <p>${booking.special_requests}</p>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('bookingDetails').innerHTML = details;
            document.getElementById('bookingModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('bookingModal').classList.add('hidden');
        }
    </script>
</body>
</html>