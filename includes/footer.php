<?php
/**
 * ToolBox — Footer Include
 */
?>
</main>

<!-- Footer -->
<footer class="tb-footer mt-auto">
    <div class="container">
        <div class="row g-4 py-5">
            <div class="col-lg-4 mb-3">
                <h5 class="fw-bold mb-3"><img src="<?= BASE_URL ?>/images/logo.png" alt="Logo" style="height: 28px; width: auto;" class="me-2"></h5>
                <p class="text-muted small"><?= APP_TAGLINE ?>. Fast, secure, and easy to use. Client-side tools run entirely in your browser — your files never leave your device.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-semibold mb-3">PDF Tools</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL ?>/tools/pdf/pdf-split.php" class="text-muted text-decoration-none">Split PDF</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/tools/pdf/pdf-merge.php" class="text-muted text-decoration-none">Merge PDF</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/tools/pdf/pdf-compress.php" class="text-muted text-decoration-none">Compress PDF</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/tools/pdf/pdf-to-jpg.php" class="text-muted text-decoration-none">PDF to JPG</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-semibold mb-3">Image Tools</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL ?>/tools/img/img-compress.php" class="text-muted text-decoration-none">Compress Image</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/tools/img/img-resize.php" class="text-muted text-decoration-none">Resize Image</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/tools/img/img-crop.php" class="text-muted text-decoration-none">Crop Image</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/tools/img/img-remove-bg.php" class="text-muted text-decoration-none">Remove BG</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-semibold mb-3">Company</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL ?>/#pricing" class="text-muted text-decoration-none">Pricing</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/auth/login.php" class="text-muted text-decoration-none">Login</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/auth/register.php" class="text-muted text-decoration-none">Sign Up</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-semibold mb-3">Security</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2 text-muted"><i class="bi bi-shield-check me-1"></i>Files auto-deleted</li>
                    <li class="mb-2 text-muted"><i class="bi bi-lock me-1"></i>SSL encrypted</li>
                    <li class="mb-2 text-muted"><i class="bi bi-browser-chrome me-1"></i>Client-side processing</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary">
        <div class="d-flex flex-wrap justify-content-between align-items-center py-3">
            <p class="small text-muted mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
            <p class="small text-muted mb-0">Made with <i class="bi bi-heart-fill text-danger"></i></p>
        </div>
    </div>
</footer>

<!-- Upgrade Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content tb-modal">
            <div class="modal-body text-center p-5">
                <div class="mb-3"><i class="bi bi-star-fill text-warning" style="font-size:3rem"></i></div>
                <h4 class="fw-bold mb-2">Upgrade to Pro</h4>
                <p class="text-muted mb-4">Unlock all premium tools with unlimited usage.</p>
                <div class="d-flex gap-3 justify-content-center mb-3">
                    <div class="tb-price-mini">
                        <div class="fw-bold">$1<small>/mo</small></div>
                    </div>
                    <div class="tb-price-mini tb-price-popular">
                        <div class="fw-bold">$9<small>/yr</small></div>
                        <small class="text-success">Save 25%</small>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/dashboard/billing.php" class="btn tb-btn-primary btn-lg w-100">
                    <i class="bi bi-rocket-takeoff me-2"></i>Upgrade Now
                </a>
                <button type="button" class="btn btn-link text-muted mt-2" data-bs-dismiss="modal">Maybe later</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
