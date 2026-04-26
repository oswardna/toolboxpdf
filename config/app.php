<?php
/**
 * ToolBox — Application Configuration
 */

// Prevent direct access
if (!defined('TOOLBOX_LOADED')) {
    define('TOOLBOX_LOADED', true);
}

// ─── Brand ───────────────────────────────────────────────
define('APP_NAME',    'ToolBox');
define('APP_TAGLINE', 'All your PDF & image tools in one place');
define('APP_VERSION', '1.0.0');

// ─── URLs ────────────────────────────────────────────────
define('BASE_URL', 'https://palchat.net');

// ─── File handling ───────────────────────────────────────
define('UPLOAD_DIR',   __DIR__ . '/../uploads/');
define('MAX_FILE_MB',  50);
define('FILE_EXPIRY_MINUTES', 30);

// ─── Free tier limits ────────────────────────────────────
define('FREE_DAILY_LIMIT', 3);   // per tool per day

// ─── Stripe ──────────────────────────────────────────────
define('STRIPE_SECRET_KEY',     getenv('STRIPE_SECRET_KEY')     ?: '');
define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY') ?: '');
define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET') ?: '');
define('STRIPE_PRICE_MONTHLY',  getenv('STRIPE_PRICE_MONTHLY')  ?: 'price_monthly_1usd');
define('STRIPE_PRICE_YEARLY',   getenv('STRIPE_PRICE_YEARLY')   ?: 'price_yearly_9usd');

// ─── Flutterwave ─────────────────────────────────────────
define('FLW_PUBLIC_KEY',   getenv('FLW_PUBLIC_KEY')   ?: '');
define('FLW_SECRET_KEY',   getenv('FLW_SECRET_KEY')   ?: '');
define('FLW_WEBHOOK_HASH', getenv('FLW_WEBHOOK_HASH') ?: '');

// ─── Mail (PHPMailer) ────────────────────────────────────
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USER',     getenv('MAIL_USER') ?: '');
define('MAIL_PASS',     getenv('MAIL_PASS') ?: '');
define('MAIL_FROM',     getenv('MAIL_FROM') ?: 'noreply@toolbox.dev');
define('MAIL_FROM_NAME', APP_NAME);

// ─── Security ────────────────────────────────────────────
define('CSRF_TOKEN_NAME', '_csrf_token');

// ─── Server-side binary paths (adjust for your OS) ──────
define('BIN_GHOSTSCRIPT',  'gs');
define('BIN_IMAGEMAGICK',  'convert');
define('BIN_LIBREOFFICE',  'libreoffice');
define('BIN_QPDF',         'qpdf');
define('BIN_TESSERACT',    'tesseract');
define('BIN_MUPDF',        'mutool');
