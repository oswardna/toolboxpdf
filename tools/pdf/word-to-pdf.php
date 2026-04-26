<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Word to PDF', 
    'slug' => 'word-to-pdf', 
    'icon' => 'file-earmark-word', 
    'category' => 'pdf',
    'description' => 'Convert Word documents (.docx) to PDF instantly in your browser.', 
    'accept' => '.docx',
    'extra_scripts' => [
        'https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js'
    ]
]); ?>

<script>
async function onProcess(file) {
    const reader = new FileReader();
    reader.onload = async function(e) {
        const arrayBuffer = e.target.result;
        
        // 1. Convert Word to HTML using Mammoth
        const result = await mammoth.convertToHtml({ arrayBuffer: arrayBuffer });
        const html = result.value;
        const messages = result.messages;
        console.log(messages);

        // 2. Create a hidden element to render the HTML
        const element = document.createElement('div');
        element.style.padding = '40px';
        element.style.backgroundColor = 'white';
        element.style.color = 'black';
        element.style.width = '800px';
        element.innerHTML = html;
        document.body.appendChild(element);

        // 3. Convert HTML to PDF using html2pdf
        const opt = {
            margin:       10,
            filename:     file.name.replace('.docx', '.pdf'),
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        try {
            const pdfBlob = await html2pdf().set(opt).from(element).output('blob');
            const url = URL.createObjectURL(pdfBlob);
            ToolBoxUI.showDownloadResult(url, opt.filename);
        } finally {
            document.body.removeChild(element);
        }
    };
    reader.readAsArrayBuffer(file);
}
</script>
