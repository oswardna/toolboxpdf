<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Watermark Image', 'slug' => 'img-watermark', 'icon' => 'droplet-half', 'category' => 'img',
    'description' => 'Add text or image watermarks to your photos.',
    'accept' => 'image/*',
    'options_html' => '<div class="tb-dash-card"><div class="mb-3">
        <label class="form-label fw-semibold">Watermark Text</label>
        <input type="text" name="watermark_text" class="form-control tb-form-control" placeholder="© My Brand" value="© My Brand"></div>
        <div class="row g-3"><div class="col-6"><label class="form-label small">Opacity</label>
        <input type="range" name="opacity" class="form-range" min="10" max="90" value="50"></div>
        <div class="col-6"><label class="form-label small">Position</label>
        <select name="position" class="form-select tb-form-control form-select-sm">
            <option value="center">Center</option><option value="bottom-right">Bottom Right</option>
            <option value="bottom-left">Bottom Left</option><option value="top-right">Top Right</option>
        </select></div></div></div>'
]); ?>
