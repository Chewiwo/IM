<?php
if (session_status() === PHP_SESSION_NONE) {
    $is_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $is_https,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "paco_cooperative";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('PASCCO database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please try again later.');
}

// Generate CSRF token if one does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper function to verify CSRF token on POST requests
function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submitted_token = (string) ($_POST['csrf_token'] ?? '');
        $session_token = (string) ($_SESSION['csrf_token'] ?? '');
        if ($session_token === '' || $submitted_token === '' || !hash_equals($session_token, $submitted_token)) {
            die("Security Error: Invalid or missing CSRF token. Request blocked.");
        }
    }
}

$pdo->exec("CREATE TABLE IF NOT EXISTS membership_applications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    member_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    preferred_schedule_date DATE DEFAULT NULL,
    pmes_schedule VARCHAR(100) DEFAULT NULL,
    certificate_path VARCHAR(255) DEFAULT NULL,
    document_paths JSON DEFAULT NULL,
    status ENUM('applied','scheduled','certificate_uploaded','under_review','approved','rejected') NOT NULL DEFAULT 'applied',
    notes TEXT DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_membership_applications_status (status, created_at),
    CONSTRAINT fk_membership_applications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$pdo->exec("CREATE TABLE IF NOT EXISTS account_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    account_type VARCHAR(120) NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_by INT UNSIGNED DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    INDEX idx_account_requests_status (status, requested_at),
    CONSTRAINT fk_account_requests_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    CONSTRAINT fk_account_requests_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

function record_audit(PDO $pdo, ?int $user_id, string $action, string $description, string $status = 'success'): void
{
    try {
        $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id, action, description, ip_address, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$user_id, $action, substr($description, 0, 255), $_SERVER['REMOTE_ADDR'] ?? 'unknown', $status]);
    } catch (Throwable $exception) {
        // Audit failure must not turn a completed member transaction into a failed request.
    }
}
?>