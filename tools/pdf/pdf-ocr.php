<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'OCR / Extract Text', 'slug' => 'pdf-ocr', 'icon' => 'textarea-t', 'category' => 'pdf',
    'description' => 'Extract text from scanned PDFs using Tesseract OCR.',
    'accept' => '.pdf',
    'options_html' => '<div class="tb-dash-card"><label class="form-label fw-semibold">Language</label>
        <select name="lang" class="form-select tb-form-control">
            <option value="eng">English</option><option value="fra">French</option>
            <option value="deu">German</option><option value="spa">Spanish</option>
            <option value="ara">Arabic</option><option value="chi_sim">Chinese (Simplified)</option>
        </select></div>'
]); ?>
