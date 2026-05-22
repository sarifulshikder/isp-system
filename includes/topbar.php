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
    <div class="flex items-center gap-4">
        <button id="toggleBtn" class="topbar-icon" style="border:none; cursor:pointer;">
            <i class="fa fa-bars"></i>
        </button>
        <h2 class="card-title d-none d-sm-block"><?= htmlspecialchars($title) ?></h2>
    </div>

    <div class="flex items-center gap-4">
        <!-- Global Search -->
        <div class="d-none d-md-block" style="position: relative; width: 280px;">
            <div class="flex items-center" style="background: var(--bg-soft); border-radius: var(--radius); padding: 0.5rem 1rem; gap: 0.75rem; border: 1px solid var(--border);">
                <i class="fa fa-search" style="color: var(--text-light); font-size: 14px;"></i>
                <input type="text" id="globalSearch" placeholder="Search customers..." 
                       style="border: none; background: transparent; outline: none; font-size: 13px; width: 100%; color: var(--text-main);">
            </div>
            <div id="searchResults" class="dropdown-menu" style="width: 100%; top: 110%; left: 0;"></div>
        </div>

        <!-- Quick Add -->
        <div class="dropdown" style="position:relative;">
            <button class="btn btn-primary btn-sm" id="quickAddBtn">
                <i class="fa fa-plus"></i> <span class="d-none d-lg-inline">Quick Add</span>
            </button>
            <div id="quickAddDropdown" class="dropdown-menu" style="right: 0;">
                <a href="add_user.php" class="dropdown-item"><i class="fa fa-user-plus"></i> Add Customer</a>
                <a href="tickets.php?action=new" class="dropdown-item"><i class="fa fa-ticket"></i> Create Ticket</a>
                <a href="leads.php?action=new" class="dropdown-item"><i class="fa fa-user-plus"></i> Add Lead</a>
                <a href="recharge.php" class="dropdown-item"><i class="fa fa-credit-card"></i> Add Recharge</a>
            </div>
        </div>

        <!-- Notifications -->
        <a href="<?= $base_path ?? '' ?>tickets.php" class="topbar-icon" style="text-decoration: none; position: relative;">
            <i class="fa fa-bell"></i>
            <?php if($openTickets > 0): ?>
                <span class="badge badge-danger" style="position:absolute; top:-5px; right:-5px; padding: 2px 6px; font-size: 10px;"><?= $openTickets ?></span>
            <?php endif; ?>
        </a>

        <!-- Theme Toggle -->
        <button id="themeToggle" class="topbar-icon" style="border:none;">
            <i class="fa fa-moon"></i>
        </button>

        <!-- Profile -->
        <div class="profile-trigger flex items-center gap-2" style="cursor:pointer; padding: 0.25rem 0.5rem; border-radius: var(--radius); transition: var(--transition);">
            <div class="user-avatar" style="width:32px; height:32px; background:var(--primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;">
                <?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?>
            </div>
            <span class="d-none d-lg-inline fw-600" style="font-size: 0.875rem;">
                <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
            </span>
            <i class="fa fa-chevron-down d-none d-sm-inline" style="font-size: 10px; color: var(--text-muted);"></i>

            <div class="profile-dropdown dropdown-menu" style="right: 0; top: 110%; width: 180px;">
                <a href="<?= $base_path ?? '' ?>change_password.php" class="dropdown-item"><i class="fa fa-key"></i> Change Password</a>
                <a href="<?= $base_path ?? '' ?>logout.php" class="dropdown-item text-danger" style="border-top: 1px solid var(--border); margin-top: 0.5rem; padding-top: 0.75rem;">
                    <i class="fa fa-sign-out-alt"></i> Logout
                </a>
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
        const updateIcon = (isDark) => {
            themeToggle.innerHTML = isDark ? '<i class="fa fa-sun"></i>' : '<i class="fa fa-moon"></i>';
        };

        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            document.body.classList.add('dark');
            updateIcon(true);
        } else {
            updateIcon(false);
        }

        themeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            const isDark = document.body.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateIcon(isDark);
        });
    }

    // 4. Profile Dropdown
    const profileDropdown = document.querySelector('.profile-dropdown');
    const profileTriggers = document.querySelectorAll('.profile-trigger');
    
    if (profileDropdown && profileTriggers.length > 0) {
        profileTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                // If clicking a link inside, let it happen
                if (e.target.closest('.dropdown-item')) return;
                
                e.stopPropagation();
                profileDropdown.classList.toggle('show');
            });
        });

        // Close when clicking anywhere else
        document.addEventListener('click', function(e) {
            if (!profileDropdown.contains(e.target) && !Array.from(profileTriggers).some(t => t.contains(e.target))) {
                profileDropdown.classList.remove('show');
            }
        });
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
        display: flex !important;
        flex-direction: column;
        opacity: 1 !important;
        visibility: visible !important;
        transform: translateY(5px) !important;
        z-index: 9999 !important;
    }
    .profile-dropdown {
        min-width: 200px !important;
        padding: 8px 0 !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
    }
</style>
