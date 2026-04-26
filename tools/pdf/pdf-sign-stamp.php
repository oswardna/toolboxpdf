<?php
$pageTitle = 'Sign & Stamp PDF';
$pageDesc  = 'Sign and stamp PDF documents with ease. Add signatures, text stamps, and image stamps.';
$extraHead = '
<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@pdf-lib/fontkit@1.1.1/dist/fontkit.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
<script>pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js";</script>
<style>
.workspace-layout { display: flex; flex-direction: column; gap: 1rem; height: calc(100vh - 200px); min-height: 500px; }
@media (min-width: 992px) { .workspace-layout { flex-direction: row; align-items: flex-start; } }
.toolbar { flex: 0 0 300px; overflow-y: auto; height: 100%; padding-right: 0.5rem; }
.pdf-viewer-area { flex: 1; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 1rem; overflow: auto; height: 100%; display: flex; flex-direction: column; align-items: center; position: relative;}
.pdf-container { position: relative; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); display: inline-block; }
.pdf-container canvas { display: block; }
.draggable-item { position: absolute; cursor: move; border: 2px dashed transparent; user-select: none; transform-origin: center center; }
.draggable-item:hover { border-color: rgba(124,58,237,0.5); }
.draggable-item.active { border-color: var(--tb-primary); background: rgba(124,58,237,0.05); }
.draggable-item .delete-btn { position: absolute; top: -10px; right: -10px; background: #dc3545; color: white; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 20px; cursor: pointer; display: none; font-size: 12px; z-index: 10; }
.draggable-item.active .delete-btn { display: block; }
.draggable-item .rotate-handle { position: absolute; bottom: -15px; left: 50%; transform: translateX(-50%); background: #fff; color: var(--tb-primary); border: 1px solid var(--tb-primary); border-radius: 50%; width: 24px; height: 24px; text-align: center; line-height: 22px; cursor: ew-resize; display: none; font-size: 12px; z-index: 10;}
.draggable-item.active .rotate-handle { display: block; }
#signature-pad { border: 1px solid #dee2e6; border-radius: 0.5rem; cursor: crosshair; background: #fff; touch-action: none; width: 100%; height: 200px; }
.stamp-btn { font-weight: bold; font-size: 1.2rem; border: 2px solid; border-radius: 0.25rem; padding: 0.25rem 0.5rem; cursor: pointer; background: transparent; transition: opacity 0.2s; }
.stamp-btn:hover { opacity: 0.8; }
</style>
';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5" style="max-width:1200px">
    <div class="tb-tool-header">
        <div class="tool-page-icon pdf">
            <i class="bi bi-pen"></i>
        </div>
        <h1>Sign &amp; Stamp PDF</h1>
        <p class="tool-desc">Add your signature, pre-defined stamps, or custom stamps to any page.</p>
        <span class="tb-badge-free me-1">Free</span><span class="tb-badge-instant">⚡ Instant</span>
    </div>

    <!-- Upload Area -->
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

    <!-- Workspace Area -->
    <div id="workspace" class="workspace-layout d-none mt-4">
        <!-- Sidebar Tools -->
        <div class="toolbar d-flex flex-column gap-3 bg-white p-3 rounded-4 shadow-sm border">
            <div class="mb-3">
                <h5 class="fw-bold mb-3">Tools</h5>
                <ul class="nav nav-pills nav-fill mb-3" id="signTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active py-2" id="sig-tab" data-bs-toggle="tab" data-bs-target="#sig-panel" type="button">Sign</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link py-2" id="stamp-tab" data-bs-toggle="tab" data-bs-target="#stamp-panel" type="button">Stamp</button>
                    </li>
                </ul>
                
                <div class="tab-content" id="signTabsContent">
                    <!-- Signature Panel -->
                    <div class="tab-pane fade show active" id="sig-panel">
                        <div class="d-grid gap-2">
                            <button class="btn tb-btn-outline" data-bs-toggle="modal" data-bs-target="#drawModal">
                                <i class="bi bi-brush me-2"></i>Draw Signature
                            </button>
                            <div class="position-relative mt-2">
                                <button class="btn tb-btn-outline w-100">
                                    <i class="bi bi-upload me-2"></i>Upload Signature
                                </button>
                                <input type="file" id="upload-sig" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stamp Panel -->
                    <div class="tab-pane fade" id="stamp-panel">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Standard Stamps</label>
                            <select id="standard-stamp-select" class="form-select mb-2">
                                <option value="" disabled selected>Select a stamp...</option>
                                <option value="APPROVED|#28a745">APPROVED</option>
                                <option value="REJECTED|#dc3545">REJECTED</option>
                                <option value="FINAL|#007bff">FINAL</option>
                                <option value="DRAFT|#6c757d">DRAFT</option>
                                <option value="CONFIDENTIAL|#ffc107">CONFIDENTIAL</option>
                                <option value="URGENT|#fd7e14">URGENT</option>
                            </select>
                            <button class="btn tb-btn-primary btn-sm w-100" onclick="addSelectedStandardStamp()">Add Stamp</button>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Custom Text Stamp</label>
                            <input type="text" id="custom-stamp-text" class="form-control mb-2" placeholder="Text...">
                            <div class="d-flex gap-2">
                                <input type="color" id="custom-stamp-color" class="form-control form-control-color" value="#e5322d">
                                <button class="btn tb-btn-outline btn-sm flex-grow-1" onclick="addCustomStamp()">Add</button>
                            </div>
                        </div>
                        <div class="position-relative">
                            <button class="btn tb-btn-outline btn-sm w-100">
                                <i class="bi bi-image me-2"></i>Image Stamp
                            </button>
                            <input type="file" id="upload-stamp" accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-auto pt-3 border-top">
                <button class="btn btn-danger btn-lg w-100 py-3 shadow-sm text-white fw-bold" id="save-btn" style="background: #e5322d; border: none; border-radius: 12px;">
                    Apply & Download <i class="bi bi-arrow-right-circle ms-2"></i>
                </button>
            </div>
        </div>

        <!-- PDF Viewer -->
        <div class="pdf-viewer-area bg-white p-4 rounded-4 shadow-sm border overflow-auto">
            <div class="d-flex justify-content-between align-items-center w-100 mb-4 px-3 py-2 bg-light rounded-pill border">
                <button class="btn btn-link text-dark p-0" id="prev-page"><i class="bi bi-chevron-left fs-5"></i></button>
                <span class="small fw-bold text-uppercase tracking-wider">Page <span id="page-num" class="text-danger">1</span> of <span id="page-count">1</span></span>
                <button class="btn btn-link text-dark p-0" id="next-page"><i class="bi bi-chevron-right fs-5"></i></button>
            </div>
            <div class="pdf-container mx-auto" id="pdf-container" style="background: #eee;">
                <canvas id="pdf-canvas"></canvas>
                <!-- Overlay elements will go here -->
            </div>
        </div>
    </div>
</div>

<!-- Draw Signature Modal -->
<div class="modal fade" id="drawModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Draw Signature</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <canvas id="signature-pad" width="400" height="200"></canvas>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn tb-btn-outline" onclick="clearSignature()">Clear</button>
        <button type="button" class="btn tb-btn-primary" onclick="saveSignature()">Add to PDF</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/common.js"></script>
<script>
let pdfDoc = null;
let currentFile = null;
let pageNum = 1;
let pageRendering = false;
let pageNumPending = null;
let scale = 1.0;
let canvas = document.getElementById('pdf-canvas');
let ctx = canvas.getContext('2d');
let totalPages = 0;

let elements = []; // Array to store placed signatures/stamps
let activeElement = null;

// Drawing state
let isDrawing = false;
let sigCanvas = document.getElementById('signature-pad');
let sigCtx = sigCanvas.getContext('2d');

// --- Initialization ---
ToolBoxUI.initDropZone('drop-zone', 'file-input', {
    accept: '.pdf',
    onFiles: async (files) => {
        currentFile = files[0];
        document.getElementById('drop-zone').classList.add('d-none');
        document.getElementById('workspace').classList.remove('d-none');
        ToolBoxUI.showProcessing('save-btn', 'Loading PDF...');
        
        const buf = await currentFile.arrayBuffer();
        pdfjsLib.getDocument({ data: buf }).promise.then(pdf => {
            pdfDoc = pdf;
            totalPages = pdf.numPages;
            document.getElementById('page-count').textContent = totalPages;
            renderPage(pageNum);
            ToolBoxUI.hideProcessing('save-btn');
        }).catch(err => {
            ToolBoxUI.showToast('Error loading PDF.', 'danger');
        });
    }
});

// --- PDF Rendering ---
function renderPage(num) {
    pageRendering = true;
    pdfDoc.getPage(num).then(page => {
        let viewport = page.getViewport({ scale: scale });
        
        // Fit to screen width if needed
        const maxWidth = document.querySelector('.pdf-viewer-area').clientWidth - 40;
        if (viewport.width > maxWidth) {
            scale = maxWidth / viewport.width;
            viewport = page.getViewport({ scale: scale });
        }

        canvas.width = viewport.width;
        canvas.height = viewport.height;
        document.getElementById('pdf-container').style.width = viewport.width + 'px';
        document.getElementById('pdf-container').style.height = viewport.height + 'px';

        let renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };
        let renderTask = page.render(renderContext);

        renderTask.promise.then(() => {
            pageRendering = false;
            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
            document.getElementById('page-num').textContent = num;
            updateOverlay();
        });
    });
}

function queueRenderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
    } else {
        renderPage(num);
    }
}

document.getElementById('prev-page').addEventListener('click', () => {
    if (pageNum <= 1) return;
    pageNum--;
    queueRenderPage(pageNum);
});

document.getElementById('next-page').addEventListener('click', () => {
    if (pageNum >= totalPages) return;
    pageNum++;
    queueRenderPage(pageNum);
});

// --- Overlay Management ---
function updateOverlay() {
    // Remove current overlay elements
    const container = document.getElementById('pdf-container');
    container.querySelectorAll('.draggable-item').forEach(el => el.remove());

    // Add elements for current page
    elements.filter(el => el.pageNum === pageNum).forEach(el => {
        const div = document.createElement('div');
        div.className = 'draggable-item';
        div.id = 'elem-' + el.id;
        
        // We store coordinates relative to the canvas width/height ratio so it scales
        const scaledX = el.x * canvas.width;
        const scaledY = el.y * canvas.height;
        const scaledW = el.width * canvas.width;
        
        div.style.left = scaledX + 'px';
        div.style.top = scaledY + 'px';
        div.style.width = scaledW + 'px';
        div.style.transform = `rotate(${el.rotation}deg)`;

        if (el.type === 'image') {
            const img = document.createElement('img');
            img.src = el.content;
            img.style.width = '100%';
            img.style.height = 'auto';
            img.style.pointerEvents = 'none';
            div.appendChild(img);
        } else if (el.type === 'text') {
            const span = document.createElement('span');
            span.textContent = el.content;
            span.style.color = el.color;
            span.style.fontSize = (el.fontSize * canvas.width) + 'px';
            span.style.fontWeight = 'bold';
            span.style.border = `2px solid ${el.color}`;
            span.style.padding = '5px 10px';
            span.style.borderRadius = '5px';
            span.style.display = 'inline-block';
            span.style.pointerEvents = 'none';
            span.style.whiteSpace = 'nowrap';
            div.appendChild(span);
        }

        const delBtn = document.createElement('div');
        delBtn.className = 'delete-btn';
        delBtn.innerHTML = '<i class="bi bi-x"></i>';
        delBtn.onclick = (e) => {
            e.stopPropagation();
            elements = elements.filter(item => item.id !== el.id);
            updateOverlay();
        };
        div.appendChild(delBtn);

        const rotHandle = document.createElement('div');
        rotHandle.className = 'rotate-handle';
        rotHandle.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
        div.appendChild(rotHandle);

        setupDraggable(div, el);
        setupRotatable(rotHandle, div, el);
        
        container.appendChild(div);
    });
}

function setupDraggable(div, el) {
    let isDragging = false;
    let startX, startY;

    div.addEventListener('mousedown', (e) => {
        if (e.target.closest('.delete-btn') || e.target.closest('.rotate-handle')) return;
        isDragging = true;
        startX = e.clientX - div.offsetLeft;
        startY = e.clientY - div.offsetTop;
        
        document.querySelectorAll('.draggable-item').forEach(el => el.classList.remove('active'));
        div.classList.add('active');
        activeElement = el.id;
        e.stopPropagation();
    });

    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        let newX = e.clientX - startX;
        let newY = e.clientY - startY;
        
        // Constrain to container
        const container = document.getElementById('pdf-container');
        newX = Math.max(0, Math.min(newX, container.clientWidth - div.clientWidth));
        newY = Math.max(0, Math.min(newY, container.clientHeight - div.clientHeight));

        div.style.left = newX + 'px';
        div.style.top = newY + 'px';

        el.x = newX / canvas.width;
        el.y = newY / canvas.height;
    });

    document.addEventListener('mouseup', () => {
        isDragging = false;
    });
}

function setupRotatable(handle, div, el) {
    let isRotating = false;
    let originX, originY;

    handle.addEventListener('mousedown', (e) => {
        isRotating = true;
        const rect = div.getBoundingClientRect();
        originX = rect.left + rect.width / 2;
        originY = rect.top + rect.height / 2;
        e.stopPropagation();
        e.preventDefault();
    });

    document.addEventListener('mousemove', (e) => {
        if (!isRotating) return;
        const angle = Math.atan2(e.clientY - originY, e.clientX - originX);
        let deg = (angle * 180 / Math.PI) + 90; // +90 because handle is at bottom
        
        // Snap to 45 deg intervals if shift is pressed, or just snap near 0, 90, 180, 270
        if (Math.abs(deg % 90) < 5) deg = Math.round(deg / 90) * 90;
        
        div.style.transform = `rotate(${deg}deg)`;
        el.rotation = deg;
    });

    document.addEventListener('mouseup', () => {
        isRotating = false;
    });
}

// Deselect on clicking container
document.getElementById('pdf-container').addEventListener('mousedown', (e) => {
    if (e.target.id === 'pdf-canvas') {
        document.querySelectorAll('.draggable-item').forEach(el => el.classList.remove('active'));
        activeElement = null;
    }
});

// --- Element Creation ---
function addImageElement(base64Str, originalWidth, originalHeight) {
    // Default size relative to canvas
    let w = 0.3; // 30% of page width
    let id = Date.now();
    elements.push({
        id: id,
        type: 'image',
        content: base64Str,
        pageNum: pageNum,
        x: 0.1,
        y: 0.1,
        width: w,
        aspectRatio: originalHeight / originalWidth,
        rotation: 0
    });
    updateOverlay();
}

function addTextStamp(text, color) {
    let id = Date.now();
    elements.push({
        id: id,
        type: 'text',
        content: text,
        color: color,
        pageNum: pageNum,
        x: 0.1,
        y: 0.1,
        width: 0.3, // Approximate width
        fontSize: 0.05, // 5% of page width
        rotation: 0
    });
    updateOverlay();
}

function addSelectedStandardStamp() {
    const sel = document.getElementById('standard-stamp-select');
    if (sel.value) {
        const [text, color] = sel.value.split('|');
        addTextStamp(text, color);
        sel.value = "";
    }
}

function addCustomStamp() {
    const text = document.getElementById('custom-stamp-text').value.trim();
    const color = document.getElementById('custom-stamp-color').value;
    if (text) {
        addTextStamp(text, color);
        document.getElementById('custom-stamp-text').value = '';
    }
}

// --- Signature Pad ---
function initSigPad() {
    sigCtx.lineWidth = 3;
    sigCtx.lineCap = 'round';
    sigCtx.lineJoin = 'round';
    sigCtx.strokeStyle = '#000';
    
    // Resize for high DPI
    const rect = sigCanvas.getBoundingClientRect();
    sigCanvas.width = rect.width;
    sigCanvas.height = rect.height;

    const getPos = (e) => {
        if (e.touches) {
            const rect = sigCanvas.getBoundingClientRect();
            return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
        }
        return { x: e.offsetX, y: e.offsetY };
    };

    const start = (e) => {
        isDrawing = true;
        const pos = getPos(e);
        sigCtx.beginPath();
        sigCtx.moveTo(pos.x, pos.y);
        e.preventDefault();
    };

    const draw = (e) => {
        if (!isDrawing) return;
        const pos = getPos(e);
        sigCtx.lineTo(pos.x, pos.y);
        sigCtx.stroke();
        e.preventDefault();
    };

    const stop = () => { isDrawing = false; };

    sigCanvas.addEventListener('mousedown', start);
    sigCanvas.addEventListener('mousemove', draw);
    sigCanvas.addEventListener('mouseup', stop);
    sigCanvas.addEventListener('mouseout', stop);

    sigCanvas.addEventListener('touchstart', start, {passive: false});
    sigCanvas.addEventListener('touchmove', draw, {passive: false});
    sigCanvas.addEventListener('touchend', stop);
}

document.getElementById('drawModal').addEventListener('shown.bs.modal', initSigPad);

function clearSignature() {
    sigCtx.clearRect(0, 0, sigCanvas.width, sigCanvas.height);
}

function saveSignature() {
    // Check if empty by looking at pixel data (simplified)
    const data = sigCtx.getImageData(0, 0, sigCanvas.width, sigCanvas.height).data;
    let isEmpty = true;
    for (let i = 0; i < data.length; i += 4) {
        if (data[i+3] > 0) { isEmpty = false; break; }
    }
    
    if (!isEmpty) {
        // Crop transparent edges
        let minX = sigCanvas.width, minY = sigCanvas.height, maxX = 0, maxY = 0;
        for (let y = 0; y < sigCanvas.height; y++) {
            for (let x = 0; x < sigCanvas.width; x++) {
                const alpha = data[(y * sigCanvas.width + x) * 4 + 3];
                if (alpha > 0) {
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
            }
        }
        
        const cropW = Math.max(1, maxX - minX);
        const cropH = Math.max(1, maxY - minY);
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = cropW;
        tempCanvas.height = cropH;
        tempCanvas.getContext('2d').putImageData(sigCtx.getImageData(minX, minY, cropW, cropH), 0, 0);
        
        const dataUrl = tempCanvas.toDataURL('image/png');
        addImageElement(dataUrl, cropW, cropH);
    }
    bootstrap.Modal.getInstance(document.getElementById('drawModal')).hide();
    clearSignature();
}

// --- Uploads ---
document.getElementById('upload-sig').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                addImageElement(e.target.result, img.width, img.height);
            }
            img.src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    }
});

document.getElementById('upload-stamp').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                addImageElement(e.target.result, img.width, img.height);
            }
            img.src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    }
});


// --- Save Logic ---
document.getElementById('save-btn').addEventListener('click', async () => {
    if (!currentFile) return;
    ToolBoxUI.showProcessing('save-btn', 'Applying...');
    
    try {
        const buf = await currentFile.arrayBuffer();
        const pdfDocMod = await PDFLib.PDFDocument.load(buf, { ignoreEncryption: true });
        
        // Register fontkit to draw text
        pdfDocMod.registerFontkit(window.fontkit);
        
        // Try to embed a standard font or custom font
        const font = await pdfDocMod.embedFont(PDFLib.StandardFonts.HelveticaBold);
        
        const pages = pdfDocMod.getPages();

        for (const el of elements) {
            const page = pages[el.pageNum - 1];
            const { width, height } = page.getSize();
            
            const domEl = document.getElementById('elem-' + el.id);
            if (!domEl) continue;
            
            const rect = domEl.getBoundingClientRect();
            const canvasRect = canvas.getBoundingClientRect();
            
            const pdfElW = (rect.width / canvasRect.width) * width;
            const pdfElH = (rect.height / canvasRect.height) * height;
            
            const pdfX = el.x * width;
            const pdfY = height - (el.y * height) - pdfElH;

            // PDF-lib draws from bottom-left. Rotation also happens from bottom-left.
            // To rotate around the center, we calculate the offset.
            const cx = pdfX + pdfElW / 2;
            const cy = pdfY + pdfElH / 2;

            if (el.type === 'image') {
                const imgBytes = Uint8Array.from(atob(el.content.split(',')[1]), c => c.charCodeAt(0));
                let img;
                if (el.content.startsWith('data:image/png')) img = await pdfDocMod.embedPng(imgBytes);
                else img = await pdfDocMod.embedJpg(imgBytes);
                
                page.drawImage(img, {
                    x: cx - (pdfElW/2)*Math.cos(el.rotation*Math.PI/180) - (pdfElH/2)*Math.sin(el.rotation*Math.PI/180),
                    y: cy + (pdfElW/2)*Math.sin(el.rotation*Math.PI/180) - (pdfElH/2)*Math.cos(el.rotation*Math.PI/180),
                    width: pdfElW,
                    height: pdfElH,
                    rotate: PDFLib.degrees(-el.rotation),
                });
            } else if (el.type === 'text') {
                const hex = el.color.replace('#', '');
                const r = parseInt(hex.substring(0,2), 16) / 255;
                const g = parseInt(hex.substring(2,4), 16) / 255;
                const b = parseInt(hex.substring(4,6), 16) / 255;
                
                // For text, PDF-lib doesn't support border rendering directly on text, 
                // so we just draw the text.
                const fontSize = (el.fontSize * width) * 0.8;
                
                // Quick hack for rotation offset
                const drawX = cx - (pdfElW/2)*Math.cos(el.rotation*Math.PI/180) - (pdfElH/2)*Math.sin(el.rotation*Math.PI/180);
                const drawY = cy + (pdfElW/2)*Math.sin(el.rotation*Math.PI/180) - (pdfElH/2)*Math.cos(el.rotation*Math.PI/180);

                page.drawRectangle({
                    x: drawX,
                    y: drawY,
                    width: pdfElW,
                    height: pdfElH,
                    borderColor: PDFLib.rgb(r, g, b),
                    borderWidth: 2,
                    rotate: PDFLib.degrees(-el.rotation),
                });

                page.drawText(el.content, {
                    x: drawX + pdfElW * 0.1,
                    y: drawY + pdfElH * 0.3,
                    size: fontSize,
                    font: font,
                    color: PDFLib.rgb(r, g, b),
                    rotate: PDFLib.degrees(-el.rotation),
                });
            }
        }

        const pdfBytes = await pdfDocMod.save();
        const blob = new Blob([pdfBytes], { type: 'application/pdf' });
        
        // Hide workspace and show results
        document.getElementById('workspace').classList.add('d-none');
        
        // Create results area if not exists
        let resultsDiv = document.getElementById('results');
        if (!resultsDiv) {
            resultsDiv = document.createElement('div');
            resultsDiv.id = 'results';
            resultsDiv.className = 'mt-4';
            document.querySelector('.container').appendChild(resultsDiv);
        }
        resultsDiv.classList.remove('d-none');
        
        ToolBoxUI.createDownloadBtn(blob, 'signed_' + currentFile.name, 'results');
        
    } catch (e) {
        console.error(e);
        ToolBoxUI.showToast('Error applying changes.', 'danger');
    }
    ToolBoxUI.hideProcessing('save-btn');
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
