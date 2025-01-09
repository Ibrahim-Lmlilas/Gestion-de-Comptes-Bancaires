<button id="mobile-menu-button" class="fixed top-4 left-4 z-[9999] p-2 rounded-lg bg-white/10 backdrop-blur-lg md:hidden">
    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path id="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
</button>

<div id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white/10 backdrop-blur-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-[9999]">
    <div class="flex items-center justify-center h-20 border-b border-white/10">
        <h1 class="text-2xl font-bold text-white">My Banking</h1>
    </div>
    <nav class="mt-6">
        <div class="px-6 py-4">
            <span class="text-blue-200 text-sm">Menu</span>
        </div>
        <a href="user.php" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
            Dashboard
        </a>
        <a href="transfer.php" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
            Transfer Money
        </a>
        <a href="transactions.php" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
            Transactions
        </a>
        <a href="profile.php" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
            Profile
        </a>
        <a href="../../auth/logout.php" class="block px-6 py-3 text-white hover:text-blue-200 sidebar-link">
            Logout
        </a>
    </nav>
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