/**
 * ToolBox — Client-Side PDF Processing
 * Uses pdf-lib for manipulation and PDF.js for rendering previews
 */

const ClientPDF = {

    /**
     * Split a PDF into multiple files by page ranges
     * @param {File} file - PDF file
     * @param {Array<Array<number>>} ranges - e.g. [[1,2,3],[5],[7,8,9]]
     * @returns {Array<{name:string, blob:Blob}>}
     */
    async split(file, ranges) {
        const arrayBuffer = await file.arrayBuffer();
        const srcDoc = await PDFLib.PDFDocument.load(arrayBuffer, { ignoreEncryption: true });
        const totalPages = srcDoc.getPageCount();
        const results = [];

        for (const range of ranges) {
            const newDoc = await PDFLib.PDFDocument.create();
            const indices = range.map(i => i - 1).filter(i => i >= 0 && i < totalPages);
            if (indices.length === 0) continue;
            const pages = await newDoc.copyPages(srcDoc, indices);
            pages.forEach(p => newDoc.addPage(p));
            const bytes = await newDoc.save();
            const first = range[0];
            const last = range[range.length - 1];
            results.push({
                name: `split_${first}-${last}.pdf`,
                blob: new Blob([bytes], { type: 'application/pdf' })
            });
        }
        return results;
    },

    /**
     * Merge multiple PDF files into one
     * @param {File[]} files
     * @returns {Blob}
     */
    async merge(files) {
        const merged = await PDFLib.PDFDocument.create();
        for (const file of files) {
            const buf = await file.arrayBuffer();
            const doc = await PDFLib.PDFDocument.load(buf, { ignoreEncryption: true });
            const pages = await merged.copyPages(doc, doc.getPageIndices());
            pages.forEach(p => merged.addPage(p));
        }
        const bytes = await merged.save();
        return new Blob([bytes], { type: 'application/pdf' });
    },

    /**
     * Rotate pages in a PDF
     * @param {File} file
     * @param {number} degrees - 90, 180, 270
     * @param {Array<number>|null} pageIndices - null = all pages (1-indexed)
     * @returns {Blob}
     */
    async rotate(file, degrees, pageIndices = null) {
        const arrayBuffer = await file.arrayBuffer();
        const doc = await PDFLib.PDFDocument.load(arrayBuffer, { ignoreEncryption: true });
        const pages = doc.getPages();

        const indices = pageIndices
            ? pageIndices.map(i => i - 1)
            : pages.map((_, i) => i);

        for (const idx of indices) {
            if (idx >= 0 && idx < pages.length) {
                const page = pages[idx];
                const current = page.getRotation().angle;
                page.setRotation(PDFLib.degrees((current + degrees) % 360));
            }
        }

        const bytes = await doc.save();
        return new Blob([bytes], { type: 'application/pdf' });
    },

    /**
     * Get page count of a PDF
     * @param {File} file
     * @returns {number}
     */
    async getPageCount(file) {
        const buf = await file.arrayBuffer();
        const doc = await PDFLib.PDFDocument.load(buf, { ignoreEncryption: true });
        return doc.getPageCount();
    },

    /**
     * Render a single page as canvas using PDF.js
     * @param {File} file
     * @param {number} pageNum - 1-indexed
     * @param {number} scale
     * @returns {HTMLCanvasElement}
     */
    async renderPage(file, pageNum, scale = 0.5) {
        const buf = await file.arrayBuffer();
        const pdf = await pdfjsLib.getDocument({ data: buf }).promise;
        const page = await pdf.getPage(pageNum);
        const viewport = page.getViewport({ scale });
        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        const ctx = canvas.getContext('2d');
        await page.render({ canvasContext: ctx, viewport }).promise;
        return canvas;
    },

    /**
     * Render all pages as thumbnails
     * @param {File} file
     * @param {number} scale
     * @returns {Array<HTMLCanvasElement>}
     */
    async renderAllPages(file, scale = 0.3) {
        const buf = await file.arrayBuffer();
        const pdf = await pdfjsLib.getDocument({ data: buf }).promise;
        const canvases = [];
        for (let i = 1; i <= pdf.numPages; i++) {
            const page = await pdf.getPage(i);
            const viewport = page.getViewport({ scale });
            const canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            const ctx = canvas.getContext('2d');
            await page.render({ canvasContext: ctx, viewport }).promise;
            canvases.push(canvas);
        }
        return canvases;
    },

    /**
     * Parse page range string like "1-3, 5, 7-9"
     * @param {string} rangeStr
     * @param {number} maxPage
     * @returns {Array<Array<number>>}
     */
    parseRanges(rangeStr, maxPage) {
        const ranges = [];
        const parts = rangeStr.split(',').map(s => s.trim()).filter(Boolean);

        for (const part of parts) {
            if (part.includes('-')) {
                const [startStr, endStr] = part.split('-').map(s => s.trim());
                const start = Math.max(1, parseInt(startStr) || 1);
                const end = Math.min(maxPage, parseInt(endStr) || maxPage);
                if (start <= end) {
                    const range = [];
                    for (let i = start; i <= end; i++) range.push(i);
                    ranges.push(range);
                }
            } else {
                const num = parseInt(part);
                if (num >= 1 && num <= maxPage) {
                    ranges.push([num]);
                }
            }
        }
        return ranges;
    },

    /**
     * Download a blob as a file
     */
    download(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 5000);
    },

    /**
     * Download multiple files as ZIP
     */
    async downloadAsZip(files, zipName = 'toolbox_result.zip') {
        const zip = new JSZip();
        for (const f of files) {
            zip.file(f.name, f.blob);
        }
        const content = await zip.generateAsync({ type: 'blob' });
        this.download(content, zipName);
    }
};
