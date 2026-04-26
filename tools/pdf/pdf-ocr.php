<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'PDF OCR', 
    'slug' => 'pdf-ocr', 
    'icon' => 'eye', 
    'category' => 'pdf',
    'description' => 'Extract text from scanned PDFs using OCR. Runs 100% in your browser.', 
    'accept' => '.pdf',
    'options_html' => '<div class="tb-dash-card"><div class="mb-3">
        <label class="form-label fw-semibold">Language</label>
        <select name="lang" class="form-select tb-form-control">
            <option value="eng">English</option>
            <option value="spa">Spanish</option>
            <option value="fra">French</option>
            <option value="deu">German</option>
        </select></div></div>',
    'extra_scripts' => [
        'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js',
        'https://cdn.jsdelivr.net/npm/tesseract.js@5.0.3/dist/tesseract.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js'
    ]
]); ?>

<script>
async function onProcess(file) {
    const lang = document.querySelector('[name="lang"]').value;
    const arrayBuffer = await file.arrayBuffer();
    
    // 1. Load PDF using PDF.js
    const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    const { jsPDF } = window.jspdf;
    const outDoc = new jsPDF();
    
    ToolBoxUI.showToast('Starting OCR... this may take a minute.', 'info');

    for (let i = 1; i <= pdf.numPages; i++) {
        const page = await pdf.getPage(i);
        const viewport = page.getViewport({ scale: 2 });
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        await page.render({ canvasContext: context, viewport: viewport }).promise;

        // 2. Perform OCR on the page canvas
        const result = await Tesseract.recognize(canvas, lang, {
            logger: m => console.log(m)
        });

        if (i > 1) outDoc.addPage();
        outDoc.text(result.data.text, 10, 10, { maxWidth: 190 });
        
        ToolBoxUI.showToast(`Processed page ${i} of ${pdf.numPages}`, 'info');
    }

    const pdfBlob = outDoc.output('blob');
    const url = URL.createObjectURL(pdfBlob);
    ToolBoxUI.showDownloadResult(url, 'ocr_result.pdf');
}
</script>
