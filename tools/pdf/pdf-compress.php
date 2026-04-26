<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Compress PDF', 'slug' => 'pdf-compress', 'icon' => 'file-earmark-zip', 'category' => 'pdf',
    'description' => 'Reduce PDF file size while maintaining quality using Ghostscript.',
    'accept' => '.pdf',
    'options_html' => '<div class="tb-dash-card"><label class="form-label fw-semibold">Quality Level</label>
        <select name="quality" class="form-select tb-form-control">
            <option value="1">Ebook (Good balance)</option>
            <option value="0">Screen (Smallest file)</option>
            <option value="2">Printer (High quality)</option>
            <option value="3">Prepress (Maximum quality)</option>
        </select></div>'
]); ?>
