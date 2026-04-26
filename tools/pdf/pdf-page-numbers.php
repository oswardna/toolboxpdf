<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'Add Page Numbers', 'slug' => 'pdf-page-numbers', 'icon' => 'list-ol', 'category' => 'pdf',
    'description' => 'Add page numbers to your PDF documents with custom positioning.',
    'accept' => '.pdf',
    'options_html' => '<div class="tb-dash-card"><div class="mb-3">
        <label class="form-label fw-semibold">Position</label>
        <select name="position" class="form-select tb-form-control">
            <option value="bottom-center">Bottom Center</option>
            <option value="bottom-right">Bottom Right</option>
            <option value="bottom-left">Bottom Left</option>
            <option value="top-center">Top Center</option>
        </select></div>
        <div><label class="form-label fw-semibold">Starting Number</label>
        <input type="number" name="start_num" class="form-control tb-form-control" value="1"></div></div>'
]); ?>
