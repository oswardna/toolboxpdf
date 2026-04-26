<?php
$pageTitle = 'Compress Image';
$pageDesc  = 'Reduce image file size without losing quality. Runs in your browser.';
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5" style="max-width:900px">
    <div class="tb-tool-header">
        <div class="tool-page-icon" style="background:rgba(6,182,212,0.12);color:#22d3ee">
            <i class="bi bi-file-earmark-zip"></i>
        </div>
        <h1>Compress Image</h1>
        <p class="tool-desc">Reduce image file size while maintaining visual quality.</p>
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
            <label class="form-label fw-semibold">Target Size (KB)</label>
            <input type="range" class="form-range" id="quality-slider" min="50" max="1000" value="200" step="50">
            <div class="d-flex justify-content-between small text-muted">
                <span>50 KB</span>
                <span id="quality-label" class="fw-semibold text-white">200 KB</span>
                <span>1000 KB</span>
            </div>
        </div>
        <div id="preview-area" class="row g-3 mb-3">
            <div class="col-6 text-center">
                <p class="small fw-semibold mb-2">Original</p>
                <img id="preview-original" class="img-fluid rounded" style="max-height:200px">
                <p class="small text-muted mt-1" id="original-size"></p>
            </div>
            <div class="col-6 text-center">
                <p class="small fw-semibold mb-2">Compressed</p>
                <img id="preview-compressed" class="img-fluid rounded d-none" style="max-height:200px">
                <p class="small text-muted mt-1" id="compressed-size"></p>
            </div>
        </div>
        <div class="d-grid">
            <button class="btn tb-btn-primary btn-lg" id="process-btn">
                <i class="bi bi-file-earmark-zip me-2"></i>Compress Image
            </button>
        </div>
    </div>

    <div id="results" class="d-none mt-4"></div>
</div>

<script src="<?= BASE_URL ?>/assets/js/client-img.js"></script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<script>
let currentFile = null;
const slider = document.getElementById('quality-slider');
slider.addEventListener('input', () => {
    document.getElementById('quality-label').textContent = slider.value + ' KB';
});

ToolBoxUI.initDropZone('drop-zone', 'file-input', {
    accept: 'image/*',
    onFiles: (files) => {
        currentFile = files[0];
        document.getElementById('options-panel').classList.remove('d-none');
        document.getElementById('results').classList.add('d-none');
        document.getElementById('preview-original').src = URL.createObjectURL(currentFile);
        document.getElementById('original-size').textContent = ToolBoxUI.formatSize(currentFile.size);
        document.getElementById('preview-compressed').classList.add('d-none');
        document.getElementById('compressed-size').textContent = '';
    }
});

document.getElementById('process-btn').addEventListener('click', async () => {
    if (!currentFile) return;
    ToolBoxUI.showProcessing('process-btn', 'Compressing...');
    try {
        const targetKB = parseInt(slider.value);
        const result = await ClientImg.compress(currentFile, targetKB);
        document.getElementById('preview-compressed').src = URL.createObjectURL(result);
        document.getElementById('preview-compressed').classList.remove('d-none');
        document.getElementById('compressed-size').textContent =
            ToolBoxUI.formatSize(result.size) + ' (' + Math.round((1 - result.size / currentFile.size) * 100) + '% smaller)';
        const ext = currentFile.name.split('.').pop();
        ToolBoxUI.createDownloadBtn(result, 'compressed.' + ext, 'results');
    } catch (e) {
        ToolBoxUI.showToast('Error: ' + e.message, 'danger');
    }
    ToolBoxUI.hideProcessing('process-btn');
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
