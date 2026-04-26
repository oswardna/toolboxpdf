<?php
$pageTitle = 'Merge PDF';
$pageDesc  = 'Combine multiple PDFs into a single document. Runs 100% in your browser.';
$extraHead = '
<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid px-4 py-4" id="main-container">
    <!-- Initial State -->
    <div id="initial-view" class="mx-auto" style="max-width:720px; text-align:center;">
        <div class="tb-tool-header">
            <div class="tool-page-icon pdf mx-auto mb-3">
                <i class="bi bi-union"></i>
            </div>
            <h1>Merge PDF</h1>
            <p class="tool-desc">Combine multiple PDF files into one document. Runs 100% in your browser.</p>
            <span class="tb-badge-free me-1">Free</span><span class="tb-badge-instant">⚡ Instant</span>
        </div>

        <div id="drop-zone" class="tb-drop-zone text-center mt-4">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
            <label for="file-input" class="tb-btn-huge mb-0">
                Select PDF files
            </label>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; background-color: #e5322d; border-color: #e5322d;" onclick="ToolBoxUI.showToast('Google Drive integration coming soon', 'info')"><i class="bi bi-google"></i></button>
                <button type="button" class="btn btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; background-color: #e5322d; border-color: #e5322d;" onclick="ToolBoxUI.showToast('Dropbox integration coming soon', 'info')"><i class="bi bi-dropbox"></i></button>
            </div>
        </div>
        <div class="tb-drop-text">or drop PDF files here</div>
        <input type="file" id="file-input" accept=".pdf" multiple class="d-none">
    </div>
    </div>

    <!-- Workspace State (Split Screen) -->
    <div id="active-view" class="d-none">
        <div class="row min-vh-75 shadow-sm rounded-4 overflow-hidden border" style="background: var(--tb-bg); min-height: 70vh;">
            <!-- Left Side: Previews -->
            <div class="col-lg-9 p-4 position-relative">
                <!-- Floating Add Button -->
                <div class="position-absolute d-flex flex-column gap-2" style="top: 20px; right: 20px; z-index: 10;">
                    <button class="btn rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="background: #e5322d; color: white; width: 48px; height: 48px; border: none;" onclick="document.getElementById('file-input').click()" title="Add more files">
                        <i class="bi bi-plus-lg fs-4"></i>
                    </button>
                    <!-- Small sort hint button -->
                    <button class="btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px; border: 1px solid var(--tb-border);" title="Drag files to reorder">
                        <i class="bi bi-sort-down fs-5"></i>
                    </button>
                </div>
                
                <div id="file-list" class="d-flex flex-wrap gap-4 align-items-start pt-2"></div>
            </div>

            <!-- Right Side: Sidebar -->
            <div class="col-lg-3 d-flex flex-column bg-white border-start p-4">
                <h3 class="fw-bold mb-4 text-center mt-2">Merge PDF</h3>
                
                <div class="alert alert-info bg-opacity-10 border-0" style="background-color: #e3f2fd; color: #0c5460; border-radius: 12px;">
                    <i class="bi bi-info-circle me-2"></i>To change the order of your PDFs, drag and drop the files as you want.
                </div>
                
                <div class="mt-auto pt-4">
                    <button class="btn btn-lg w-100 py-3 shadow-sm text-white fw-bold d-flex align-items-center justify-content-center gap-2" id="process-btn" style="background: #e5322d; border-radius: 12px; transition: 0.2s;">
                        Merge PDF <i class="bi bi-arrow-right-circle fs-5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="results" class="d-none mt-4 mx-auto" style="max-width: 720px;"></div>
</div>

<style>
.pdf-page-thumb { 
    transition: 0.2s; border: 2px solid transparent; border-radius: var(--tb-radius-md); 
    box-shadow: 0 2px 4px rgba(0,0,0,0.1); background: #fff; padding: 6px; 
    position: relative; display: flex; flex-direction: column; align-items: center; 
    width: 120px; 
}
.pdf-page-thumb:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
.pdf-page-thumb canvas { max-width: 100px; height: auto; display: block; border: 1px solid var(--tb-border); margin-bottom: 5px; }
.page-label { text-align: center; font-size: 0.75rem; color: var(--tb-text-muted); font-weight: 600; width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>

<script src="<?= BASE_URL ?>/assets/js/client-pdf.js"></script>
<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<script>
let pdfFiles = [];

function renderFileList() {
    const list = document.getElementById('file-list');
    list.innerHTML = '';
    
    if (pdfFiles.length === 0) {
        document.getElementById('initial-view').classList.remove('d-none');
        document.getElementById('active-view').classList.add('d-none');
        return;
    }
    
    document.getElementById('initial-view').classList.add('d-none');
    document.getElementById('active-view').classList.remove('d-none');

    pdfFiles.forEach((f, i) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'pdf-page-thumb';
        wrapper.draggable = true;
        wrapper.style.cursor = 'grab';
        
        // Remove Button
        const removeBtn = document.createElement('button');
        removeBtn.className = 'btn btn-sm btn-danger position-absolute shadow-sm';
        removeBtn.style.top = '-10px';
        removeBtn.style.right = '-10px';
        removeBtn.style.borderRadius = '50%';
        removeBtn.style.width = '24px';
        removeBtn.style.height = '24px';
        removeBtn.style.padding = '0';
        removeBtn.style.zIndex = '10';
        removeBtn.innerHTML = '<i class="bi bi-x"></i>';
        removeBtn.onclick = (e) => {
            e.stopPropagation();
            pdfFiles.splice(i, 1);
            renderFileList();
        };
        wrapper.appendChild(removeBtn);
        
        // Loading Placeholder
        const placeholder = document.createElement('div');
        placeholder.className = 'spinner-border text-primary my-4';
        placeholder.style.width = '1.5rem';
        placeholder.style.height = '1.5rem';
        wrapper.appendChild(placeholder);
        
        // Label
        const label = document.createElement('div');
        label.className = 'page-label';
        label.textContent = f.name;
        label.title = f.name;

        wrapper.dataset.idx = i;

        // Async Render Thumbnail
        ClientPDF.renderPage(f, 1, 0.2).then(canvas => {
            if(wrapper.contains(placeholder)) {
                wrapper.replaceChild(canvas, placeholder);
                wrapper.appendChild(label);
            }
        }).catch(err => {
            placeholder.className = 'text-danger small p-2';
            placeholder.textContent = 'Preview Error';
            wrapper.appendChild(label);
        });

        list.appendChild(wrapper);
    });

    // Initialize SortableJS for fluid drag-and-drop reordering
    new Sortable(list, {
        animation: 150,
        ghostClass: 'opacity-50',
        onEnd: function (evt) {
            const newOrder = [];
            list.querySelectorAll('.pdf-page-thumb').forEach(el => {
                newOrder.push(pdfFiles[parseInt(el.dataset.idx)]);
            });
            pdfFiles = newOrder;
            
            // Reassign indices to match new DOM order so subsequent sorts work
            list.querySelectorAll('.pdf-page-thumb').forEach((el, index) => {
                el.dataset.idx = index;
            });
        }
    });
}

ToolBoxUI.initDropZone('drop-zone', 'file-input', {
    accept: '.pdf', multiple: true,
    onFiles: (files) => {
        pdfFiles = pdfFiles.concat(files);
        renderFileList();
        document.getElementById('results').classList.add('d-none');
    }
});

document.getElementById('process-btn').addEventListener('click', async () => {
    if (pdfFiles.length < 2) { ToolBoxUI.showToast('Add at least 2 PDFs to merge', 'warning'); return; }
    
    // Change button state
    const btn = document.getElementById('process-btn');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Merging...';
    btn.disabled = true;
    
    try {
        const blob = await ClientPDF.merge(pdfFiles);
        document.getElementById('active-view').classList.add('d-none');
        document.getElementById('initial-view').classList.remove('d-none');
        pdfFiles = [];
        ToolBoxUI.createDownloadBtn(blob, 'merged.pdf', 'results');
    } catch (e) {
        ToolBoxUI.showToast('Merge Error: ' + e.message, 'danger');
    }
    
    // Restore button state
    btn.innerHTML = originalHTML;
    btn.disabled = false;
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
