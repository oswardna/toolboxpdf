<?php
$pageTitle = 'Crop Image';
$pageDesc  = 'Crop images to your desired area with interactive selection.';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5" style="max-width:800px">
    <div class="tb-tool-header">
        <div class="tool-page-icon" style="background:rgba(6,182,212,0.12);color:#22d3ee">
            <i class="bi bi-crop"></i>
        </div>
        <h1>Crop Image</h1>
        <p class="tool-desc">Click and drag to select the crop area. Runs entirely in your browser.</p>
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

    <div id="crop-area" class="d-none mt-4">
        <div class="tb-dash-card mb-3 p-2 text-center" style="position:relative;overflow:hidden">
            <canvas id="crop-canvas" style="max-width:100%;cursor:crosshair"></canvas>
            <div id="crop-selection" style="position:absolute;border:2px dashed #7c3aed;background:rgba(124,58,237,0.1);display:none;pointer-events:none"></div>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-3"><label class="form-label small">X</label><input type="number" class="form-control tb-form-control form-control-sm" id="crop-x" value="0"></div>
            <div class="col-3"><label class="form-label small">Y</label><input type="number" class="form-control tb-form-control form-control-sm" id="crop-y" value="0"></div>
            <div class="col-3"><label class="form-label small">Width</label><input type="number" class="form-control tb-form-control form-control-sm" id="crop-w" value="0"></div>
            <div class="col-3"><label class="form-label small">Height</label><input type="number" class="form-control tb-form-control form-control-sm" id="crop-h" value="0"></div>
        </div>
        <div class="d-grid">
            <button class="btn tb-btn-primary btn-lg" id="process-btn"><i class="bi bi-crop me-2"></i>Crop Image</button>
        </div>
    </div>
    <div id="results" class="d-none mt-4"></div>
</div>

<script src="<?= BASE_URL ?>/assets/js/client-img.js"></script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<script>
let currentFile = null, imgEl = null, scale = 1;
let startX = 0, startY = 0, isDragging = false;
const canvas = document.getElementById('crop-canvas');
const ctx = canvas.getContext('2d');

ToolBoxUI.initDropZone('drop-zone', 'file-input', {
    accept: 'image/*',
    onFiles: async (files) => {
        currentFile = files[0];
        imgEl = new Image();
        imgEl.onload = () => {
            const maxW = 750;
            scale = imgEl.naturalWidth > maxW ? maxW / imgEl.naturalWidth : 1;
            canvas.width = imgEl.naturalWidth * scale;
            canvas.height = imgEl.naturalHeight * scale;
            ctx.drawImage(imgEl, 0, 0, canvas.width, canvas.height);
            document.getElementById('crop-area').classList.remove('d-none');
            document.getElementById('results').classList.add('d-none');
            // Default crop = full image
            document.getElementById('crop-x').value = 0;
            document.getElementById('crop-y').value = 0;
            document.getElementById('crop-w').value = imgEl.naturalWidth;
            document.getElementById('crop-h').value = imgEl.naturalHeight;
        };
        imgEl.src = URL.createObjectURL(currentFile);
    }
});

canvas.addEventListener('mousedown', (e) => {
    const rect = canvas.getBoundingClientRect();
    startX = e.clientX - rect.left; startY = e.clientY - rect.top;
    isDragging = true;
});

canvas.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    const rect = canvas.getBoundingClientRect();
    const curX = e.clientX - rect.left, curY = e.clientY - rect.top;
    const x = Math.min(startX, curX), y = Math.min(startY, curY);
    const w = Math.abs(curX - startX), h = Math.abs(curY - startY);
    // Redraw
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(imgEl, 0, 0, canvas.width, canvas.height);
    ctx.fillStyle = 'rgba(0,0,0,0.4)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.clearRect(x, y, w, h);
    ctx.drawImage(imgEl, x / scale, y / scale, w / scale, h / scale, x, y, w, h);
    ctx.strokeStyle = '#7c3aed'; ctx.lineWidth = 2; ctx.setLineDash([5, 5]);
    ctx.strokeRect(x, y, w, h); ctx.setLineDash([]);
    // Update inputs
    document.getElementById('crop-x').value = Math.round(x / scale);
    document.getElementById('crop-y').value = Math.round(y / scale);
    document.getElementById('crop-w').value = Math.round(w / scale);
    document.getElementById('crop-h').value = Math.round(h / scale);
});

canvas.addEventListener('mouseup', () => { isDragging = false; });

document.getElementById('process-btn').addEventListener('click', async () => {
    const cx = parseInt(document.getElementById('crop-x').value) || 0;
    const cy = parseInt(document.getElementById('crop-y').value) || 0;
    const cw = parseInt(document.getElementById('crop-w').value) || 100;
    const ch = parseInt(document.getElementById('crop-h').value) || 100;
    if (cw < 1 || ch < 1) { ToolBoxUI.showToast('Select a crop area', 'warning'); return; }
    ToolBoxUI.showProcessing('process-btn', 'Cropping...');
    try {
        const blob = await ClientImg.crop(currentFile, cx, cy, cw, ch);
        const ext = currentFile.name.split('.').pop();
        ToolBoxUI.createDownloadBtn(blob, 'cropped.' + ext, 'results');
    } catch (e) { ToolBoxUI.showToast('Error: ' + e.message, 'danger'); }
    ToolBoxUI.hideProcessing('process-btn');
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
