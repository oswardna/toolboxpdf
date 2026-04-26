<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Excel to PDF', 
    'slug' => 'excel-to-pdf', 
    'icon' => 'file-earmark-excel', 
    'category' => 'pdf',
    'description' => 'Convert Excel spreadsheets (.xlsx, .xls) to PDF instantly in your browser.', 
    'accept' => '.xlsx,.xls',
    'extra_scripts' => [
        'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js'
    ]
]); ?>

<script>
async function onProcess(file) {
    const reader = new FileReader();
    reader.onload = async function(e) {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, {type: 'array'});
        
        // 1. Convert first sheet to HTML
        const firstSheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[firstSheetName];
        const html = XLSX.utils.sheet_to_html(worksheet);

        // 2. Create a hidden element to render the HTML
        const element = document.createElement('div');
        element.style.padding = '20px';
        element.style.backgroundColor = 'white';
        element.style.color = 'black';
        element.style.fontSize = '12px';
        element.innerHTML = `<h3>${firstSheetName}</h3>` + html;
        
        // Add some basic table styling for the PDF
        const style = document.createElement('style');
        style.innerHTML = 'table { border-collapse: collapse; width: 100%; } td, th { border: 1px solid #ccc; padding: 5px; }';
        element.appendChild(style);
        
        document.body.appendChild(element);

        // 3. Convert HTML to PDF
        const opt = {
            margin:       10,
            filename:     file.name.replace(/\.[^/.]+$/, "") + '.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
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
