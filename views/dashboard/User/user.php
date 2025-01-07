<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-blue: #3B82F6;
            --primary-indigo: #6366F1;
        }
        .sidebar-link {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .sidebar-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 2px;
            width: 0;
            background: var(--primary-blue);
            transition: width 0.3s ease;
        }
        .sidebar-link:hover::after {
            width: 100%;
        }
        .sidebar-link:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateX(10px);
        }
        .card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center;
            animation: cardAppear 0.6s backwards;
        }
        .card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 via-indigo-600 to-indigo-800">
    <!-- Mobile Menu Button -->
    <button id="mobile-menu-button" class="fixed top-4 left-4 z-[9999] p-2 rounded-lg bg-white/10 backdrop-blur-lg md:hidden">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white/10 backdrop-blur-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-[9999]">
        <div class="flex items-center justify-center h-20 border-b border-white/10">
            <h1 class="text-2xl font-bold text-white">My Banking</h1>
        </div>
        <nav class="mt-6">
            <div class="px-6 py-4">
                <span class="text-blue-200 text-sm">Menu</span>
            </div>
            <a href="#" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
                Dashboard
            </a>
            <a href="#" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
                Transactions
            </a>
            <a href="#" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
                Transfer Money
            </a>
            <a href="#" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
                Profile
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="md:ml-64 p-4 md:p-8">
        <!-- Header -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-4 md:p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Welcome Back, John!</h2>
                    <p class="text-white/80 text-lg">Here's your financial summary</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="bg-blue-500 text-white px-4 md:px-6 py-2 rounded-lg hover:bg-blue-600 transition-all duration-300 transform hover:scale-105">
                        New Transfer
                    </button>
                    <div class="relative">
                        <img src="https://ui-avatars.com/api/?name=John+Doe&background=3B82F6&color=fff" 
                             alt="User" 
                             class="w-10 h-10 rounded-full cursor-pointer border-2 border-blue-400 hover:border-white transition-all duration-300">
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Balance Card -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 mb-8">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div>
                    <p class="text-blue-200 mb-2">Total Balance</p>
                    <h3 class="text-3xl font-bold text-white">$24,500.00</h3>
                    <p class="text-white/60 mt-1">Available Balance</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-4">
                    <button class="flex items-center space-x-2 bg-blue-500/20 text-white px-4 py-2 rounded-lg hover:bg-blue-500/30 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add Money</span>
                    </button>
                    <button class="flex items-center space-x-2 bg-blue-500/20 text-white px-4 py-2 rounded-lg hover:bg-blue-500/30 transition-all duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        <span>Withdraw</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-8">
            <!-- Income Card -->
            <div style="animation-delay: 0.1s" class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-200">Monthly Income</p>
                        <h3 class="text-2xl font-bold text-white">$3,240</h3>
                    </div>
                    <div class="bg-green-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-green-400 text-sm font-semibold">↑ 8.2%</span>
                    <span class="text-blue-200 text-sm"> from last month</span>
                </div>
            </div>

            <!-- Expenses Card -->
            <div style="animation-delay: 0.2s" class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-200">Monthly Expenses</p>
                        <h3 class="text-2xl font-bold text-white">$1,890</h3>
                    </div>
                    <div class="bg-red-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-red-400 text-sm font-semibold">↓ 3.1%</span>
                    <span class="text-blue-200 text-sm"> from last month</span>
                </div>
            </div>

            <!-- Savings Card -->
            <div style="animation-delay: 0.3s" class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-200">Total Savings</p>
                        <h3 class="text-2xl font-bold text-white">$12,580</h3>
                    </div>
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-green-400 text-sm font-semibold">↑ 12.4%</span>
                    <span class="text-blue-200 text-sm"> from last month</span>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6">
            <h4 class="text-xl font-bold text-white mb-6">Recent Transactions</h4>
            <div class="space-y-6">
                <!-- Transaction Item 1 -->
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <div class="flex items-center space-x-4">
                        <div class="bg-green-500/20 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-medium">Salary Deposit</p>
                            <p class="text-blue-200 text-sm">From Company Inc</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-green-400 font-medium">+$3,240.00</p>
                        <p class="text-blue-200 text-sm">Today</p>
                    </div>
                </div>

                <!-- Transaction Item 2 -->
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <div class="flex items-center space-x-4">
                        <div class="bg-red-500/20 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-medium">Online Shopping</p>
                            <p class="text-blue-200 text-sm">Amazon.com</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-red-400 font-medium">-$128.00</p>
                        <p class="text-blue-200 text-sm">Yesterday</p>
                    </div>
                </div>

                <!-- Transaction Item 3 -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-500/20 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-medium">Money Transfer</p>
                            <p class="text-blue-200 text-sm">To Sarah Smith</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-red-400 font-medium">-$450.00</p>
                        <p class="text-blue-200 text-sm">2 days ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
    </script>
</body>
</html>
