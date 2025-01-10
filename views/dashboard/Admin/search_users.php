<?php
session_start();
require_once __DIR__ . '/../../../controllers/UserController.php';

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('Unauthorized');
}

$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$users = getUsers();

// Filter users
if ($searchQuery) {
    $users = array_filter($users, function($user) use ($searchQuery) {
        return stripos($user['username'], $searchQuery) !== false || 
               stripos($user['email'], $searchQuery) !== false ||
               stripos($user['role'], $searchQuery) !== false;
    });
}

// Return only the table HTML
?>
<table class="w-full text-white">
    <thead>
        <tr class="text-left border-b border-white/10">
            <th class="py-3 px-4">Username</th>
            <th class="py-3 px-4">Email</th>
            <th class="py-3 px-4">Role</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr class="user-row border-b border-white/10">
                <td class="py-3 px-4"><?php echo htmlspecialchars($user['username']); ?></td>
                <td class="py-3 px-4"><?php echo htmlspecialchars($user['email']); ?></td>
                <td class="py-3 px-4">
                    <span class="px-2 py-1 rounded-full text-sm <?php echo $user['role'] === 'admin' ? 'bg-purple-500/20 text-purple-300' : 'bg-blue-500/20 text-blue-300'; ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                </td>
                <td class="py-3 px-4">
                    <span class="px-2 py-1 rounded-full text-sm <?php echo $user['status'] === 'Active' ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'; ?>">
                        <?php echo $user['status']; ?>
                    </span>
                </td>
                <td class="py-3 px-4">
                    <div class="flex space-x-2">
                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($user)); ?>)"
                                class="px-3 py-1 bg-blue-500/20 hover:bg-blue-500/30 text-blue-300 rounded-lg transition-colors">
                            Edit
                        </button>
                        <button onclick="openDeleteModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                class="px-3 py-1 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-lg transition-colors">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table> 