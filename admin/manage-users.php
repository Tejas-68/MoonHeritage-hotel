<?php
define('MOONHERITAGE_ACCESS', true);
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = getDB();

// Get filter parameters
$role = sanitize($_GET['role'] ?? 'all');
$status = sanitize($_GET['status'] ?? 'all');
$search = sanitize($_GET['search'] ?? '');

// Build query
$where = ["1=1"];
$params = [];

if ($role !== 'all') {
    $where[] = "role = ?";
    $params[] = $role;
}

if ($status !== 'all') {
    $where[] = "status = ?";
    $params[] = $status;
}

if ($search) {
    $where[] = "(email LIKE ? OR username LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

$whereClause = implode(' AND ', $where);

// Get users
$users = $db->prepare("
    SELECT u.*,
           (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as booking_count,
           (SELECT SUM(total_amount) FROM bookings WHERE user_id = u.id AND payment_status = 'paid') as total_spent
    FROM users u
    WHERE $whereClause
    ORDER BY u.created_at DESC
");
$users->execute($params);
$allUsers = $users->fetchAll();

// Get stats
$stats = $db->query("
    SELECT 
        COUNT(CASE WHEN role = 'user' THEN 1 END) as total_users,
        COUNT(CASE WHEN role = 'admin' THEN 1 END) as total_admins,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
        COUNT(CASE WHEN status = 'suspended' THEN 1 END) as suspended_users
    FROM users
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
            <h1 class="text-3xl font-bold text-gray-800">Manage Users</h1>
            <p class="text-gray-600">View and manage all registered users</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-blue-50 rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-600 text-sm font-semibold">Total Users</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['total_users']; ?></h3>
                    </div>
                    <i class="fas fa-users text-4xl text-blue-500"></i>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-600 text-sm font-semibold">Admins</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['total_admins']; ?></h3>
                    </div>
                    <i class="fas fa-user-shield text-4xl text-purple-500"></i>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-600 text-sm font-semibold">Active</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['active_users']; ?></h3>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-green-500"></i>
                </div>
            </div>

            <div class="bg-red-50 rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-600 text-sm font-semibold">Suspended</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?php echo $stats['suspended_users']; ?></h3>
                    </div>
                    <i class="fas fa-ban text-4xl text-red-500"></i>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="<?php echo escape($search); ?>" 
                           placeholder="Search by name, email, or username..."
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all" <?php echo $role === 'all' ? 'selected' : ''; ?>>All Roles</option>
                        <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>Users</option>
                        <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admins</option>
                        <option value="hotel_owner" <?php echo $role === 'hotel_owner' ? 'selected' : ''; ?>>Hotel Owners</option>
                    </select>
                </div>
                <div>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="suspended" <?php echo $status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                        <option value="deleted" <?php echo $status === 'deleted' ? 'selected' : ''; ?>>Deleted</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <a href="manage-users.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bookings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($allUsers as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900">
                                            <?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">@<?php echo escape($user['username']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo escape($user['email']); ?></div>
                                <?php if ($user['phone']): ?>
                                <div class="text-sm text-gray-500"><?php echo escape($user['phone']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $roleColors = [
                                    'admin' => 'bg-purple-100 text-purple-800',
                                    'user' => 'bg-blue-100 text-blue-800',
                                    'hotel_owner' => 'bg-green-100 text-green-800'
                                ];
                                $roleClass = $roleColors[$user['role']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $roleClass; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php echo $user['booking_count']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                <?php echo formatPrice($user['total_spent'] ?? 0); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'suspended' => 'bg-red-100 text-red-800',
                                    'deleted' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusClass = $statusColors[$user['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo formatDate($user['created_at'], 'M d, Y'); ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <button onclick="viewUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                        class="text-blue-600 hover:text-blue-800 mr-2" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($user['id'] != getUserId()): ?>
                                    <?php if ($user['status'] === 'active'): ?>
                                    <button onclick="updateUserStatus(<?php echo $user['id']; ?>, 'suspended')" 
                                            class="text-red-600 hover:text-red-800 mr-2" title="Suspend">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                    <?php else: ?>
                                    <button onclick="updateUserStatus(<?php echo $user['id']; ?>, 'active')" 
                                            class="text-green-600 hover:text-green-800 mr-2" title="Activate">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo escape($user['email']); ?>')" 
                                            class="text-red-600 hover:text-red-800" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white">
                <h2 class="text-2xl font-bold text-gray-800">User Details</h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div id="userDetails" class="p-6"></div>
        </div>
    </div>

    <script>
        function viewUser(user) {
            const details = `
                <div class="space-y-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Personal Information</h3>
                        <p><strong>Name:</strong> ${user.first_name} ${user.last_name}</p>
                        <p><strong>Username:</strong> @${user.username}</p>
                        <p><strong>Email:</strong> ${user.email}</p>
                        ${user.phone ? `<p><strong>Phone:</strong> ${user.phone}</p>` : ''}
                        ${user.address ? `<p><strong>Address:</strong> ${user.address}</p>` : ''}
                        ${user.city ? `<p><strong>City:</strong> ${user.city}, ${user.country || ''}</p>` : ''}
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Account Information</h3>
                        <p><strong>Role:</strong> <span class="capitalize">${user.role.replace('_', ' ')}</span></p>
                        <p><strong>Status:</strong> <span class="capitalize">${user.status}</span></p>
                        <p><strong>Email Verified:</strong> ${user.email_verified ? 'Yes' : 'No'}</p>
                        <p><strong>Joined:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                        ${user.last_login ? `<p><strong>Last Login:</strong> ${new Date(user.last_login).toLocaleString()}</p>` : ''}
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Booking Statistics</h3>
                        <p><strong>Total Bookings:</strong> ${user.booking_count}</p>
                        <p><strong>Total Spent:</strong> $${parseFloat(user.total_spent || 0).toFixed(2)}</p>
                    </div>
                </div>
            `;
            
            document.getElementById('userDetails').innerHTML = details;
            document.getElementById('userModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        function updateUserStatus(userId, status) {
            const action = status === 'suspended' ? 'suspend' : 'activate';
            if (!confirm(`Are you sure you want to ${action} this user?`)) return;

            fetch('../api/update-user-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User status updated successfully');
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

        function deleteUser(userId, email) {
            if (!confirm(`Are you sure you want to delete user "${email}"? This action cannot be undone.`)) return;

            fetch('../api/delete-user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully');
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