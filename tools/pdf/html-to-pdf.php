<?php require_once __DIR__ . '/../premium-template.php'; renderPremiumTool([
    'title' => 'HTML to PDF', 'slug' => 'html-to-pdf', 'icon' => 'filetype-html', 'category' => 'pdf',
    'description' => 'Convert HTML pages or code to PDF documents using mPDF.',
    'accept' => '.html,.htm',
    'options_html' => '<div class="tb-dash-card"><label class="form-label fw-semibold">Or paste HTML code</label>
        <textarea name="html_code" class="form-control tb-form-control" rows="5" placeholder="<h1>Hello World</h1>"></textarea></div>'
]); ?>
