<?php
require_once 'db.php';
require_once 'pascco_shell.php';
require_once 'pascco_products.php';

$error = '';
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$account_type = trim($_POST['account_type'] ?? '');
$account_types = pascco_savings_product_names();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = $_POST['password'] ?? '';

    if ($first_name === '' || $last_name === '' || $phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($account_type, $account_types, true) || strlen($password) < 8) {
        $error = 'Please complete every field. Passwords must contain at least 8 characters.';
    } else {
        try {
            $pdo->beginTransaction();

            $user_stmt = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, 'member', 'active')");
            $user_stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
            $user_id = (int) $pdo->lastInsertId();

            $member_number = 'MEM-' . date('Y') . '-' . str_pad((string) $user_id, 4, '0', STR_PAD_LEFT);
            $member_stmt = $pdo->prepare("INSERT INTO members (user_id, member_number, first_name, last_name, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
            $member_stmt->execute([$user_id, $member_number, $first_name, $last_name, $email, $phone]);
            $member_id = (int) $pdo->lastInsertId();

            $account_number = 'ACC-' . str_pad((string) (10001233 + $member_id), 8, '0', STR_PAD_LEFT);
            $account_stmt = $pdo->prepare("INSERT INTO accounts (member_id, account_number, account_type, balance, status) VALUES (?, ?, ?, 0, 'active')");
            $account_stmt->execute([$member_id, $account_number, $account_type]);

            $pdo->commit();
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = (int) ($e->errorInfo[1] ?? 0) === 1062
                ? 'That email is already registered. Please use the login page.'
                : 'Registration failed. Please try again later.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php pascco_global_styles(); ?>
    <title>Register | PASCCO</title>
    <style>
        :root { --deep-blue: #071d49; --blue: #1456a0; --gold: #e7b84b; }
        * { box-sizing: border-box; }
        body { background: var(--deep-blue) url('paco2.png') center / cover fixed; color: white; font-family: Georgia, 'Times New Roman', serif; margin: 0; min-height: 100vh; }
        body::before { background: rgba(7,29,73,.8); content: ''; inset: 0; position: fixed; z-index: -1; }
        .site-header { align-items: center; background: linear-gradient(90deg, var(--deep-blue), var(--blue)); display: flex; gap: 24px; min-height: 84px; padding: 12px clamp(18px, 5vw, 70px); }
        .brand { align-items: center; color: white; display: flex; gap: 10px; margin-right: auto; text-decoration: none; }
        .brand img { height: 54px; width: 54px; }
        .brand strong { font-family: Impact, 'Arial Black', sans-serif; font-size: clamp(1.7rem, 4vw, 2.5rem); letter-spacing: .05em; }
        .site-nav { display: flex; gap: 18px; }
        .site-nav a, .header-action { color: white; font-size: .82rem; font-weight: bold; text-decoration: none; }
        .site-nav a:hover, .header-action:hover { color: var(--gold); }
        .header-action { border: 1px solid var(--gold); padding: 9px 12px; }
        .page { align-items: center; display: grid; gap: clamp(30px, 7vw, 100px); grid-template-columns: minmax(0, 1fr) minmax(340px, 520px); margin: 0 auto; max-width: 1120px; min-height: calc(100vh - 84px); padding: 48px 22px 70px; }
        .intro { text-align: center; }
        .intro img { max-width: 150px; width: 35%; }
        .intro h1 { font-size: clamp(2.3rem, 5vw, 4.2rem); line-height: 1.05; margin: 25px 0 0; text-shadow: 3px 4px 0 rgba(0,0,0,.35); }
        .intro p { color: #ffe7a1; letter-spacing: .16em; }
        .card { backdrop-filter: blur(14px); background: rgba(0,0,0,.42); border: 1px solid rgba(231,184,75,.9); border-radius: 22px; box-shadow: 0 18px 50px rgba(0,0,0,.3); padding: clamp(26px, 5vw, 44px); }
        .card h2 { margin: 0 0 22px; text-align: center; }
        .error { background: #fff4cf; border-left: 4px solid var(--gold); color: var(--deep-blue); margin-bottom: 18px; padding: 12px; }
        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: .9rem; font-weight: bold; margin-bottom: 7px; }
        .field input { background: rgba(255,255,255,.08); border: 0; border-bottom: 2px solid rgba(255,255,255,.75); color: white; font: inherit; outline: 0; padding: 11px 4px; width: 100%; }
        .field select { background: #071d49; border: 0; border-bottom: 2px solid rgba(255,255,255,.75); color: white; font: inherit; outline: 0; padding: 11px 4px; width: 100%; }
        .field input:focus { border-bottom-color: var(--gold); }
        button { background: linear-gradient(90deg, var(--blue), var(--deep-blue), var(--blue)); border: 1px solid var(--gold); border-radius: 30px; color: white; cursor: pointer; font: inherit; font-weight: bold; margin-top: 8px; padding: 14px; width: 100%; }
        .login-link { font-size: .9rem; text-align: center; }
        .login-link a { color: var(--gold); font-weight: bold; }
        @media (max-width: 760px) { .site-nav { display: none; } .page { grid-template-columns: 1fr; min-height: auto; } .card { max-width: 520px; width: 100%; justify-self: center; } .intro h1 { font-size: 2.6rem; } }
    </style>
</head>
<body>
    <?php pascco_public_header('Login', 'login.php'); ?>
    <main class="page">
        <section class="intro"><img src="LOGO.png" alt="Paco Savings and Credit Cooperative logo"><h1>JOIN PASCCO</h1><p>BUILDING FINANCIAL CONFIDENCE TOGETHER</p></section>
        <section class="card" aria-labelledby="register-title">
            <h2 id="register-title">Create Your Member Account</h2>
            <?php if ($error): ?><div class="error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
            <form method="POST" action="register.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="field"><label for="first-name">First Name</label><input id="first-name" type="text" name="first_name" value="<?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="given-name" required></div>
                <div class="field"><label for="last-name">Last Name</label><input id="last-name" type="text" name="last_name" value="<?php echo htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="family-name" required></div>
                <div class="field"><label for="email">Email Address</label><input id="email" type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="email" required></div>
                <div class="field"><label for="phone">Phone Number</label><input id="phone" type="tel" name="phone" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="tel" required></div>
                <div class="field"><label for="account-type">Savings Account</label><select id="account-type" name="account_type" required><option value="">Select a savings account</option><?php foreach ($account_types as $option): ?><option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $account_type === $option ? 'selected' : ''; ?>><?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
                <div class="field"><label for="password">Create Password</label><input id="password" type="password" name="password" minlength="8" autocomplete="new-password" required></div>
                <button type="submit">Create Account</button>
            </form>
            <p class="login-link">Already registered? <a href="login.php">Sign in</a></p>
        </section>
    </main>
    <?php pascco_global_footer(); ?>
</body>
</html>
