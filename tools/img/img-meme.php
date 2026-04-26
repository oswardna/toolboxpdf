<?php
$pageTitle = 'Meme Generator';
$pageDesc  = 'Create memes with custom text overlays.';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5" style="max-width:900px">
    <div class="tb-tool-header">
        <div class="tool-page-icon" style="background:rgba(6,182,212,0.12);color:#22d3ee">
            <i class="bi bi-emoji-laughing"></i>
        </div>
        <h1>Meme Generator</h1>
        <p class="tool-desc">Add top and bottom text to any image. Classic meme style!</p>
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
            <div class="mb-3">
                <label class="form-label small fw-semibold">Top Text</label>
                <input type="text" class="form-control tb-form-control" id="top-text" placeholder="TOP TEXT" maxlength="100">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Bottom Text</label>
                <input type="text" class="form-control tb-form-control" id="bottom-text" placeholder="BOTTOM TEXT" maxlength="100">
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label small fw-semibold">Text Color</label>
                    <input type="color" class="form-control form-control-color tb-form-control" id="text-color" value="#ffffff">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-semibold">Font Size</label>
                    <input type="range" class="form-range mt-2" id="font-size" min="16" max="120" value="48">
                </div>
            </div>
        </div>
        <div class="text-center mb-3">
            <p class="small fw-semibold mb-2">Preview</p>
            <canvas id="meme-preview" style="max-width:100%;border-radius:8px;background:#222"></canvas>
        </div>
        <div class="d-grid">
            <button class="btn tb-btn-primary btn-lg" id="process-btn"><i class="bi bi-download me-2"></i>Download Meme</button>
        </div>
    </div>
    <div id="results" class="d-none mt-4"></div>
</div>

<script src="<?= BASE_URL ?>/assets/js/client-img.js"></script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<script>
let currentFile = null, imgEl = null;
const canvas = document.getElementById('meme-preview');
const ctx = canvas.getContext('2d');

function renderPreview() {
    if (!imgEl) return;
    canvas.width = imgEl.naturalWidth; canvas.height = imgEl.naturalHeight;
    ctx.drawImage(imgEl, 0, 0);
    const fontSize = parseInt(document.getElementById('font-size').value);
    const textColor = document.getElementById('text-color').value;
    ctx.font = `bold ${fontSize}px Impact, sans-serif`;
    ctx.textAlign = 'center'; ctx.fillStyle = textColor;
    ctx.strokeStyle = '#000'; ctx.lineWidth = Math.max(2, fontSize / 12); ctx.lineJoin = 'round';
    const top = document.getElementById('top-text').value;
    const bottom = document.getElementById('bottom-text').value;
    if (top) { ctx.strokeText(top.toUpperCase(), canvas.width/2, fontSize+10); ctx.fillText(top.toUpperCase(), canvas.width/2, fontSize+10); }
    if (bottom) { ctx.strokeText(bottom.toUpperCase(), canvas.width/2, canvas.height-15); ctx.fillText(bottom.toUpperCase(), canvas.width/2, canvas.height-15); }
}

['top-text','bottom-text','text-color','font-size'].forEach(id => {
    document.getElementById(id).addEventListener('input', renderPreview);
});

ToolBoxUI.initDropZone('drop-zone', 'file-input', {
    accept: 'image/*',
    onFiles: (files) => {
        currentFile = files[0];
        imgEl = new Image();
        imgEl.onload = () => { renderPreview(); document.getElementById('options-panel').classList.remove('d-none'); };
        imgEl.src = URL.createObjectURL(currentFile);
    }
});

document.getElementById('process-btn').addEventListener('click', () => {
    canvas.toBlob(blob => {
        ToolBoxUI.createDownloadBtn(blob, 'meme.png', 'results');
    }, 'image/png');
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
