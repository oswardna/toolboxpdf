<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Word to PDF', 
    'slug' => 'word-to-pdf', 
    'icon' => 'file-earmark-word', 
    'category' => 'pdf',
    'description' => 'Convert Word documents (.docx) to PDF with high fidelity. Runs in your browser.', 
    'accept' => '.docx',
    'extra_scripts' => [
        'https://unpkg.com/jszip/dist/jszip.min.js',
        'https://unpkg.com/docx-preview/dist/docx-preview.js',
        'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js'
    ]
]); ?>

<script>
async function onProcess(file) {
    const reader = new FileReader();
    reader.onload = async function(e) {
        const arrayBuffer = e.target.result;
        
        // 1. Create a hidden element to render the Word document
        const element = document.createElement('div');
        element.style.padding = '0';
        element.style.margin = '0';
        element.style.backgroundColor = 'white';
        element.style.width = '800px'; // Standard A4-ish width
        document.body.appendChild(element);

        try {
            // 2. Render Word to HTML using docx-preview (much better fidelity than Mammoth)
            await docx.renderAsync(arrayBuffer, element, null, {
                className: "docx", //prefix class name for css rules
                inWrapper: true, //enables rendering of wrapper around document content
                ignoreWidth: false, //disables rendering width of document
                ignoreHeight: false, //disables rendering height of document
                ignoreFonts: false, //disables fonts rendering
                breakPages: true, //enables page breaks rendering
                ignoreLastRenderedPageBreak: false, //disables page breaks rendering
                experimental: true, //enables experimental features (for example: tabs, table of contents, etc.)
                trimXmlDeclaration: true, //if true, xml declaration will be removed from xml documents
                useBase64URL: true, //if true, images, fonts, etc. will be converted to base64 url, otherwise to blob url
                usePrettify: true, //if true, xml documents will be prettified
            });

            // 3. Convert the rendered element to PDF
            const opt = {
                margin:       0,
                filename:     file.name.replace('.docx', '.pdf'),
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            const pdfBlob = await html2pdf().set(opt).from(element).output('blob');
            const url = URL.createObjectURL(pdfBlob);
            ToolBoxUI.showDownloadResult(url, opt.filename);
        } catch (err) {
            console.error(err);
            ToolBoxUI.showToast('Rendering error: ' + err.message, 'danger');
        } finally {
            document.body.removeChild(element);
        }
    };
    reader.readAsArrayBuffer(file);
}
</script>

<style>
/* Ensure docx-preview styles are captured correctly */
.docx-wrapper {
    background: white !important;
    padding: 0 !important;
}
.docx {
    margin-bottom: 0 !important;
    box-shadow: none !important;
}
</style>
