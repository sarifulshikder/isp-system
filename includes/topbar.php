<?php
if (!isset($conn)) {
    include_once __DIR__ . '/../config.php';
}

$openTickets = 0;
if (isset($conn)) {
    $res = $conn->query("SELECT COUNT(*) AS total FROM tickets WHERE status='Open'");
    if ($res) {
        $row = $res->fetch_assoc();
        $openTickets = $row['total'];
    }
}

$title = $page_title ?? 'ISP Management';
?>

<div class="topbar">
    <div class="topbar-left">
        <button id="toggleBtn" class="topbar-icon" style="border:none;">
            <i class="fa fa-bars"></i>
        </button>
        <h2 class="topbar-title d-none d-sm-block"><?= htmlspecialchars($title) ?></h2>
    </div>

    <div class="topbar-right">

        <!-- Global Search -->
        <div class="d-none d-md-block" style="position: relative; width: 250px;">
            <div style="display: flex; align-items: center; background: var(--bg-soft); border-radius: 20px; padding: 5px 15px; gap: 8px; border: 1px solid var(--border);">
                <i class="fa fa-search" style="color: var(--text-light); font-size: 14px;"></i>
                <input type="text" id="globalSearch" placeholder="Search..." 
                       style="border: none; background: transparent; outline: none; font-size: 13px; width: 100%; color: var(--text-main);">
            </div>
            <div id="searchResults" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid var(--border); border-radius: 10px; box-shadow: var(--shadow-lg); max-height: 300px; overflow-y: auto; z-index: 1000; margin-top: 5px;">
            </div>
        </div>

        <!-- Quick Add Button -->
        <div class="dropdown">
            <button class="btn btn-primary btn-sm rounded-pill px-3" id="quickAddBtn" style="height: 36px;">
                <i class="fa fa-plus"></i> <span class="d-none d-lg-inline">Quick Add</span>
            </button>
            <div id="quickAddDropdown" class="dropdown-menu">
                <a href="add_user.php" class="dropdown-item"><i class="fa fa-user-plus"></i> Add Customer</a>
                <a href="tickets.php?action=new" class="dropdown-item"><i class="fa fa-ticket"></i> Create Ticket</a>
                <a href="leads.php?action=new" class="dropdown-item"><i class="fa fa-user-plus"></i> Add Lead</a>
                <a href="recharge.php" class="dropdown-item"><i class="fa fa-credit-card"></i> Add Recharge</a>
            </div>
        </div>

        <!-- Notification / Tickets -->
        <a href="<?= $base_path ?? '' ?>tickets.php" class="topbar-icon" style="text-decoration: none;">
            <i class="fa fa-bell"></i>
            <?php if($openTickets > 0): ?>
                <span class="badge"><?= $openTickets ?></span>
            <?php endif; ?>
        </a>

        <!-- Theme Toggle -->
        <button id="themeToggle" class="topbar-icon" style="border:none;">
            <i class="fa fa-moon"></i>
        </button>

        <!-- Profile -->
        <div class="user-menu profile-container" style="position: relative;">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
            </div>
            <span class="d-none d-lg-inline" style="font-weight: 600;">
                <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
            </span>
            <i class="fa fa-chevron-down d-none d-sm-inline" style="font-size: 10px;"></i>

            <div class="profile-dropdown dropdown-menu" style="display: none; position: absolute; top: 115%; right: 0; width: 180px;">
                <a href="<?= $base_path ?? '' ?>change_password.php" class="dropdown-item"><i class="fa fa-key"></i> Change Password</a>
                <a href="<?= $base_path ?? '' ?>logout.php" class="dropdown-item text-danger border-top mt-1"><i class="fa fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
// Consolidated UI Logic
document.addEventListener('DOMContentLoaded', function() {
    // 1. Sidebar Toggle
    const toggleSidebar = () => {
        if (window.innerWidth <= 1024) {
            document.body.classList.toggle('sidebar-show');
        } else {
            document.body.classList.toggle('sidebar-collapsed');
        }
    };

    const toggleBtns = document.querySelectorAll('#toggleBtn, .mobile-header .icon-btn');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            toggleSidebar();
        });
    });

    // Sidebar Overlay
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }

    // 2. Submenu Logic
    window.toggleMenu = function(menuId, btn) {
        const submenu = document.getElementById(menuId);
        if (submenu) submenu.classList.toggle('show');
        if (btn) btn.classList.toggle('expanded');
    };

    // 3. Theme Toggle Logic
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            document.body.classList.add('dark');
            themeToggle.innerHTML = '<i class="fa fa-sun"></i>';
        }

        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            const isDark = document.body.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            themeToggle.innerHTML = isDark ? '<i class="fa fa-sun"></i>' : '<i class="fa fa-moon"></i>';
        });
    }

    // 4. Profile Dropdown
    const profileContainer = document.querySelector('.profile-container');
    const profileDropdown = document.querySelector('.profile-dropdown');
    if (profileContainer && profileDropdown) {
        profileContainer.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', () => profileDropdown.style.display = 'none');
    }

    // 5. Quick Add Dropdown
    const quickAddBtn = document.getElementById('quickAddBtn');
    const quickAddDropdown = document.getElementById('quickAddDropdown');
    if (quickAddBtn && quickAddDropdown) {
        quickAddBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            quickAddDropdown.classList.toggle('show');
        });
        document.addEventListener('click', () => quickAddDropdown.classList.remove('show'));
    }

    // 6. Global Search
    const searchInput = document.getElementById('globalSearch');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;
    if (searchInput && searchResults) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            searchTimeout = setTimeout(() => {
                fetch('api/global_search.php?q=' + encodeURIComponent(query))
                    .then(res => res.text())
                    .then(html => {
                        searchResults.innerHTML = html || '<div class="p-15 text-center text-muted">No results found</div>';
                        searchResults.style.display = 'block';
                    });
            }, 300);
        });
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }

    // 7. Auto-expand sidebar menus based on current page
    const currentPath = window.location.pathname;
    const expandMenu = (pathPart, menuId) => {
        if (currentPath.includes(pathPart)) {
            const submenu = document.getElementById(menuId);
            const toggle = document.querySelector(`[onclick*="${menuId}"]`);
            if (submenu) submenu.classList.add('show');
            if (toggle) toggle.classList.add('expanded');
        }
    };

    const menuMappings = {
        'users.php': 'customer-submenu',
        'online_users.php': 'customer-submenu',
        'add_user.php': 'customer-submenu',
        'tickets.php': 'ticket-submenu',
        'nas.php': 'network-submenu',
        'olt_dashboard.php': 'network-submenu',
        'leads.php': 'lead-submenu',
        'work_diary.php': 'operation-submenu',
        'hotspot/admin/': 'hotspot-submenu',
        'report/': 'report-submenu',
        'admin.php': 'settings-submenu',
        'billing/': 'finance-submenu'
    };

    Object.entries(menuMappings).forEach(([path, menuId]) => expandMenu(path, menuId));
});
</script>
<style>
    /* These specific overrides ensure the topbar looks "premium" and remains functional */
    .topbar .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        padding: 2px 6px;
        font-size: 10px;
    }
    .has-ticket i {
        animation: bellRing 2s infinite;
    }
    @keyframes bellRing {
        0%, 50%, 100% { transform: rotate(0); }
        25% { transform: rotate(10deg); }
        75% { transform: rotate(-10deg); }
    }
    .dropdown-menu.show {
        display: block;
        opacity: 1;
        visibility: visible;
        transform: translateY(5px);
    }
</style>
