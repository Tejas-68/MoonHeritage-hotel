<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = getDB();


$startDate = sanitize($_GET['start_date'] ?? date('Y-m-01')); 
$endDate = sanitize($_GET['end_date'] ?? date('Y-m-d')); 


$revenueStats = $db->prepare("
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
        SUM(CASE WHEN booking_status = 'cancelled' THEN total_amount ELSE 0 END) as total_losses,
        SUM(CASE WHEN payment_status = 'pending' THEN total_amount ELSE 0 END) as pending_revenue,
        AVG(CASE WHEN payment_status = 'paid' THEN total_amount ELSE NULL END) as avg_booking_value
    FROM bookings
    WHERE created_at BETWEEN ? AND ?
");
$revenueStats->execute([$startDate, $endDate . ' 23:59:59']);
$revenue = $revenueStats->fetch();


$topHotels = $db->prepare("
    SELECT h.name, h.city, h.country, h.category,
           COUNT(b.id) as booking_count,
           SUM(CASE WHEN b.payment_status = 'paid' THEN b.total_amount ELSE 0 END) as total_revenue
    FROM hotels h
    LEFT JOIN bookings b ON h.id = b.hotel_id AND b.created_at BETWEEN ? AND ?
    GROUP BY h.id
    ORDER BY total_revenue DESC
    LIMIT 10
");
$topHotels->execute([$startDate, $endDate . ' 23:59:59']);
$topHotelsData = $topHotels->fetchAll();


$dailyRevenue = $db->prepare("
    SELECT 
        DATE(created_at) as date,
        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as revenue,
        COUNT(*) as bookings
    FROM bookings
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date
");
$dailyRevenue->execute([$startDate, $endDate . ' 23:59:59']);
$dailyData = $dailyRevenue->fetchAll();

$dates = [];
$revenues = [];
$bookingCounts = [];
foreach ($dailyData as $row) {
    $dates[] = date('M d', strtotime($row['date']));
    $revenues[] = $row['revenue'];
    $bookingCounts[] = $row['bookings'];
}


$categoryStats = $db->prepare("
    SELECT h.category,
           COUNT(b.id) as booking_count,
           SUM(CASE WHEN b.payment_status = 'paid' THEN b.total_amount ELSE 0 END) as revenue
    FROM hotels h
    LEFT JOIN bookings b ON h.id = b.hotel_id AND b.created_at BETWEEN ? AND ?
    GROUP BY h.category
    ORDER BY revenue DESC
");
$categoryStats->execute([$startDate, $endDate . ' 23:59:59']);
$categories = $categoryStats->fetchAll();


$userStats = $db->query("
    SELECT 
        COUNT(*) as total_users,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_30d,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users
    FROM users
    WHERE role = 'user'
")->fetch();


$paymentMethods = $db->prepare("
    SELECT payment_method, COUNT(*) as count, SUM(total_amount) as total
    FROM bookings
    WHERE payment_status = 'paid' AND created_at BETWEEN ? AND ?
    GROUP BY payment_method
");
$paymentMethods->execute([$startDate, $endDate . ' 23:59:59']);
$paymentData = $paymentMethods->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media (max-width: 768px) {
            body { display: none !important; }
        }
        @media print {
            .no-print { display: none !important; }
            .ml-64 { margin-left: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="no-print">
        <?php include 'includes/sidebar.php'; ?>
    </div>

    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8 no-print">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Reports & Analytics</h1>
                <p class="text-gray-600">View detailed performance reports</p>
            </div>
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
        </div>

        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6 no-print">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $startDate; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">End Date</label>
                    <input type="date" name="end_date" value="<?php echo $endDate; ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-filter mr-2"></i>Apply Filter
                </button>
            </form>
        </div>

        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Performance Report</h2>
            <p class="text-gray-600">Period: <?php echo formatDate($startDate); ?> - <?php echo formatDate($endDate); ?></p>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-green-100 p-3 rounded-lg">
                        <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo formatPrice($revenue['total_revenue']); ?></h3>
                <p class="text-gray-600">Total Revenue</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-red-100 p-3 rounded-lg">
                        <i class="fas fa-chart-line-down text-2xl text-red-600"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo formatPrice($revenue['total_losses']); ?></h3>
                <p class="text-gray-600">Losses (Cancellations)</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <i class="fas fa-calendar-check text-2xl text-blue-600"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $revenue['total_bookings']; ?></h3>
                <p class="text-gray-600">Total Bookings</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <i class="fas fa-receipt text-2xl text-purple-600"></i>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo formatPrice($revenue['avg_booking_value']); ?></h3>
                <p class="text-gray-600">Avg Booking Value</p>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Daily Revenue Trend</h3>
                <canvas id="revenueChart"></canvas>
            </div>

            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Revenue by Category</h3>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">Top Performing Properties</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Property</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bookings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $rank = 1; foreach ($topHotelsData as $hotel): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-gray-700">#<?php echo $rank++; ?></span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                <?php echo escape($hotel['name']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <?php echo escape($hotel['city'] . ', ' . $hotel['country']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo ucfirst($hotel['category']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo $hotel['booking_count']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-green-600">
                                <?php echo formatPrice($hotel['total_revenue']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">User Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Users:</span>
                        <span class="font-bold"><?php echo $userStats['total_users']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">New (30 days):</span>
                        <span class="font-bold text-green-600"><?php echo $userStats['new_users_30d']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Active Users:</span>
                        <span class="font-bold text-blue-600"><?php echo $userStats['active_users']; ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Payment Methods</h3>
                <div class="space-y-3">
                    <?php foreach ($paymentData as $payment): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php echo ucfirst($payment['payment_method'] ?? 'N/A'); ?>:</span>
                        <span class="font-bold"><?php echo formatPrice($payment['total']); ?> (<?php echo $payment['count']; ?>)</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Profit/Loss Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Revenue:</span>
                        <span class="font-bold text-green-600"><?php echo formatPrice($revenue['total_revenue']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Losses:</span>
                        <span class="font-bold text-red-600">-<?php echo formatPrice($revenue['total_losses']); ?></span>
                    </div>
                    <div class="border-t pt-2 flex justify-between">
                        <span class="text-gray-800 font-semibold">Net Profit:</span>
                        <span class="font-bold text-lg text-green-600">
                            <?php echo formatPrice($revenue['total_revenue'] - $revenue['total_losses']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Daily Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($revenues); ?>,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Category Performance Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($categories, 'category')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($categories, 'revenue')); ?>,
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 191, 36)',
                        'rgb(239, 68, 68)',
                        'rgb(168, 85, 247)'
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
    </script>
</body>
</html>