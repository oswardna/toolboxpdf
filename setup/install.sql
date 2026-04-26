-- ToolBox Database Schema
-- Run via setup/install.php or manually import

-- ─── Users ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  email       VARCHAR(150) NOT NULL UNIQUE,
  password    VARCHAR(255) NOT NULL,
  role        ENUM('user','admin') DEFAULT 'user',
  avatar      VARCHAR(255) DEFAULT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Subscriptions ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS subscriptions (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NOT NULL,
  plan            ENUM('monthly','yearly') NOT NULL,
  status          ENUM('active','cancelled','expired') DEFAULT 'active',
  started_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at      DATETIME NOT NULL,
  payment_id      VARCHAR(255),
  gateway         ENUM('stripe','flutterwave') NOT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_status (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Tool Jobs (audit log + download gate) ─────────────────
CREATE TABLE IF NOT EXISTS tool_jobs (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED DEFAULT NULL,
  tool_slug     VARCHAR(60) NOT NULL,
  is_clientside TINYINT(1) DEFAULT 0,
  input_file    VARCHAR(255) DEFAULT NULL,
  output_file   VARCHAR(255) DEFAULT NULL,
  file_size     BIGINT UNSIGNED DEFAULT 0,
  status        ENUM('pending','processing','done','failed','expired') DEFAULT 'pending',
  error_msg     TEXT DEFAULT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at    TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_status (status),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Tools Registry (admin-toggleable) ─────────────────────
CREATE TABLE IF NOT EXISTS tools (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(60) NOT NULL UNIQUE,
  name        VARCHAR(100) NOT NULL,
  category    ENUM('pdf','img') NOT NULL,
  is_premium  TINYINT(1) DEFAULT 1,
  is_client   TINYINT(1) DEFAULT 0,
  is_active   TINYINT(1) DEFAULT 1,
  icon        VARCHAR(50) DEFAULT 'gear',
  description TEXT,
  sort_order  INT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Free Usage (rate-limiting) ────────────────────────────
CREATE TABLE IF NOT EXISTS free_usage (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  identifier  VARCHAR(64) NOT NULL,
  tool_slug   VARCHAR(60) NOT NULL,
  used_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_identifier_tool (identifier, tool_slug),
  INDEX idx_used_at (used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Password Reset Tokens ────────────────────────────────
CREATE TABLE IF NOT EXISTS password_resets (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  token       VARCHAR(100) NOT NULL,
  expires_at  DATETIME NOT NULL,
  used        TINYINT(1) DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── Settings ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ═══════════════════════════════════════════════════════════
-- SEED DATA — Tool Catalogue
-- ═══════════════════════════════════════════════════════════

INSERT INTO tools (slug, name, category, is_premium, is_client, icon, description, sort_order) VALUES
-- PDF Tools — Free (Client-side)
('pdf-split',       'Split PDF',          'pdf', 0, 1, 'scissors',          'Separate a PDF into multiple files by page ranges.',           1),
('pdf-merge',       'Merge PDF',          'pdf', 0, 1, 'union',             'Combine multiple PDFs into a single document.',                2),
('pdf-rotate',      'Rotate Pages',       'pdf', 0, 1, 'arrow-clockwise',   'Rotate individual pages or the entire PDF.',                   3),

-- PDF Tools — Premium (Server-side)
('pdf-compress',    'Compress PDF',       'pdf', 1, 0, 'file-earmark-zip',  'Reduce PDF file size while maintaining quality.',              4),
('pdf-to-jpg',      'PDF to JPG',         'pdf', 1, 0, 'file-earmark-image','Convert each PDF page to a JPG image.',                        5),
('jpg-to-pdf',      'JPG to PDF',         'pdf', 1, 0, 'file-earmark-pdf',  'Convert JPG images to a single PDF document.',                 6),
('word-to-pdf',     'Word to PDF',        'pdf', 1, 0, 'file-earmark-word', 'Convert Word documents (.docx, .doc) to PDF.',                 7),
('pdf-to-word',     'PDF to Word',        'pdf', 1, 0, 'file-earmark-word', 'Convert PDF files to editable Word documents.',                8),
('excel-to-pdf',    'Excel to PDF',       'pdf', 1, 0, 'file-earmark-excel','Convert Excel spreadsheets to PDF.',                           9),
('ppt-to-pdf',      'PowerPoint to PDF',  'pdf', 1, 0, 'file-earmark-ppt',  'Convert PowerPoint presentations to PDF.',                    10),
('html-to-pdf',     'HTML to PDF',        'pdf', 1, 0, 'filetype-html',     'Convert HTML pages or code to PDF documents.',                11),
('pdf-protect',     'Protect PDF',        'pdf', 1, 0, 'shield-lock',       'Add password protection to your PDF files.',                  12),
('pdf-unlock',      'Unlock PDF',         'pdf', 1, 0, 'unlock',            'Remove password protection from PDF files.',                  13),
('pdf-watermark',   'Watermark PDF',      'pdf', 1, 0, 'droplet-half',      'Add text or image watermarks to PDF pages.',                  14),
('pdf-ocr',         'OCR / Extract Text', 'pdf', 1, 0, 'textarea-t',        'Extract text from scanned PDFs using OCR.',                   15),
('pdf-repair',      'Repair PDF',         'pdf', 1, 0, 'wrench-adjustable', 'Fix corrupted or damaged PDF files.',                         16),
('pdf-page-numbers','Add Page Numbers',   'pdf', 1, 0, 'list-ol',           'Add page numbers to your PDF documents.',                     17),
('pdf-flatten',     'Flatten Forms',      'pdf', 1, 0, 'layers',            'Flatten PDF form fields into static content.',                18),

-- Image Tools — Free (Client-side)
('img-compress',    'Compress Image',     'img', 0, 1, 'file-earmark-zip',  'Reduce image file size without losing quality.',              19),
('img-resize',      'Resize Image',       'img', 0, 1, 'aspect-ratio',      'Resize images to specific dimensions.',                       20),
('img-crop',        'Crop Image',         'img', 0, 1, 'crop',              'Crop images to your desired area.',                           21),
('img-meme',        'Meme Generator',     'img', 0, 1, 'emoji-laughing',    'Create memes with custom text overlays.',                     22),
('img-flip',        'Flip / Mirror',      'img', 0, 1, 'symmetry-vertical', 'Flip or mirror images horizontally or vertically.',           23),
('img-grayscale',   'Grayscale',          'img', 0, 1, 'circle-half',       'Convert images to black and white.',                          24),

-- Image Tools — Premium (Server-side)
('img-convert',     'Convert Format',     'img', 1, 0, 'arrow-left-right',  'Convert between image formats (PNG, JPG, WebP, etc.).',       25),
('img-remove-bg',   'Remove Background',  'img', 1, 0, 'eraser',            'Automatically remove image backgrounds using AI.',            26),
('img-watermark',   'Watermark Image',    'img', 1, 0, 'droplet-half',      'Add text or image watermarks to your photos.',               27),
('img-enhance',     'Enhance / Sharpen',  'img', 1, 0, 'stars',             'Enhance image quality, sharpen, and adjust colors.',          28),
('img-to-pdf',      'Image to PDF',       'img', 1, 0, 'file-earmark-pdf',  'Convert images to a single PDF document.',                   29),
('img-upscale',     'Upscale (2×/4×)',    'img', 1, 0, 'arrows-fullscreen', 'Upscale images to 2× or 4× resolution.',                     30);
