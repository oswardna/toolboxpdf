<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Convert Format', 'slug' => 'img-convert', 'icon' => 'arrow-left-right', 'category' => 'img',
    'description' => 'Convert between image formats (PNG, JPG, WebP, AVIF, TIFF, etc.).',
    'accept' => 'image/*',
    'options_html' => '<div class="tb-dash-card"><label class="form-label fw-semibold">Target Format</label>
        <select name="format" class="form-select tb-form-control">
            <option value="jpg">JPG</option><option value="png">PNG</option>
            <option value="webp">WebP</option><option value="avif">AVIF</option>
            <option value="tiff">TIFF</option><option value="bmp">BMP</option>
        </select></div>'
]); ?>
