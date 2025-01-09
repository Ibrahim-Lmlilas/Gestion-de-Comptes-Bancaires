
<?php
session_start();
require_once (__DIR__ . '/../../../controllers/vusers.php');
// echo __DIR__;
// var_dump( $result[0]);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <style>
        :root {
            --primary-orange: #FF7043;
            --primary-purple: #9C27B0;
            --light-purple: #BA68C8;
        }
        .sidebar-link {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .sidebar-link:hover {
            background: rgba(255, 112, 67, 0.1);
            transform: translateX(10px);
        }
        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .input-field {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(156, 39, 176, 0.1);
            border: 2px solid rgba(186, 104, 200, 0.2);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .input-field:focus {
            background: rgba(156, 39, 176, 0.2);
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 4px rgba(255, 112, 67, 0.1), 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        .input-label {
            position: absolute;
            left: 1rem;
            top: 1rem;
            padding: 0 0.5rem;
            color: #E1BEE7;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }
        .input-field:focus + .input-label,
        .input-field:not(:placeholder-shown) + .input-label {
            transform: translateY(-2.4rem) scale(0.85);
            color: var(--primary-orange);
            background: linear-gradient(180deg,rgb(133, 52, 147) 50%, rgba(156, 39, 176, 0.1) 50%);
        }
        .input-field::placeholder {
            color: transparent;
        }
        .btn-primary {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            z-index: 1;
            background: linear-gradient(135deg, var(--primary-orange) 0%, #F4511E 100%);
        }
        .btn-primary:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.6s ease;
            z-index: -1;
        }
        .btn-primary:hover:before {
            transform: translate(-50%, -50%) scale(1);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 112, 67, 0.4);
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0% { transform: translate(0, 0px); }
            50% { transform: translate(0, 15px); }
            100% { transform: translate(0, -0px); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-orange-500 via-purple-600 to-purple-800">
    <!-- Mobile Menu Button -->
    <button id="mobile-menu-button" class="fixed top-4 left-4 z-[9999] p-2 rounded-lg bg-white/10 backdrop-blur-lg md:hidden">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white/10 backdrop-blur-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-[9999]">
        <div class="flex items-center justify-center h-20 border-b border-white/10">
            <h1 class="text-2xl font-bold text-white">Bank Admin</h1>
        </div>
        <nav class="mt-6">
            <div class="px-6 py-4">
                <span class="text-orange-200 text-sm">Menu</span>
            </div>
            <a href="#" class="block px-6 py-3 text-white hover:text-orange-200 sidebar-link">
                Dashboard
            </a>
            <a href="#" class="block px-6 py-3 text-white hover:text-orange-200 sidebar-link">
                Users
            </a>
            <a href="#" class="block px-6 py-3 text-white hover:text-orange-200 sidebar-link">
                Transactions
            </a>
            <a href="#" class="block px-6 py-3 text-white hover:text-orange-200 sidebar-link">
                Settings
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="md:ml-64 p-4 md:p-8">
        <!-- Header -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 md:p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Clients Management</h2>
                    <p class="text-white/80 text-lg">Total Clients: <?php echo count($result); ?></p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="openAddClientModal()" class="bg-orange-500 text-white px-4 md:px-6 py-2 rounded-lg hover:bg-orange-600 transition-all duration-300 transform hover:scale-105">
                        Add New Client
                    </button>
                </div>
            </div>
        </div>

        <!-- Clients Table -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 md:p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-white">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">Account Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-orange-200 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                    <?php foreach($result as $user ): ?>
                        <!-- Sample Client 1 -->
                        <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="<?php echo $user['profile_pic'] . urlencode($user['username']); ?>" alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium"><?php echo htmlspecialchars($user['username']); ?></div>
                                        <div class="text-sm text-orange-300"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-10 py-4 whitespace-nowrap">
                                <div class="text-sm"><?php echo $user['account_id']?></div>
                                
                            </td>
                            <td>
                            <div class="text-sm text"><?php echo $user['balance']?></div>
                            </td>

                         
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <?php echo $user['account_type'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-indigo-300 hover:text-indigo-100 mr-3">Edit</button>
                                <button class="text-red-300 hover:text-red-100">Delete</button>
                            </td>
                        </tr>

                        <!-- Sample Client 2 -->
                        <!-- <tr class="hover:bg-white/5">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=Jane+Smith" alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium">Jane Smith</div>
                                        <div class="text-sm text-orange-200">jane@example.com</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">9876-5432-1098-7654</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">$8,750.00</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button class="text-indigo-300 hover:text-indigo-100 mr-3">Edit</button>
                                <button class="text-red-300 hover:text-red-100">Delete</button>
                            </td>
                        </tr> -->
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Client Modal -->
    <div id="addClientModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[10000] flex items-center justify-center p-4">
        <div class="bg-purple-900/90 backdrop-blur-sm p-8 rounded-xl shadow-2xl w-full max-w-4xl flex flex-col md:flex-row gap-8 relative">
            <!-- Close Button -->
            <button onclick="closeAddClientModal()" class="absolute top-4 right-4 text-white/60 hover:text-white transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <!-- Left Side - Bank Icon and Welcome Message -->
            <div class="flex-1 flex flex-col items-center justify-center">
                <div class="bank-icon bg-orange-400 rounded-full p-8 w-32 h-32 mx-auto mb-8 shadow-lg floating">
                    <div class="text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m16-11v11m-8-11v11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-white mb-4 text-center">Add New Client</h1>
                <p class="text-indigo-200 text-center">Create a new bank account for your client. It only takes a few minutes!</p>
            </div>

            <!-- Right Side - Add Client Form -->
            <div class="flex-1">
                <form class="space-y-6">
                    <div class="input-group">
                        <input type="text" class="input-field" id="fullname" name="username" placeholder="Full Name">
                        <label for="fullname" class="input-label">Full Name</label>
                    </div>
                    <div class="input-group">
                        <input type="email" class="input-field" id="email" name="email" placeholder="Email">
                        <label for="email" class="input-label">Email</label>
                    </div>
                    <div class="input-group">
                        <input type="number" class="input-field" id="current_balance" name="current_balance" placeholder="Initial Current account Balance">
                        <label for="current_balance" class="input-label">Initial Current Balance</label>
                    </div>
                    <div class="input-group">
                        <input type="number" class="input-field" id="savings_balance" name="savings_balance" placeholder="Initial Savings account Balance">
                        <label for="savings_balance" class="input-label">Initial Savings Balance</label>
                    </div>
                    <div class="input-group">
                        <input type="password" class="input-field" id="password" name="password" placeholder="Password">
                        <label for="password" class="input-label">Password</label>
                    </div>
                    <div class="mt-8">
                        <button type="submit" name="register" class="btn-primary w-full py-3 px-4 rounded-lg text-white font-medium">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const sidebar = document.getElementById('sidebar');
            const menuIcon = document.getElementById('menu-icon');
            
            mobileMenuButton.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                if (sidebar.classList.contains('-translate-x-full')) {
                    menuIcon.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
                } else {
                    menuIcon.setAttribute('d', 'M6 18L18 6M6 6l12 12');
                }
            });

            document.addEventListener('click', (e) => {
                if (window.innerWidth < 768) {
                    if (!sidebar.contains(e.target) && !mobileMenuButton.contains(e.target)) {
                        sidebar.classList.add('-translate-x-full');
                        menuIcon.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
                    }
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('-translate-x-full');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        });

        // Modal functionality
        function openAddClientModal() {
            document.getElementById('addClientModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddClientModal() {
            document.getElementById('addClientModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
</body>
</html>
