<?php
$pageTitle = 'Rotate PDF';
$pageDesc  = 'Rotate individual pages or the entire PDF. Runs in your browser.';
$extraHead = '
<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5" style="max-width:900px">
    <div class="tb-tool-header">
        <div class="tool-page-icon pdf">
            <i class="bi bi-arrow-clockwise"></i>
        </div>
        <h1>Rotate PDF</h1>
        <p class="tool-desc">Rotate all pages or specific pages. Preview before saving.</p>
        <span class="tb-badge-free me-1">Free</span><span class="tb-badge-instant">⚡ Instant</span>
    </div>

    <div id="drop-zone" class="tb-drop-zone text-center mt-4">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
            <label for="file-input" class="tb-btn-huge mb-0">
                Select PDF file
            </label>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; background-color: #e5322d; border-color: #e5322d;" onclick="ToolBoxUI.showToast('Google Drive integration coming soon', 'info')"><i class="bi bi-google"></i></button>
                <button type="button" class="btn btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; background-color: #e5322d; border-color: #e5322d;" onclick="ToolBoxUI.showToast('Dropbox integration coming soon', 'info')"><i class="bi bi-dropbox"></i></button>
            </div>
        </div>
        <div class="tb-drop-text">or drop PDF files here</div>
        <input type="file" id="file-input" accept=".pdf" class="d-none">
    </div>

    <div id="options-panel" class="d-none mt-4">
        <div class="tb-dash-card mb-3">
            <label class="form-label fw-semibold">Rotation</label>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn tb-btn-outline active" data-deg="90">90° Clockwise</button>
                <button class="btn tb-btn-outline" data-deg="180">180°</button>
                <button class="btn tb-btn-outline" data-deg="270">90° Counter-clockwise</button>
            </div>
        </div>
        <div class="tb-dash-card mb-3">
            <label class="form-label fw-semibold">Apply to</label>
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="applyTo" id="applyAll" value="all" checked>
                <label class="form-check-label" for="applyAll">All pages</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="applyTo" id="applySpecific" value="specific">
                <label class="form-check-label" for="applySpecific">Specific pages</label>
            </div>
            <input type="text" class="form-control tb-form-control mt-2 d-none" id="specific-pages" placeholder="e.g. 1, 3, 5">
        </div>
        <div class="d-grid">
            <button class="btn tb-btn-primary btn-lg" id="process-btn">
                <i class="bi bi-arrow-clockwise me-2"></i>Rotate PDF
            </button>
        </div>
    </div>

    <div id="results" class="d-none mt-4"></div>
</div>

<script src="<?= BASE_URL ?>/assets/js/client-pdf.js"></script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<script>
let currentFile = null;
let selectedDeg = 90;

document.querySelectorAll('[data-deg]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('[data-deg]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedDeg = parseInt(btn.dataset.deg);
    });
});

document.getElementById('applySpecific').addEventListener('change', () => {
    document.getElementById('specific-pages').classList.remove('d-none');
});
document.getElementById('applyAll').addEventListener('change', () => {
    document.getElementById('specific-pages').classList.add('d-none');
});

ToolBoxUI.initDropZone('drop-zone', 'file-input', {
    accept: '.pdf',
    onFiles: (files) => {
        currentFile = files[0];
        document.getElementById('options-panel').classList.remove('d-none');
        document.getElementById('results').classList.add('d-none');
    }
});

document.getElementById('process-btn').addEventListener('click', async () => {
    if (!currentFile) return;
    ToolBoxUI.showProcessing('process-btn', 'Rotating...');
    try {
        let indices = null;
        if (document.getElementById('applySpecific').checked) {
            const val = document.getElementById('specific-pages').value;
            indices = val.split(',').map(s => parseInt(s.trim())).filter(n => !isNaN(n));
        }
        const blob = await ClientPDF.rotate(currentFile, selectedDeg, indices);
        ToolBoxUI.createDownloadBtn(blob, 'rotated_' + currentFile.name, 'results');
    } catch (e) {
        ToolBoxUI.showToast('Error: ' + e.message, 'danger');
    }
    ToolBoxUI.hideProcessing('process-btn');
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
