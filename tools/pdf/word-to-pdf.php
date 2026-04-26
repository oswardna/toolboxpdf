<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Word to PDF', 
    'slug' => 'word-to-pdf', 
    'icon' => 'file-earmark-word', 
    'category' => 'pdf',
    'description' => 'Convert Word documents (.docx) to PDF with the highest possible browser fidelity.', 
    'accept' => '.docx',
    'extra_scripts' => [
        'https://unpkg.com/jszip/dist/jszip.min.js',
        'https://unpkg.com/docx-preview/dist/docx-preview.js',
        'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js'
    ]
]); ?>

<script>
async function onProcess(file) {
    const reader = new FileReader();
    reader.onload = async function(e) {
        const arrayBuffer = e.target.result;
        
        // Create a dedicated container for rendering
        const container = document.createElement('div');
        container.style.position = 'absolute';
        container.style.left = '-9999px';
        container.style.width = '210mm'; // A4 Width
        document.body.appendChild(container);

        try {
            await docx.renderAsync(arrayBuffer, container, null, {
                className: "docx",
                inWrapper: true,
                ignoreWidth: false,
                ignoreHeight: false,
                breakPages: true,
                experimental: true,
                useBase64URL: true
            });

            // Use jspdf's advanced html method for better fidelity
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({
                orientation: 'p',
                unit: 'mm',
                format: 'a4',
                compress: true
            });

            // Target the rendered content wrapper
            const content = container.querySelector('.docx-wrapper');
            
            await doc.html(content, {
                callback: function (doc) {
                    const url = doc.output('bloburl');
                    ToolBoxUI.showDownloadResult(url, file.name.replace('.docx', '.pdf'));
                },
                x: 0,
                y: 0,
                width: 210, // Target width in mm
                windowWidth: 794, // A4 width in pixels at 96 DPI
                autoPaging: 'text',
                html2canvas: {
                    scale: 2, // Higher scale for better text sharpness
                    useCORS: true,
                    letterRendering: true
                }
            });

        } catch (err) {
            console.error(err);
            ToolBoxUI.showToast('Rendering error: ' + err.message, 'danger');
        } finally {
            // Keep container temporarily for the callback to finish
            setTimeout(() => document.body.removeChild(container), 5000);
        }
    };
    reader.readAsArrayBuffer(file);
}
</script>

<style>
.docx-wrapper {
    background: white !important;
    padding: 20mm !important; /* Standard Word margins */
}
.docx {
    margin: 0 !important;
    padding: 0 !important;
    box-shadow: none !important;
}
</style>
