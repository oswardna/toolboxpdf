<?php
/**
 * ToolBox — Header Include
 * 
 * Renders the HTML head and navigation.
 * Every page should require this file AFTER logic/auth checks.
 */
require_once __DIR__ . '/init.php';

// Global instances are already set by init.php
global $auth, $pdo;

// Page title — pages can set $pageTitle before including header
$pageTitle  = isset($pageTitle) ? $pageTitle . ' — ' . APP_NAME : APP_NAME . ' — ' . APP_TAGLINE;
$pageDesc   = $pageDesc ?? APP_TAGLINE;
$bodyClass  = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDesc) ?>">
    <meta name="theme-color" content="#e5322d">
    
    <title><?= e($pageTitle) ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>/images/logo.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- ToolBox CSS -->
    <link href="<?= BASE_URL ?>/assets/css/toolbox.css?v=1.1" rel="stylesheet">

    <script>
        window.TB_CONFIG = {
            maxSizeMB: <?= $auth->isPro() ? 500 : 50 ?>
        };
    </script>

    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body class="<?= e($bodyClass) ?>">

<?php require_once __DIR__ . '/nav.php'; ?>

<!-- Flash messages -->
<div class="container mt-3">
    <?= renderFlashes() ?>
</div>

<main>
