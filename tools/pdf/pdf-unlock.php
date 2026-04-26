<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Unlock PDF', 'slug' => 'pdf-unlock', 'icon' => 'unlock', 'category' => 'pdf',
    'description' => 'Remove password protection from PDF files.',
    'accept' => '.pdf',
    'options_html' => '<div class="tb-dash-card"><label class="form-label fw-semibold">Current Password</label>
        <input type="text" name="password" class="form-control tb-form-control" placeholder="Enter the PDF password" required></div>'
]); ?>
