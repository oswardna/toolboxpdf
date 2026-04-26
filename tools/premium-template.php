<?php
/**
 * ToolBox — Premium Tool Page Template
 *
 * Each tool file includes this file and calls renderPremiumTool() with its config.
 * The header is included inside the function so $pageTitle/$pageDesc are set first.
 */

function renderPremiumTool(array $toolConfig): void {
    global $pdo, $auth;

    // Set page meta before including the header so <title> is correct
    $pageTitle = $toolConfig['title'];
    $pageDesc  = $toolConfig['description'];
    require_once __DIR__ . '/../includes/header.php';
    
    $isPro = $auth->isPro();
    $toolSlug = $toolConfig['slug'];
    $category = $toolConfig['category'] ?? 'pdf';

    // Fetch tool status from DB
    $stmt = $pdo->prepare("SELECT is_premium, is_active FROM tools WHERE slug = ?");
    $stmt->execute([$toolSlug]);
    $dbTool = $stmt->fetch();
    
    // Default to premium if not found, but respect DB if it is
    $isActuallyPremium = $dbTool ? (bool)$dbTool['is_premium'] : true;
    $isActive = $dbTool ? (bool)$dbTool['is_active'] : true;

    // If disabled, show message and stop
    if (!$isActive && !$auth->isAdmin()) {
        echo "<div class='container py-5 text-center'><h3>This tool is currently disabled.</h3><a href='".BASE_URL."' class='btn tb-btn-primary mt-3'>Back to Home</a></div>";
        require_once __DIR__ . '/../includes/footer.php';
        return;
    }

    $accept = $toolConfig['accept'] ?? '.pdf';
    $icon = $toolConfig['icon'] ?? 'gear';
?>

<div class="container py-5" style="max-width:900px">
    <div class="tb-tool-header">
        <div class="tool-page-icon <?= $category ?>">
            <i class="bi bi-<?= htmlspecialchars($icon) ?>"></i>
        </div>
        <h1><?= htmlspecialchars($toolConfig['title']) ?></h1>
        <p class="tool-desc"><?= htmlspecialchars($toolConfig['description']) ?></p>
        <?php if ($isActuallyPremium): ?>
        <span class="tb-badge-premium"><i class="bi bi-star-fill me-1"></i>Pro</span>
        <?php endif; ?>
    </div>

    <?php if ($isActuallyPremium && !$isPro): ?>
    <!-- Upgrade prompt for non-Pro users -->
    <div class="tb-dash-card text-center py-5" style="border-color:var(--tb-primary);background:rgba(124,58,237,0.05)">
        <i class="bi bi-lock-fill text-warning" style="font-size:3rem"></i>
        <h4 class="fw-bold mt-3 mb-2">Pro Feature</h4>
        <p class="text-muted mb-4">This tool requires a Pro subscription. Upgrade for just $1/month to unlock all 30+ tools.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= BASE_URL ?>/dashboard/billing.php?plan=monthly" class="btn tb-btn-primary btn-lg">
                <i class="bi bi-rocket-takeoff me-2"></i>Upgrade — $1/mo
            </a>
            <a href="<?= BASE_URL ?>/dashboard/billing.php?plan=yearly" class="btn tb-btn-outline btn-lg">
                $9/yr — Save 25%
            </a>
        </div>
        <?php if (!$auth->isLoggedIn()): ?>
        <p class="text-muted small mt-3 mb-0">
            <a href="<?= BASE_URL ?>/auth/login.php">Sign in</a> or 
            <a href="<?= BASE_URL ?>/auth/register.php">create an account</a> first.
        </p>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <!-- Upload area for Pro users -->
    <div id="drop-zone" class="tb-drop-zone text-center mt-4">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
            <label for="file-input" class="tb-btn-huge mb-0">
                Select file
            </label>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; background-color: #e5322d; border-color: #e5322d;" onclick="ToolBoxUI.showToast('Google Drive integration coming soon', 'info')"><i class="bi bi-google"></i></button>
                <button type="button" class="btn btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; background-color: #e5322d; border-color: #e5322d;" onclick="ToolBoxUI.showToast('Dropbox integration coming soon', 'info')"><i class="bi bi-dropbox"></i></button>
            </div>
        </div>
        <div class="tb-drop-text">or drop files here</div>
        <input type="file" id="file-input" accept="<?= htmlspecialchars($accept) ?>" class="d-none">
    </div>

    <div id="file-info" class="d-none mt-3">
        <div class="tb-dash-card d-flex justify-content-between align-items-center">
            <div><i class="bi bi-file-earmark me-2"></i><span id="file-name" class="fw-semibold"></span></div>
            <span id="file-size" class="text-muted small"></span>
        </div>
    </div>

    <?php if (isset($toolConfig['options_html'])): ?>
    <div id="options-panel" class="d-none mt-3">
        <?= $toolConfig['options_html'] ?>
    </div>
    <?php endif; ?>

    <div id="upload-section" class="d-none mt-3">
        <div class="tb-progress mb-2"><div class="tb-progress-bar" id="upload-progress" style="width:0%"></div></div>
        <div class="d-grid">
            <button class="btn tb-btn-primary btn-lg" id="process-btn">
                <i class="bi bi-gear me-2"></i>Process
            </button>
        </div>
    </div>

    <div id="results" class="d-none mt-4"></div>

    <?php if (isset($toolConfig['extra_scripts'])): foreach($toolConfig['extra_scripts'] as $s): ?>
    <script src="<?= $s ?>"></script>
    <?php endforeach; endif; ?>

    <script src="<?= BASE_URL ?>/assets/js/common.js"></script>
    <script>
    let currentFile = null;
    ToolBoxUI.initDropZone('drop-zone', 'file-input', {
        accept: '<?= htmlspecialchars($accept) ?>',
        onFiles: (files) => {
            currentFile = files[0];
            document.getElementById('file-name').textContent = currentFile.name;
            document.getElementById('file-size').textContent = ToolBoxUI.formatSize(currentFile.size);
            document.getElementById('file-info').classList.remove('d-none');
            document.getElementById('upload-section').classList.remove('d-none');
            const opts = document.getElementById('options-panel');
            if (opts) opts.classList.remove('d-none');
            document.getElementById('results').classList.add('d-none');
        }
    });

    document.getElementById('process-btn').addEventListener('click', async () => {
        if (!currentFile) return;
        
        // Handle Client-Side Processing if defined
        if (typeof onProcess === 'function') {
            ToolBoxUI.showProcessing('process-btn', 'Processing in browser...');
            try {
                await onProcess(currentFile);
            } catch (e) {
                ToolBoxUI.showToast('Processing failed: ' + e.message, 'danger');
            }
            ToolBoxUI.hideProcessing('process-btn');
            return;
        }

        ToolBoxUI.showProcessing('process-btn', 'Uploading & Processing...');

        const formData = new FormData();
        formData.append('file', currentFile);
        formData.append('tool', '<?= $toolSlug ?>');
        formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= getCsrfToken() ?>');

        // Collect any extra options
        document.querySelectorAll('#options-panel [name]').forEach(el => {
            formData.append(el.name, el.value);
        });

        try {
            const endpoint = '<?= $toolConfig['category'] === 'img' ? BASE_URL . "/api/process_img.php" : BASE_URL . "/api/process_pdf.php" ?>';
            const res = await fetch(endpoint, { method: 'POST', body: formData });
            const data = await res.json();

            if (data.error) {
                ToolBoxUI.showToast(data.error, 'danger');
            } else if (data.download_url) {
                ToolBoxUI.showDownloadResult(data.download_url);
            }
        } catch (e) {
            ToolBoxUI.showToast('Upload failed: ' + e.message, 'danger');
        }
        ToolBoxUI.hideProcessing('process-btn');
    });

    // Helper for tools to show the result
    ToolBoxUI.showDownloadResult = (url, filename = 'result.pdf') => {
        const parent = document.getElementById('results');
        parent.classList.remove('d-none');
        parent.innerHTML = `
            <div class="tb-dash-card text-center">
                <i class="bi bi-check-circle text-success" style="font-size:2.5rem"></i>
                <h5 class="fw-bold mt-2 mb-3">Processing Complete!</h5>
                <a href="${url}" download="${filename}" class="btn tb-btn-primary btn-lg">
                    <i class="bi bi-download me-2"></i>Download Result
                </a>
                <p class="text-muted small mt-2 mb-0">Conversion done successfully.</p>
            </div>`;
    };
    </script>
    <?php endif; ?>
</div>

<?php
    require_once __DIR__ . '/../includes/footer.php';
}
