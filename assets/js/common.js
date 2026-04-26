/**
 * ToolBox — Common UI utilities
 */

const ToolBoxUI = {

    /**
     * Initialize a drop zone
     */
    initDropZone(dropZoneId, fileInputId, options = {}) {
        const zone = document.getElementById(dropZoneId);
        const input = document.getElementById(fileInputId);
        if (!zone || !input) return;

        const accept = options.accept || '*';
        const multiple = options.multiple || false;
        const onFiles = options.onFiles || (() => {});
        const maxSizeMB = options.maxSizeMB || (window.TB_CONFIG ? window.TB_CONFIG.maxSizeMB : 50);

        if (multiple) input.setAttribute('multiple', '');

        zone.addEventListener('click', (e) => {
            if (e.target !== input && !e.target.closest('label')) {
                input.click();
            }
        });

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('drag-over');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');
            const files = Array.from(e.dataTransfer.files);
            this._validateAndProcess(files, maxSizeMB, onFiles, accept);
        });

        input.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            this._validateAndProcess(files, maxSizeMB, onFiles, accept);
        });
    },

    _validateAndProcess(files, maxSizeMB, callback, accept = '*') {
        const valid = [];
        const extensions = accept !== '*' ? accept.split(',').map(ext => ext.trim().toLowerCase()) : null;

        for (const file of files) {
            // Check size
            if (file.size > maxSizeMB * 1024 * 1024) {
                this.showToast(`${file.name} exceeds ${maxSizeMB}MB limit`, 'danger');
                continue;
            }
            
            // Check type (simple extension check)
            if (extensions) {
                const fileExt = '.' + file.name.split('.').pop().toLowerCase();
                const isAccepted = extensions.some(ext => {
                    if (ext === fileExt) return true;
                    if (ext === 'image/*' && file.type.startsWith('image/')) return true;
                    if (ext === 'video/*' && file.type.startsWith('video/')) return true;
                    return false;
                });
                
                if (!isAccepted) {
                    this.showToast(`${file.name} is not a supported file type`, 'warning');
                    continue;
                }
            }
            
            valid.push(file);
        }
        if (valid.length > 0) callback(valid);
    },

    /**
     * Show Bootstrap toast
     */
    showToast(message, type = 'info') {
        const container = document.getElementById('toast-container') || this._createToastContainer();
        const id = 'toast-' + Date.now();
        const html = `
            <div id="${id}" class="toast align-items-center text-bg-${type} border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        setTimeout(() => {
            const el = document.getElementById(id);
            if (el) el.remove();
        }, 4000);
    },

    _createToastContainer() {
        const c = document.createElement('div');
        c.id = 'toast-container';
        c.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        c.style.zIndex = '9999';
        document.body.appendChild(c);
        return c;
    },

    /**
     * Show upgrade modal
     */
    showUpgradeModal() {
        const modal = new bootstrap.Modal(document.getElementById('upgradeModal'));
        modal.show();
    },

    /**
     * Show processing state
     */
    showProcessing(btnId, text = 'Processing...') {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn._originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${text}`;
    },

    /**
     * Reset processing state
     */
    hideProcessing(btnId) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.disabled = false;
        btn.innerHTML = btn._originalHTML || 'Process';
    },

    /**
     * Format file size
     */
    formatSize(bytes) {
        if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
        return bytes + ' B';
    },

    /**
     * Create download button
     */
    createDownloadBtn(blob, filename, parentId) {
        const parent = document.getElementById(parentId);
        if (!parent) return;
        parent.classList.remove('d-none');
        parent.innerHTML = `
            <div class="tb-dash-card text-center">
                <i class="bi bi-check-circle text-success" style="font-size:2.5rem"></i>
                <h5 class="fw-bold mt-2 mb-1">Done!</h5>
                <p class="text-muted small mb-3">Your file is ready to download (${this.formatSize(blob.size)})</p>
                <button class="btn tb-btn-primary btn-lg" id="download-result-btn">
                    <i class="bi bi-download me-2"></i>Download ${filename}
                </button>
            </div>`;
        document.getElementById('download-result-btn').addEventListener('click', () => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = filename;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(url), 5000);
        });
    },

    /**
     * Create multi-file download area
     */
    createMultiDownload(files, parentId) {
        const parent = document.getElementById(parentId);
        if (!parent) return;
        parent.classList.remove('d-none');
        let html = `
            <div class="tb-dash-card">
                <div class="text-center mb-3">
                    <i class="bi bi-check-circle text-success" style="font-size:2rem"></i>
                    <h5 class="fw-bold mt-2 mb-0">Done! ${files.length} files created</h5>
                </div>
                <div class="list-group list-group-flush mb-3">`;
        files.forEach((f, i) => {
            html += `<div class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-secondary">
                <span class="small">${f.name} (${this.formatSize(f.blob.size)})</span>
                <button class="btn btn-sm tb-btn-outline download-single" data-idx="${i}"><i class="bi bi-download"></i></button>
            </div>`;
        });
        html += `</div>
            <button class="btn tb-btn-primary w-100" id="download-all-zip">
                <i class="bi bi-file-zip me-2"></i>Download All as ZIP
            </button></div>`;
        parent.innerHTML = html;

        parent.querySelectorAll('.download-single').forEach(btn => {
            btn.addEventListener('click', () => {
                const f = files[parseInt(btn.dataset.idx)];
                ClientPDF.download(f.blob, f.name);
            });
        });

        document.getElementById('download-all-zip').addEventListener('click', async () => {
            await ClientPDF.downloadAsZip(files);
        });
    }
};
