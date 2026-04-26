<?php
$pageTitle = 'Grayscale Image';
$pageDesc  = 'Convert images to black and white instantly.';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5" style="max-width:900px">
    <div class="tb-tool-header">
        <div class="tool-page-icon" style="background:rgba(6,182,212,0.12);color:#22d3ee">
            <i class="bi bi-circle-half"></i>
        </div>
        <h1>Grayscale</h1>
        <p class="tool-desc">Convert any image to black and white with a single click.</p>
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
        <div class="row g-3 mb-3">
            <div class="col-6 text-center">
                <p class="small fw-semibold mb-2">Original</p>
                <img id="preview-original" class="img-fluid rounded" style="max-height:250px">
            </div>
            <div class="col-6 text-center">
                <p class="small fw-semibold mb-2">Grayscale</p>
                <img id="preview-gray" class="img-fluid rounded d-none" style="max-height:250px">
                <div id="gray-placeholder" class="d-flex align-items-center justify-content-center" style="height:250px;border:1px dashed var(--tb-border);border-radius:8px">
                    <span class="text-muted small">Click process to preview</span>
                </div>
            </div>
        </div>
        <div class="d-grid">
            <button class="btn tb-btn-primary btn-lg" id="process-btn"><i class="bi bi-circle-half me-2"></i>Convert to Grayscale</button>
        </div>
    </div>
    <div id="results" class="d-none mt-4"></div>
</div>

<script src="<?= BASE_URL ?>/assets/js/client-img.js"></script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<script>
let currentFile = null;

ToolBoxUI.initDropZone('drop-zone', 'file-input', {
    accept: 'image/*',
    onFiles: (files) => {
        currentFile = files[0];
        document.getElementById('preview-original').src = URL.createObjectURL(currentFile);
        document.getElementById('preview-gray').classList.add('d-none');
        document.getElementById('gray-placeholder').classList.remove('d-none');
        document.getElementById('options-panel').classList.remove('d-none');
        document.getElementById('results').classList.add('d-none');
    }
});

document.getElementById('process-btn').addEventListener('click', async () => {
    if (!currentFile) return;
    ToolBoxUI.showProcessing('process-btn', 'Converting...');
    try {
        const blob = await ClientImg.grayscale(currentFile);
        document.getElementById('preview-gray').src = URL.createObjectURL(blob);
        document.getElementById('preview-gray').classList.remove('d-none');
        document.getElementById('gray-placeholder').classList.add('d-none');
        const ext = currentFile.name.split('.').pop();
        ToolBoxUI.createDownloadBtn(blob, 'grayscale.' + ext, 'results');
    } catch (e) { ToolBoxUI.showToast('Error: ' + e.message, 'danger'); }
    ToolBoxUI.hideProcessing('process-btn');
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
