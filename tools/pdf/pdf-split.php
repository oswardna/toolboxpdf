<?php
$pageTitle = 'Split PDF';
$pageDesc  = 'Split a PDF into multiple files by page ranges. Runs 100% in your browser.';
$extraHead = '
<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5" style="max-width:900px">
    <div class="tb-tool-header">
        <div class="tool-page-icon pdf">
            <i class="bi bi-scissors"></i>
        </div>
        <h1>Split PDF</h1>
        <p class="tool-desc">Separate one PDF into multiple files by page ranges. Runs entirely in your browser — nothing is uploaded.</p>
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

    <div id="file-info" class="d-none mt-3">
        <div class="tb-dash-card d-flex justify-content-between align-items-center">
            <div><i class="bi bi-file-earmark-pdf text-danger me-2"></i><span id="file-name" class="fw-semibold"></span></div>
            <span class="badge bg-secondary" id="page-count"></span>
        </div>
    </div>

    <div id="options-panel" class="d-none mt-4">
        <div class="tb-dash-card mb-3">
            <label class="form-label fw-semibold mb-3">Select pages to extract</label>
            <div id="preview-grid" class="d-flex flex-wrap gap-3" style="max-height: 400px; overflow-y: auto; padding: 10px; background: var(--tb-bg-primary); border-radius: var(--tb-radius-md); border: 1px solid var(--tb-border);">
                <!-- Thumbnails go here -->
            </div>
            
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="merge-extracted">
                <label class="form-check-label fw-semibold" for="merge-extracted">
                    Merge extracted pages into a single PDF
                </label>
            </div>
        </div>
        <div class="d-grid mt-3">
            <button class="btn tb-btn-primary btn-lg" id="process-btn">
                <i class="bi bi-scissors me-2"></i>Split PDF
            </button>
        </div>
    </div>

    <div id="results" class="d-none mt-4"></div>
</div>

<script src="<?= BASE_URL ?>/assets/js/client-pdf.js"></script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<style>
.pdf-page-thumb { cursor: pointer; transition: 0.2s; border: 3px solid transparent; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); background: #fff; padding: 4px; position: relative; }
.pdf-page-thumb:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
.pdf-page-thumb.selected { border-color: var(--tb-primary); }
.pdf-page-thumb.selected::after { content: '\F26A'; font-family: 'bootstrap-icons'; position: absolute; top: -10px; right: -10px; background: var(--tb-primary); color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
.pdf-page-thumb canvas { max-width: 100px; height: auto; display: block; }
.page-label { text-align: center; font-size: 0.8rem; margin-top: 5px; color: var(--tb-text-muted); font-weight: 500; }
</style>
<script>
let currentFile = null;
let totalPages = 0;
let selectedPages = new Set();

ToolBoxUI.initDropZone('drop-zone', 'file-input', {
    accept: '.pdf',
    onFiles: async (files) => {
        currentFile = files[0];
        document.getElementById('file-name').textContent = currentFile.name;
        document.getElementById('file-info').classList.remove('d-none');
        document.getElementById('results').classList.add('d-none');
        
        ToolBoxUI.showProcessing('process-btn', 'Loading Previews...');
        document.getElementById('options-panel').classList.remove('d-none');
        document.getElementById('process-btn').disabled = true;
        
        try {
            totalPages = await ClientPDF.getPageCount(currentFile);
            document.getElementById('page-count').textContent = totalPages + ' pages';
            
            const grid = document.getElementById('preview-grid');
            grid.innerHTML = '<div class="text-center w-100 py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Rendering pages...</p></div>';
            
            const canvases = await ClientPDF.renderAllPages(currentFile, 0.2);
            grid.innerHTML = '';
            selectedPages.clear();
            
            canvases.forEach((canvas, i) => {
                const pageNum = i + 1;
                const wrapper = document.createElement('div');
                wrapper.className = 'pdf-page-thumb selected';
                wrapper.dataset.page = pageNum;
                selectedPages.add(pageNum);
                
                wrapper.appendChild(canvas);
                
                const label = document.createElement('div');
                label.className = 'page-label';
                label.textContent = `Page ${pageNum}`;
                wrapper.appendChild(label);
                
                wrapper.addEventListener('click', () => {
                    if (selectedPages.has(pageNum)) {
                        selectedPages.delete(pageNum);
                        wrapper.classList.remove('selected');
                    } else {
                        selectedPages.add(pageNum);
                        wrapper.classList.add('selected');
                    }
                });
                
                grid.appendChild(wrapper);
            });
            
        } catch (e) {
            ToolBoxUI.showToast('Failed to render PDF previews: ' + e.message, 'danger');
            document.getElementById('options-panel').classList.add('d-none');
        }
        
        ToolBoxUI.hideProcessing('process-btn');
    }
});

document.getElementById('process-btn').addEventListener('click', async () => {
    if (selectedPages.size === 0) {
        ToolBoxUI.showToast('Please select at least one page.', 'warning');
        return;
    }

    const merge = document.getElementById('merge-extracted').checked;
    const sortedPages = Array.from(selectedPages).sort((a, b) => a - b);
    
    // Determine ranges
    let ranges = [];
    if (merge) {
        ranges.push(sortedPages);
    } else {
        ranges = sortedPages.map(p => [p]);
    }

    ToolBoxUI.showProcessing('process-btn', 'Processing...');
    try {
        const results = await ClientPDF.split(currentFile, ranges);
        if (results.length === 1) {
            ToolBoxUI.createDownloadBtn(results[0].blob, merge ? 'merged_extracted.pdf' : results[0].name, 'results');
        } else {
            ToolBoxUI.createMultiDownload(results, 'results');
        }
    } catch (e) {
        ToolBoxUI.showToast('Error: ' + e.message, 'danger');
    }
    ToolBoxUI.hideProcessing('process-btn');
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
