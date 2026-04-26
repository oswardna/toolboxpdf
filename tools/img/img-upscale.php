<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Upscale (2×/4×)', 'slug' => 'img-upscale', 'icon' => 'arrows-fullscreen', 'category' => 'img',
    'description' => 'Upscale images to 2× or 4× resolution using AI enhancement.',
    'accept' => 'image/*',
    'options_html' => '<div class="tb-dash-card"><label class="form-label fw-semibold">Scale Factor</label>
        <select name="scale" class="form-select tb-form-control">
            <option value="2">2× Resolution</option>
            <option value="4">4× Resolution</option>
        </select></div>'
]); ?>
