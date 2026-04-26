<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'PDF to JPG', 'slug' => 'pdf-to-jpg', 'icon' => 'file-earmark-image', 'category' => 'pdf',
    'description' => 'Convert each page of your PDF to a high-quality JPG image.',
    'accept' => '.pdf',
    'options_html' => '<div class="tb-dash-card"><label class="form-label fw-semibold">Image Quality (DPI)</label>
        <select name="dpi" class="form-select tb-form-control">
            <option value="150">150 DPI (Standard)</option>
            <option value="300">300 DPI (High quality)</option>
            <option value="72">72 DPI (Web/small)</option>
        </select></div>'
]); ?>
