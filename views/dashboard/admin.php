<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <style>
        :root {
            --primary-orange: #FF7043;
            --primary-purple: #9C27B0;
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
            background: var(--primary-orange);
            transition: width 0.3s ease;
        }
        .sidebar-link:hover::after {
            width: 100%;
        }
        .sidebar-link:hover {
            background: rgba(255, 112, 67, 0.1);
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
        .stat-icon {
            transition: all 0.3s ease;
        }
        .card:hover .stat-icon {
            transform: scale(1.1) rotate(10deg);
        }
        .activity-item {
            transition: all 0.3s ease;
            animation: slideIn 0.5s backwards;
        }
        .activity-item:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.1);
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-orange-500 via-purple-600 to-purple-800">
    <!-- Sidebar -->
    <div class="fixed left-0 top-0 w-64 h-full bg-white/10 backdrop-blur-lg">
        <div class="flex items-center justify-center h-20 border-b border-white/10">
            <h1 class="text-2xl font-bold text-white floating">Bank Admin</h1>
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
    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-white pulse">Welcome Back</h2>
                    <p class="text-orange-200">Your dashboard is looking great today</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition-all duration-300 transform hover:scale-105">
                        New Transaction
                    </button>
                    <div class="relative">
                        <img src="https://ui-avatars.com/api/?name=Admin&background=FF7043&color=fff" 
                             alt="Admin" 
                             class="w-10 h-10 rounded-full cursor-pointer border-2 border-orange-400 hover:border-white transition-all duration-300">
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Card 1 -->
            <div style="animation-delay: 0.1s" class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-200">Total Users</p>
                        <h3 class="text-2xl font-bold text-white">1,482</h3>
                    </div>
                    <div class="stat-icon bg-orange-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-green-400 text-sm font-semibold">↑ 12%</span>
                    <span class="text-orange-200 text-sm"> from last month</span>
                </div>
            </div>

            <!-- Card 2 -->
            <div style="animation-delay: 0.2s" class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-200">Total Transactions</p>
                        <h3 class="text-2xl font-bold text-white">$42,589</h3>
                    </div>
                    <div class="stat-icon bg-green-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-green-400 text-sm font-semibold">↑ 8.5%</span>
                    <span class="text-orange-200 text-sm"> from last month</span>
                </div>
            </div>

            <!-- Card 3 -->
            <div style="animation-delay: 0.3s" class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-200">Active Accounts</p>
                        <h3 class="text-2xl font-bold text-white">892</h3>
                    </div>
                    <div class="stat-icon bg-blue-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-green-400 text-sm font-semibold">↑ 4.2%</span>
                    <span class="text-orange-200 text-sm"> from last month</span>
                </div>
            </div>

            <!-- Card 4 -->
            <div style="animation-delay: 0.4s" class="card bg-white/10 backdrop-blur-md rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-200">Support Tickets</p>
                        <h3 class="text-2xl font-bold text-white">28</h3>
                    </div>
                    <div class="stat-icon bg-red-500/20 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-red-400 text-sm font-semibold">↓ 2.5%</span>
                    <span class="text-orange-200 text-sm"> from last month</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white/10 backdrop-blur-md rounded-xl p-6">
            <h4 class="text-xl font-bold text-white mb-6">Recent Activity</h4>
            <div class="space-y-6">
                <!-- Activity Item 1 -->
                <div style="animation-delay: 0.5s" class="activity-item flex items-center justify-between border-b border-white/10 pb-4 rounded-lg p-2">
                    <div class="flex items-center space-x-4">
                        <div class="bg-orange-500/20 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white">New Transaction</p>
                            <p class="text-sm text-orange-200">John Doe sent $250 to Jane Smith</p>
                        </div>
                    </div>
                    <span class="text-sm text-orange-200">2 minutes ago</span>
                </div>

                <!-- Activity Item 2 -->
                <div style="animation-delay: 0.6s" class="activity-item flex items-center justify-between border-b border-white/10 pb-4 rounded-lg p-2">
                    <div class="flex items-center space-x-4">
                        <div class="bg-green-500/20 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white">Account Verified</p>
                            <p class="text-sm text-orange-200">Sarah Johnson's account verified</p>
                        </div>
                    </div>
                    <span class="text-sm text-orange-200">5 hours ago</span>
                </div>

                <!-- Activity Item 3 -->
                <div style="animation-delay: 0.7s" class="activity-item flex items-center justify-between rounded-lg p-2">
                    <div class="flex items-center space-x-4">
                        <div class="bg-red-500/20 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-white">Failed Transfer</p>
                            <p class="text-sm text-orange-200">Transfer to Mike Brown failed</p>
                        </div>
                    </div>
                    <span class="text-sm text-orange-200">1 day ago</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize animations when page loads
        document.addEventListener('DOMContentLoaded', () => {
            // Animate cards sequentially
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // Animate activity items sequentially
            const activities = document.querySelectorAll('.activity-item');
            activities.forEach((activity, index) => {
                activity.style.animationDelay = `${(index + 5) * 0.1}s`;
            });
        });
    </script>
</body>
</html>
