<?php
/**
 * ToolBox — Homepage
 */
$pageTitle = '';
$pageDesc  = 'ToolBox — All your PDF & image tools in one place. Split, merge, compress, convert and more. Free tools run in your browser.';
require_once __DIR__ . '/includes/header.php';

$toolModel = new Tool($pdo);
$tools = $toolModel->getAll();
?>

<!-- HERO -->
<section class="tb-hero">
    <div class="container text-center">
        <h1 class="mb-3 animate-in">
            Every PDF &amp; Image Tool<br>
            <span class="gradient-text">You'll Ever Need</span>
        </h1>
        <p class="lead mb-4 animate-in" style="animation-delay:.1s">
            Split, merge, compress, convert, and edit — all in one place.<br>
            Free tools run <strong>100% in your browser</strong>. Nothing uploaded. Instant results.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap animate-in" style="animation-delay:.2s">
            <a href="#tools" class="btn tb-btn-primary btn-lg px-4">
                <i class="bi bi-grid me-2"></i>Explore Tools
            </a>
            <a href="<?= BASE_URL ?>/auth/register.php" class="btn tb-btn-outline btn-lg px-4">
                Get Started Free
            </a>
        </div>

        <!-- Trust features -->
        <div class="tb-features mt-5 animate-in" style="animation-delay:.3s">
            <div class="tb-feature-item">
                <div class="tb-feature-icon"><i class="bi bi-lightning-charge"></i></div>
                <div class="small fw-semibold">Instant Processing</div>
                <div class="small text-muted">No upload needed</div>
            </div>
            <div class="tb-feature-item">
                <div class="tb-feature-icon"><i class="bi bi-shield-check"></i></div>
                <div class="small fw-semibold">100% Secure</div>
                <div class="small text-muted">Files never leave device</div>
            </div>
            <div class="tb-feature-item">
                <div class="tb-feature-icon"><i class="bi bi-infinity"></i></div>
                <div class="small fw-semibold">30+ Tools</div>
                <div class="small text-muted">PDF &amp; Image suite</div>
            </div>
            <div class="tb-feature-item">
                <div class="tb-feature-icon"><i class="bi bi-currency-dollar"></i></div>
                <div class="small fw-semibold">From $1/mo</div>
                <div class="small text-muted">Unlock everything</div>
            </div>
        </div>
    </div>
</section>

<!-- TOOLS GRID -->
<section id="tools" class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold mb-2">All Tools</h2>
            <p class="text-muted">Pick a tool and get started in seconds</p>
        </div>

        <!-- Filter pills -->
        <ul class="nav tb-filter-pills justify-content-center mb-4" id="toolFilter">
            <li class="nav-item"><a class="nav-link active" href="#" data-filter="all">All Tools</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-filter="pdf"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-filter="img"><i class="bi bi-image me-1"></i>Image</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-filter="free"><i class="bi bi-unlock me-1"></i>Free</a></li>
        </ul>

        <!-- Grid -->
        <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5" id="toolGrid">
            <?php foreach ($tools as $i => $tool): ?>
            <div class="col tool-col animate-in" 
                 data-category="<?= e($tool['category']) ?>" 
                 data-premium="<?= $tool['is_premium'] ?>"
                 style="animation-delay:<?= ($i * 0.04) ?>s">
                <a href="<?= BASE_URL ?>/tools/<?= e($tool['category']) ?>/<?= e($tool['slug']) ?>.php" class="text-decoration-none">
                    <div class="card tool-card h-100" data-category="<?= e($tool['category']) ?>" data-slug="<?= e($tool['slug']) ?>">
                        <div class="card-body p-4 d-flex flex-column position-relative">
                            <!-- Premium Badge Top Right -->
                            <?php if ($tool['is_premium']): ?>
                                <div class="position-absolute top-0 end-0 mt-3 me-3">
                                    <span class="tb-badge-premium shadow-sm"><i class="bi bi-star-fill me-1"></i>Pro</span>
                                </div>
                            <?php endif; ?>

                            <div class="tool-icon-wrap mb-4">
                                <i class="bi bi-<?= e($tool['icon']) ?>"></i>
                            </div>
                            <h6 class="card-title mb-2"><?= e($tool['name']) ?></h6>
                            <p class="card-text text-muted mb-0"><?= e($tool['description']) ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PRICING -->
<section id="pricing" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Simple Pricing</h2>
            <p class="text-muted">Start free. Upgrade when you need more power.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Free -->
            <div class="col-md-5 col-lg-4">
                <div class="tb-pricing-card h-100">
                    <div class="text-center">
                        <h5 class="fw-bold mb-1">Free</h5>
                        <p class="text-muted small mb-3">For casual use</p>
                        <div class="mb-3">
                            <span class="tb-price">$0</span>
                            <span class="tb-price-period">/forever</span>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Split, Merge, Rotate PDF</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Compress, Resize, Crop Images</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Meme Generator, Flip, Grayscale</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Client-side processing</li>
                        <li class="mb-2 text-muted"><i class="bi bi-x-circle me-2"></i><?= FREE_DAILY_LIMIT ?> uses/tool/day</li>
                        <li class="mb-2 text-muted"><i class="bi bi-x-circle me-2"></i>No premium tools</li>
                    </ul>
                    <a href="<?= BASE_URL ?>/auth/register.php" class="btn tb-btn-outline w-100">Get Started</a>
                </div>
            </div>

            <!-- Monthly -->
            <div class="col-md-5 col-lg-4">
                <div class="tb-pricing-card popular h-100">
                    <div class="text-center">
                        <h5 class="fw-bold mb-1">Pro Monthly</h5>
                        <p class="text-muted small mb-3">Full access, cancel anytime</p>
                        <div class="mb-3">
                            <span class="tb-price">$1</span>
                            <span class="tb-price-period">/month</span>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>All 30+ tools unlocked</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Unlimited usage</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>PDF compress, convert, OCR</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Remove background (AI)</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Image upscale &amp; enhance</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Priority support</li>
                    </ul>
                    <a href="<?= BASE_URL ?>/dashboard/billing.php?plan=monthly" class="btn tb-btn-primary w-100">
                        <i class="bi bi-rocket-takeoff me-2"></i>Subscribe — $1/mo
                    </a>
                </div>
            </div>

            <!-- Yearly -->
            <div class="col-md-5 col-lg-4">
                <div class="tb-pricing-card h-100">
                    <div class="text-center">
                        <h5 class="fw-bold mb-1">Pro Yearly</h5>
                        <p class="text-muted small mb-3">Best value — save 25%</p>
                        <div class="mb-3">
                            <span class="tb-price">$9</span>
                            <span class="tb-price-period">/year</span>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Everything in Pro Monthly</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Save 25% vs monthly</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>One payment, full year</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>All future tools included</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Priority support</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Cancel anytime</li>
                    </ul>
                    <a href="<?= BASE_URL ?>/dashboard/billing.php?plan=yearly" class="btn tb-btn-outline w-100">
                        Subscribe — $9/yr
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tool Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pills = document.querySelectorAll('#toolFilter .nav-link');
    const cols  = document.querySelectorAll('.tool-col');

    pills.forEach(pill => {
        pill.addEventListener('click', function(e) {
            e.preventDefault();
            pills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            cols.forEach(col => {
                const cat = col.dataset.category;
                const premium = col.dataset.premium === '1';
                let show = false;

                if (filter === 'all') show = true;
                else if (filter === 'free') show = !premium;
                else show = cat === filter;

                col.style.display = show ? '' : 'none';
                if (show) {
                    col.style.opacity = '0';
                    col.style.transform = 'translateY(10px)';
                    setTimeout(() => {
                        col.style.transition = 'all 0.3s ease';
                        col.style.opacity = '1';
                        col.style.transform = 'translateY(0)';
                    }, 30);
                }
            });
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
