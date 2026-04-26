<?php
/**
 * ToolBox — Navigation Bar
 */
// Hide on admin pages
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) return;
?>
<nav class="navbar navbar-expand-lg tb-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand tb-brand" href="<?= BASE_URL ?>/">
            <img src="<?= BASE_URL ?>/images/logo.png" alt="Logo" class="tb-brand-icon" style="height: 32px; width: auto;">
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF Tools
                    </a>
                    <ul class="dropdown-menu tb-dropdown">
                        <li><h6 class="dropdown-header">Free Tools</h6></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/pdf/pdf-split.php"><i class="bi bi-scissors me-2"></i>Split PDF</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/pdf/pdf-merge.php"><i class="bi bi-union me-2"></i>Merge PDF</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/pdf/pdf-rotate.php"><i class="bi bi-arrow-clockwise me-2"></i>Rotate Pages</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Pro Tools <span class="badge bg-warning text-dark ms-1">PRO</span></h6></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/pdf/pdf-compress.php"><i class="bi bi-file-earmark-zip me-2"></i>Compress PDF</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/pdf/pdf-to-jpg.php"><i class="bi bi-file-earmark-image me-2"></i>PDF to JPG</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/pdf/word-to-pdf.php"><i class="bi bi-file-earmark-word me-2"></i>Word to PDF</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/pdf/pdf-protect.php"><i class="bi bi-shield-lock me-2"></i>Protect PDF</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/pdf/pdf-ocr.php"><i class="bi bi-textarea-t me-2"></i>OCR Text</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-primary fw-semibold" href="<?= BASE_URL ?>/#tools"><i class="bi bi-grid me-2"></i>View All →</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-image me-1"></i>Image Tools
                    </a>
                    <ul class="dropdown-menu tb-dropdown">
                        <li><h6 class="dropdown-header">Free Tools</h6></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/img/img-compress.php"><i class="bi bi-file-earmark-zip me-2"></i>Compress</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/img/img-resize.php"><i class="bi bi-aspect-ratio me-2"></i>Resize</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/img/img-crop.php"><i class="bi bi-crop me-2"></i>Crop</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/img/img-meme.php"><i class="bi bi-emoji-laughing me-2"></i>Meme</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/img/img-flip.php"><i class="bi bi-symmetry-vertical me-2"></i>Flip</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/img/img-grayscale.php"><i class="bi bi-circle-half me-2"></i>Grayscale</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Pro Tools <span class="badge bg-warning text-dark ms-1">PRO</span></h6></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/img/img-convert.php"><i class="bi bi-arrow-left-right me-2"></i>Convert</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/img/img-remove-bg.php"><i class="bi bi-eraser me-2"></i>Remove BG</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>/tools/img/img-enhance.php"><i class="bi bi-stars me-2"></i>Enhance</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-primary fw-semibold" href="<?= BASE_URL ?>/#tools"><i class="bi bi-grid me-2"></i>View All →</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/#pricing"><i class="bi bi-tag me-1"></i>Pricing</a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <?php if ($auth->isLoggedIn()): ?>
                    <?php if ($auth->isPro()): ?>
                        <li class="nav-item me-2 d-flex align-items-center">
                            <span class="badge tb-badge-pro"><i class="bi bi-star-fill me-1"></i>PRO</span>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= e($_SESSION['user_name'] ?? 'Account') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end tb-dropdown">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/dashboard/"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/dashboard/billing.php"><i class="bi bi-credit-card me-2"></i>Billing</a></li>
                            <?php if ($auth->isAdmin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/"><i class="bi bi-gear me-2"></i>Admin</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/auth/login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn tb-btn-primary btn-sm px-3" href="<?= BASE_URL ?>/auth/register.php">Get Started Free</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
