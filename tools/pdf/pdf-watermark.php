<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Watermark PDF', 'slug' => 'pdf-watermark', 'icon' => 'droplet-half', 'category' => 'pdf',
    'description' => 'Add text or image watermarks to every page of your PDF.',
    'accept' => '.pdf',
    'options_html' => '<div class="tb-dash-card"><div class="mb-3">
        <label class="form-label fw-semibold">Watermark Text</label>
        <input type="text" name="watermark_text" class="form-control tb-form-control" placeholder="CONFIDENTIAL" value="CONFIDENTIAL"></div>
        <div class="row g-3"><div class="col-6"><label class="form-label small">Opacity</label>
        <input type="range" name="opacity" class="form-range" min="10" max="90" value="30"></div>
        <div class="col-6"><label class="form-label small">Font Size</label>
        <input type="number" name="font_size" class="form-control tb-form-control form-control-sm" value="48"></div></div></div>'
]); ?>
