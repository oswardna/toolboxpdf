<?php
/**
 * ToolBox — Admin Sidebar
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="tb-admin-sidebar">
    <div class="sidebar-header px-4 py-4 d-flex align-items-center">
        <img src="<?= BASE_URL ?>/images/logo.png" alt="Logo" style="height: 28px; width: auto;" class="me-2">
        <h5 class="mb-0 fw-bold">Admin</h5>
    </div>
    
    <nav class="nav flex-column">
        <a class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/index.php">
            <i class="bi bi-speedometer2 me-3"></i>Dashboard
        </a>
        <a class="nav-link <?= $current_page === 'users.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/users.php">
            <i class="bi bi-people me-3"></i>Users
        </a>
        <a class="nav-link <?= $current_page === 'tools.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/tools.php">
            <i class="bi bi-grid me-3"></i>Tools Management
        </a>
        <a class="nav-link <?= $current_page === 'payments.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/payments.php">
            <i class="bi bi-credit-card me-3"></i>Payments
        </a>
        
        <hr class="mx-4 my-3 border-secondary opacity-25">
        
        <a class="nav-link <?= $current_page === 'profile.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/profile.php">
            <i class="bi bi-person-circle me-3"></i>Admin Profile
        </a>
        <a class="nav-link <?= $current_page === 'settings.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/settings.php">
            <i class="bi bi-gear me-3"></i>System Settings
        </a>
        
        <div class="mt-auto px-3 pb-3">
            <div class="bg-dark rounded-4 p-3 text-center border border-secondary shadow-sm">
                <p class="small text-muted mb-3">Return to public site</p>
                <a href="<?= BASE_URL ?>/" class="btn tb-btn-primary btn-sm w-100 fw-bold">
                    <i class="bi bi-box-arrow-up-right me-2"></i>View Website
                </a>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-outline-danger border-0 btn-sm w-100 mt-2">
                    <i class="bi bi-box-arrow-left me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>
</div>

<style>
.tb-admin-sidebar {
    width: 280px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: var(--tb-bg-secondary);
    border-right: 1px solid var(--tb-border);
    z-index: 1050;
    display: flex;
    flex-direction: column;
}

.tb-admin-sidebar .nav-link {
    color: var(--tb-text-muted);
    padding: 0.8rem 1.5rem;
    font-weight: 500;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.tb-admin-sidebar .nav-link:hover {
    color: var(--tb-primary);
    background: var(--tb-primary-light);
}

.tb-admin-sidebar .nav-link.active {
    color: var(--tb-primary);
    background: var(--tb-primary-light);
    border-left-color: var(--tb-primary);
}

.tb-admin-sidebar .nav-link i {
    font-size: 1.1rem;
    width: 20px;
    display: inline-block;
}

.tb-admin-main {
    margin-left: 280px;
    padding: 2rem;
    min-height: 100vh;
}

@media (max-width: 991.98px) {
    .tb-admin-sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    .tb-admin-main {
        margin-left: 0;
    }
}
</style>
