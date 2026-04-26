<?php
$pageTitle = 'Resize Image';
$pageDesc  = 'Resize images to specific dimensions with aspect ratio lock.';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5" style="max-width:900px">
    <div class="tb-tool-header">
        <div class="tool-page-icon img">
            <i class="bi bi-aspect-ratio"></i>
        </div>
        <h1>Resize Image</h1>
        <p class="tool-desc">Resize images to exact pixel dimensions with optional aspect ratio lock.</p>
        <span class="tb-badge-free me-1">Free</span><span class="tb-badge-instant">⚡ Instant</span>
    </div>

    <div id="drop-zone" class="tb-drop-zone text-center mt-4">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
            <label for="file-input" class="tb-btn-huge mb-0">
                Select Image
            </label>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; background-color: #e5322d; border-color: #e5322d;" onclick="ToolBoxUI.showToast('Google Drive integration coming soon', 'info')"><i class="bi bi-google"></i></button>
                <button type="button" class="btn btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; background-color: #e5322d; border-color: #e5322d;" onclick="ToolBoxUI.showToast('Dropbox integration coming soon', 'info')"><i class="bi bi-dropbox"></i></button>
            </div>
        </div>
        <div class="tb-drop-text">or drop Image here</div>
        <input type="file" id="file-input" accept="image/*" class="d-none">
    </div>

    <div id="options-panel" class="d-none mt-4">
        <div class="tb-dash-card mb-3">
            <div class="mb-2"><span class="small text-muted">Original: </span><span id="orig-dims" class="small fw-semibold"></span></div>
            <div class="row g-3 align-items-end">
                <div class="col">
                    <label class="form-label small fw-semibold">Width (px)</label>
                    <input type="number" class="form-control tb-form-control" id="inp-width" min="1" max="10000">
                </div>
                <div class="col-auto d-flex align-items-center pt-4">
                    <button class="btn btn-sm tb-btn-outline" id="lock-btn" title="Lock aspect ratio">
                        <i class="bi bi-link-45deg"></i>
                    </button>
                </div>
                <div class="col">
                    <label class="form-label small fw-semibold">Height (px)</label>
                    <input type="number" class="form-control tb-form-control" id="inp-height" min="1" max="10000">
                </div>
            </div>
            <div class="d-flex gap-2 mt-3 flex-wrap">
                <button class="btn btn-sm tb-btn-outline preset-btn" data-w="1920" data-h="1080">1920×1080</button>
                <button class="btn btn-sm tb-btn-outline preset-btn" data-w="1280" data-h="720">1280×720</button>
                <button class="btn btn-sm tb-btn-outline preset-btn" data-w="800" data-h="600">800×600</button>
                <button class="btn btn-sm tb-btn-outline preset-btn" data-w="500" data-h="500">500×500</button>
            </div>
        </div>
        <div class="d-grid">
            <button class="btn tb-btn-primary btn-lg" id="process-btn">
                <i class="bi bi-aspect-ratio me-2"></i>Resize Image
            </button>
        </div>
    </div>
    <div id="results" class="d-none mt-4"></div>
</div>

<script src="<?= BASE_URL ?>/assets/js/client-img.js"></script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<script>
let currentFile = null, origW = 0, origH = 0, locked = true;
const wInp = document.getElementById('inp-width');
const hInp = document.getElementById('inp-height');

document.getElementById('lock-btn').addEventListener('click', function() {
    locked = !locked;
    this.innerHTML = locked ? '<i class="bi bi-link-45deg"></i>' : '<i class="bi bi-link"></i>';
    this.classList.toggle('active', locked);
});

wInp.addEventListener('input', () => {
    if (locked && origW) hInp.value = Math.round(wInp.value * (origH / origW));
});
hInp.addEventListener('input', () => {
    if (locked && origH) wInp.value = Math.round(hInp.value * (origW / origH));
});

document.querySelectorAll('.preset-btn').forEach(btn => {
    btn.addEventListener('click', () => { wInp.value = btn.dataset.w; hInp.value = btn.dataset.h; });
});

ToolBoxUI.initDropZone('drop-zone', 'file-input', {
    accept: 'image/*',
    onFiles: async (files) => {
        currentFile = files[0];
        const dims = await ClientImg.getDimensions(currentFile);
        origW = dims.width; origH = dims.height;
        wInp.value = origW; hInp.value = origH;
        document.getElementById('orig-dims').textContent = origW + ' × ' + origH + ' px';
        document.getElementById('options-panel').classList.remove('d-none');
        document.getElementById('results').classList.add('d-none');
    }
});

document.getElementById('process-btn').addEventListener('click', async () => {
    if (!currentFile) return;
    ToolBoxUI.showProcessing('process-btn', 'Resizing...');
    try {
        const blob = await ClientImg.resize(currentFile, parseInt(wInp.value), parseInt(hInp.value), false);
        const ext = currentFile.name.split('.').pop();
        ToolBoxUI.createDownloadBtn(blob, 'resized.' + ext, 'results');
    } catch (e) { ToolBoxUI.showToast('Error: ' + e.message, 'danger'); }
    ToolBoxUI.hideProcessing('process-btn');
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
