<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Prevent session confusion - force single context
if (!isset($_SESSION['admin_mode'])) {
    $_SESSION['admin_mode'] = true;
}

$db = getDB();

// Get statistics
$statsQuery = "
    SELECT 
        (SELECT COUNT(*) FROM hotels WHERE status = 'active') as total_properties,
        (SELECT COUNT(*) FROM bookings WHERE booking_status = 'pending') as pending_bookings,
        (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
        (SELECT SUM(total_amount) FROM bookings WHERE payment_status = 'paid' AND MONTH(created_at) = MONTH(CURDATE())) as monthly_revenue,
        (SELECT SUM(total_amount) FROM bookings WHERE payment_status = 'paid') as total_revenue
";
$stats = $db->query($statsQuery)->fetch();

// Get recent bookings
$recentBookings = $db->query("
    SELECT b.*, h.name as hotel_name, u.email as user_email, u.first_name, u.last_name
    FROM bookings b
    JOIN hotels h ON b.hotel_id = h.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC
    LIMIT 10
")->fetchAll();

// Get monthly revenue data for chart
$revenueData = $db->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as revenue,
        SUM(CASE WHEN booking_status = 'cancelled' THEN total_amount ELSE 0 END) as losses
    FROM bookings
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month
")->fetchAll();

$months = [];
$revenues = [];
$losses = [];
foreach ($revenueData as $row) {
    $months[] = date('M Y', strtotime($row['month'] . '-01'));
    $revenues[] = $row['revenue'];
    $losses[] = $row['losses'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MoonHeritage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media (max-width: 768px) {
            body {
                display: none !important;
            }
            body::before {
                content: "Admin panel is only accessible on desktop devices";
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                font-size: 1.5rem;
                text-align: center;
                padding: 2rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Mobile Block Message -->
    <div class="md:hidden fixed inset-0 bg-gray-900 text-white flex items-center justify-center p-6 z-50">
        <div class="text-center">
            <i class="fas fa-desktop text-6xl mb-4"></i>
            <h1 class="text-2xl font-bold mb-2">Desktop Only</h1>
            <p>Admin panel is only accessible on desktop devices for security and functionality reasons.</p>
        </div>
    </div>

    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 h-screen w-64 bg-gray-900 text-white shadow-lg hidden md:block">
        <div class="p-6">
            <div class="flex items-center space-x-2 mb-8">
                <i class="fas fa-moon text-2xl"></i>
                <span class="text-xl font-bold">MoonHeritage</span>
            </div>
            
            <nav class="space-y-2">
                <a href="dashboard.php" class="flex items-center space-x-3 bg-blue-600 px-4 py-3 rounded-lg">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage-hotels.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-hotel"></i>
                    <span>Manage Hotels</span>
                </a>
                <a href="manage-villas.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-home"></i>
                    <span>Manage Villas</span>
                </a>
                <a href="manage-amenities.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-swimming-pool"></i>
                    <span>Facilities</span>
                </a>
                <a href="manage-bookings.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-calendar-check"></i>
                    <span>Bookings</span>
                    <?php if ($stats['pending_bookings'] > 0): ?>
                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $stats['pending_bookings']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="manage-users.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="settings.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="../index.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition">
                    <i class="fas fa-globe"></i>
                    <span>View Website</span>
                </a>
                <a href="../logout.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-red-400">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h1>
            <p class="text-gray-600">Welcome back, <?php echo escape($_SESSION['first_name']); ?>!</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-hotel text-2xl text-blue-600"></i>
                    </div>
                    <span class="text-sm text-gray-500">Total</span>
                </div>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['total_properties']; ?></h3>
                <p class="text-gray-600">Properties</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <i class="fas fa-calendar-check text-2xl text-yellow-600"></i>
                    </div>
                    <span class="text-sm text-gray-500">Pending</span>
                </div>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['pending_bookings']; ?></h3>
                <p class="text-gray-600">Bookings</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-users text-2xl text-green-600"></i>
                    </div>
                    <span class="text-sm text-gray-500">Total</span>
                </div>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['total_users']; ?></h3>
                <p class="text-gray-600">Users</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-dollar-sign text-2xl text-purple-600"></i>
                    </div>
                    <span class="text-sm text-gray-500">This Month</span>
                </div>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo formatPrice($stats['monthly_revenue'] ?? 0); ?></h3>
                <p class="text-gray-600">Revenue</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Revenue & Losses (Last 6 Months)</h3>
                <canvas id="revenueChart"></canvas>
            </div>

            <!-- Bookings Status -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Booking Statistics</h3>
                <canvas id="bookingChart"></canvas>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Recent Bookings</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hotel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recentBookings as $booking): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?php echo $booking['booking_number']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo escape($booking['first_name'] . ' ' . $booking['last_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo escape($booking['hotel_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDate($booking['check_in_date']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                <?php echo formatPrice($booking['total_amount']); ?>
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
                                        class="text-green-600 hover:text-green-800 mr-2">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="updateBookingStatus(<?php echo $booking['id']; ?>, 'cancelled')" 
                                        class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                                <a href="booking-details.php?id=<?php echo $booking['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-800 ml-2">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($revenues); ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Losses',
                    data: <?php echo json_encode($losses); ?>,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Booking Status Chart
        <?php
        $bookingStats = $db->query("
            SELECT booking_status, COUNT(*) as count 
            FROM bookings 
            GROUP BY booking_status
        ")->fetchAll(PDO::FETCH_KEY_PAIR);
        ?>
        const bookingCtx = document.getElementById('bookingChart').getContext('2d');
        new Chart(bookingCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Confirmed', 'Cancelled', 'Completed'],
                datasets: [{
                    data: [
                        <?php echo $bookingStats['pending'] ?? 0; ?>,
                        <?php echo $bookingStats['confirmed'] ?? 0; ?>,
                        <?php echo $bookingStats['cancelled'] ?? 0; ?>,
                        <?php echo $bookingStats['completed'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        'rgb(251, 191, 36)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                        'rgb(59, 130, 246)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Update booking status
        function updateBookingStatus(bookingId, status) {
            if (!confirm(`Are you sure you want to ${status} this booking?`)) return;

            fetch('../api/update-booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
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
    </script>
</body>
</html>