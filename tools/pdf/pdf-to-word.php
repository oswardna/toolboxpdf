<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'PDF to Word', 
    'slug' => 'pdf-to-word', 
    'icon' => 'file-earmark-word', 
    'category' => 'pdf',
    'description' => 'Convert PDF files to editable Word documents (.docx). Runs 100% in your browser.', 
    'accept' => '.pdf',
    'extra_scripts' => [
        'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js',
        'https://cdn.jsdelivr.net/npm/docx@8.2.2/build/index.js'
    ]
]); ?>

<script>
async function onProcess(file) {
    const arrayBuffer = await file.arrayBuffer();
    
    // 1. Load PDF using PDF.js
    const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    const { Document, Packer, Paragraph, TextRun } = window.docx;
    
    const sections = [];

    for (let i = 1; i <= pdf.numPages; i++) {
        const page = await pdf.getPage(i);
        const textContent = await page.getTextContent();
        
        // Group items by their vertical position (lines)
        const lines = {};
        textContent.items.forEach(item => {
            const y = Math.round(item.transform[5]);
            if (!lines[y]) lines[y] = [];
            lines[y].push(item);
        });

        // Sort lines from top to bottom
        const sortedY = Object.keys(lines).sort((a, b) => b - a);
        
        const children = [];
        sortedY.forEach(y => {
            // Sort items in line from left to right
            const items = lines[y].sort((a, b) => a.transform[4] - b.transform[4]);
            const lineText = items.map(item => item.str).join(' ');
            
            children.push(new Paragraph({
                children: [new TextRun(lineText)]
            }));
        });

        sections.push({
            properties: {},
            children: children
        });
        
        ToolBoxUI.showToast(`Extracted text from page ${i} of ${pdf.numPages}`, 'info');
    }

    // 2. Create Word Document
    const doc = new Document({
        sections: sections
    });

    const blob = await Packer.toBlob(doc);
    const url = URL.createObjectURL(blob);
    ToolBoxUI.showDownloadResult(url, file.name.replace('.pdf', '.docx'));
}
</script>
