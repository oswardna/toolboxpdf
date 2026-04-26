<?php
$pageTitle = 'Flip / Mirror Image';
$pageDesc  = 'Flip images horizontally or vertically.';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5" style="max-width:900px">
    <div class="tb-tool-header">
        <div class="tool-page-icon" style="background:rgba(6,182,212,0.12);color:#22d3ee">
            <i class="bi bi-symmetry-vertical"></i>
        </div>
        <h1>Flip / Mirror</h1>
        <p class="tool-desc">Flip images horizontally or vertically with instant preview.</p>
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
        <div class="text-center mb-3">
            <img id="preview-img" class="img-fluid rounded" style="max-height:300px">
        </div>
        <div class="d-flex gap-3 justify-content-center mb-3">
            <button class="btn tb-btn-outline active" id="btn-horizontal"><i class="bi bi-symmetry-vertical me-2"></i>Horizontal</button>
            <button class="btn tb-btn-outline" id="btn-vertical"><i class="bi bi-symmetry-horizontal me-2"></i>Vertical</button>
        </div>
        <div class="d-grid">
            <button class="btn tb-btn-primary btn-lg" id="process-btn"><i class="bi bi-symmetry-vertical me-2"></i>Flip &amp; Download</button>
        </div>
    </div>
    <div id="results" class="d-none mt-4"></div>
</div>

<script src="<?= BASE_URL ?>/assets/js/client-img.js"></script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<script>
let currentFile = null, direction = 'horizontal';

document.getElementById('btn-horizontal').addEventListener('click', function() {
    direction = 'horizontal';
    this.classList.add('active');
    document.getElementById('btn-vertical').classList.remove('active');
});
document.getElementById('btn-vertical').addEventListener('click', function() {
    direction = 'vertical';
    this.classList.add('active');
    document.getElementById('btn-horizontal').classList.remove('active');
});

ToolBoxUI.initDropZone('drop-zone', 'file-input', {
    accept: 'image/*',
    onFiles: (files) => {
        currentFile = files[0];
        document.getElementById('preview-img').src = URL.createObjectURL(currentFile);
        document.getElementById('options-panel').classList.remove('d-none');
        document.getElementById('results').classList.add('d-none');
    }
});

document.getElementById('process-btn').addEventListener('click', async () => {
    if (!currentFile) return;
    ToolBoxUI.showProcessing('process-btn', 'Flipping...');
    try {
        const blob = await ClientImg.flip(currentFile, direction);
        document.getElementById('preview-img').src = URL.createObjectURL(blob);
        const ext = currentFile.name.split('.').pop();
        ToolBoxUI.createDownloadBtn(blob, 'flipped.' + ext, 'results');
    } catch (e) { ToolBoxUI.showToast('Error: ' + e.message, 'danger'); }
    ToolBoxUI.hideProcessing('process-btn');
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
