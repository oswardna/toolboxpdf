<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Protect PDF', 'slug' => 'pdf-protect', 'icon' => 'shield-lock', 'category' => 'pdf',
    'description' => 'Add password protection to your PDF files using qpdf.',
    'accept' => '.pdf',
    'options_html' => '<div class="tb-dash-card"><div class="mb-3">
        <label class="form-label fw-semibold">User Password <small class="text-muted">(to open)</small></label>
        <input type="text" name="user_password" class="form-control tb-form-control" placeholder="Enter password" required></div>
        <div><label class="form-label fw-semibold">Owner Password <small class="text-muted">(to edit)</small></label>
        <input type="text" name="owner_password" class="form-control tb-form-control" placeholder="Enter password"></div></div>'
]); ?>
