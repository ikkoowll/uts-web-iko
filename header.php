<?php
// PHP block to ensure user is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - SIM HIMATIF' : 'SIM HIMATIF'; ?></title>
    <link rel="stylesheet" href="style.css?v=1.4">
    <?php if (isset($load_chartjs) && $load_chartjs): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
</head>
<body>

    <!-- Top Navbar -->
    <div class="navbar">
        <div style="display: flex; align-items: center; gap: 16px;">
            <!-- Hamburger Menu Button -->
            <button id="sidebar-toggle" class="hamburger-btn" aria-label="Toggle Sidebar">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="nav-brand">SIM HIMATIF</div>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="logout.php" style="background: var(--danger-gradient) !important; border: none !important; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);">Logout</a>
        </div>
    </div>

    <!-- Sidebar Overlay for mobile -->
    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <!-- Sidebar Navigation -->
    <div id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <h3>SIM HIMATIF</h3>
            <p>Sistem Informasi Manajemen</p>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="<?php echo ($active_menu ?? '') === 'dashboard' ? 'active-menu-item' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="menu-icon"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="anggota.php" class="<?php echo ($active_menu ?? '') === 'anggota' ? 'active-menu-item' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="menu-icon"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Anggota Internal
                </a>
            </li>
            <li>
                <a href="kinerja.php" class="<?php echo ($active_menu ?? '') === 'kinerja' ? 'active-menu-item' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="menu-icon"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    Kinerja Proker
                </a>
            </li>
            <li>
                <a href="keuangan.php" class="<?php echo ($active_menu ?? '') === 'keuangan' ? 'active-menu-item' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="menu-icon"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    Analisis Finansial
                </a>
            </li>
            <li class="menu-divider">MANAJEMEN KAS & SPONSOR</li>
            <li>
                <a href="pengeluaran.php" class="<?php echo ($active_menu ?? '') === 'pengeluaran' ? 'active-menu-item' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="menu-icon"><rect x="2" y="4" width="20" height="16" rx="2" ry="2"></rect><line x1="12" y1="4" x2="12" y2="20"></line><line x1="2" y1="12" x2="22" y2="12"></line></svg>
                    Pengeluaran Kas
                </a>
            </li>
            <li>
                <a href="sponsor.php" class="<?php echo ($active_menu ?? '') === 'sponsor' ? 'active-menu-item' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="menu-icon"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    Kelola Sponsor
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content Wrapper -->
    <div id="main-content-wrapper" class="main-content-wrapper">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarOverlay = document.getElementById('sidebar-overlay');

            // Load persistent collapsed state
            const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (isCollapsed && window.innerWidth > 768) {
                document.body.classList.add('sidebar-collapsed');
            }

            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('open');
                    sidebarOverlay.classList.toggle('open');
                } else {
                    document.body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
                }
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('open');
            });
        });
    </script>
