<?php
/**
 * ToolBox — One-Time Setup Script
 * 
 * Run once to create the database and seed tool data.
 * URL: http://localhost/myutility/toolbox/setup/install.php
 */

// Basic security — remove or protect this file after setup
$installLock = __DIR__ . '/.installed';

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToolBox — Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f0f1a; color: #e0e0e0; min-height: 100vh; display: flex; align-items: center; }
        .setup-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; }
    </style>
</head>
<body>
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-6">
<div class="setup-card p-5 my-5">
    <h2 class="mb-4 text-center">🧰 ToolBox Setup</h2>

<?php
if (file_exists($installLock)) {
    echo '<div class="alert alert-warning">Installation has already been completed. Delete <code>setup/.installed</code> to run again.</div>';
    echo '</div></div></div></div></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors  = [];
    $success = [];

    try {
        // Connect without DB name first
        $dbHost = 'localhost';
        $dbUser = 'root';
        $dbPass = '';
        $dbName = 'toolbox';

        $pdo = new PDO("mysql:host={$dbHost};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");
        $success[] = "Database <code>{$dbName}</code> created.";

        // Run SQL file
        $sql = file_get_contents(__DIR__ . '/install.sql');
        
        // Split on semicolons but not inside strings
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $stmt) {
            if (!empty($stmt) && $stmt !== '--') {
                $pdo->exec($stmt);
            }
        }
        $success[] = "Tables created and seed data inserted.";

        // Create default admin user
        $adminEmail = trim($_POST['admin_email'] ?? 'admin@toolbox.dev');
        $adminPass  = trim($_POST['admin_pass'] ?? 'admin123');
        $adminName  = trim($_POST['admin_name'] ?? 'Admin');

        if ($adminEmail && $adminPass) {
            $hash = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')
                                   ON DUPLICATE KEY UPDATE role='admin'");
            $stmt->execute([$adminName, $adminEmail, $hash]);
            $success[] = "Admin user created: <code>{$adminEmail}</code>";
        }

        // Create uploads directory
        $uploadsDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0750, true);
            $success[] = "Uploads directory created.";
        }

        // Create lock file
        file_put_contents($installLock, date('Y-m-d H:i:s'));
        $success[] = "Installation complete! 🎉";

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }

    // Display results
    foreach ($success as $msg) {
        echo "<div class='alert alert-success py-2'><small>✅ {$msg}</small></div>";
    }
    foreach ($errors as $msg) {
        echo "<div class='alert alert-danger py-2'><small>❌ {$msg}</small></div>";
    }

    if (empty($errors)) {
        echo '<a href="' . '../index.php' . '" class="btn btn-primary w-100 mt-3">Go to ToolBox →</a>';
    }

} else {
?>
    <p class="text-muted mb-4">This will create the database, tables, and an admin account.</p>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Admin Name</label>
            <input type="text" name="admin_name" class="form-control bg-dark text-white border-secondary" value="Admin" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Admin Email</label>
            <input type="email" name="admin_email" class="form-control bg-dark text-white border-secondary" value="admin@toolbox.dev" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Admin Password</label>
            <input type="password" name="admin_pass" class="form-control bg-dark text-white border-secondary" value="admin123" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg">Install ToolBox</button>
    </form>
<?php } ?>

</div>
</div>
</div>
</div>
</body>
</html>
