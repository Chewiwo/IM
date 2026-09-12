<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/pascco_shell.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'member') {
    header('Location: login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$message = '';
$error = '';
$member_name = trim($_POST['member_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$preferred_schedule_date = trim($_POST['preferred_schedule_date'] ?? '');
$notes = trim($_POST['notes'] ?? '');

$application_stmt = $pdo->prepare('SELECT * FROM membership_applications WHERE user_id = ? ORDER BY id DESC LIMIT 1');
$application_stmt->execute([$user_id]);
$current_application = $application_stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if ($member_name === '' || $email === '' || $phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide your full name, valid email, and contact number.';
    } else {
        try {
            $member_record_stmt = $pdo->prepare('SELECT member_number, first_name, last_name, email, phone FROM members WHERE user_id = ? LIMIT 1');
            $member_record_stmt->execute([$user_id]);
            $member_record = $member_record_stmt->fetch();

            $final_name = $member_name !== '' ? $member_name : (($member_record['first_name'] ?? '') . ' ' . ($member_record['last_name'] ?? ''));
            $final_email = $email !== '' ? $email : ($member_record['email'] ?? '');
            $final_phone = $phone !== '' ? $phone : ($member_record['phone'] ?? '');

            $stmt = $pdo->prepare('INSERT INTO membership_applications (user_id, member_name, email, phone, preferred_schedule_date, notes, status) VALUES (?, ?, ?, ?, ?, ?, "applied")');
            $stmt->execute([$user_id, $final_name, $final_email, $final_phone, $preferred_schedule_date !== '' ? $preferred_schedule_date : null, $notes]);

            record_audit($pdo, $user_id, 'membership_application', 'Submitted membership application for PMES scheduling', 'success');
            $message = 'Your application for PMES scheduling has been submitted. We will notify you once a schedule becomes available.';
            $member_name = '';
            $email = '';
            $phone = '';
            $preferred_schedule_date = '';
            $notes = '';
            $application_stmt->execute([$user_id]);
            $current_application = $application_stmt->fetch();
        } catch (Throwable $exception) {
            $error = 'Unable to save your membership application. Please try again later.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php pascco_global_styles(); ?>
    <title>Become a Member | PASCCO</title>
    <style>
        body { background: #f4f7fb; color: #182a45; font-family: Georgia, 'Times New Roman', serif; margin: 0; }
        .page { margin: 0 auto; max-width: 920px; padding: 42px 22px 70px; }
        .layout { display: grid; gap: 22px; grid-template-columns: minmax(0, 1.25fr) minmax(260px, .75fr); }
        .card { background: white; border-top: 5px solid #1456a0; box-shadow: 0 6px 20px rgba(7,29,73,.08); padding: clamp(22px, 5vw, 38px); }
        .guide { background: #071d49; color: white; padding: 26px; }
        .guide h2 { color: white; }
        .guide ol { line-height: 1.7; margin: 0; padding-left: 20px; }
        .eyebrow { color: #1456a0; font-size: .8rem; font-weight: bold; letter-spacing: .16em; text-transform: uppercase; }
        form { display: grid; gap: 14px; }
        label { color: #071d49; display: block; font-weight: bold; margin-bottom: 7px; }
        input, textarea { border: 1px solid #b8c8d9; border-radius: 4px; font: inherit; font-size: 16px; padding: 12px; width: 100%; }
        textarea { min-height: 110px; resize: vertical; }
        button { background: #1456a0; border: 0; color: white; cursor: pointer; font: inherit; font-weight: bold; padding: 14px; width: 100%; }
        .status-box { background: #e6edf8; border-left: 4px solid #e7b84b; margin-top: 12px; padding: 14px; }
        .msg { margin-bottom: 18px; padding: 12px; }
        .msg.success { background: #fff4cf; color: #071d49; }
        .msg.error { background: #f8d7da; color: #721c24; }
        @media (max-width: 720px) {
            .page { padding: 28px 14px 52px; }
            .layout { grid-template-columns: 1fr; }
            .card, .guide { padding: 20px 16px; }
        }
    </style>
</head>
<body>
<?php pascco_member_header('Become a Member'); ?>
<main class="page">
    <div class="eyebrow">Membership</div>
    <h1>Become a Member</h1>
    <div class="layout">
        <section class="card">
            <?php if ($message): ?><div class="msg success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="msg error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>

            <form method="POST" action="membership_apply.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                <div>
                    <label for="member-name">Full Name</label>
                    <input id="member-name" type="text" name="member_name" value="<?php echo htmlspecialchars($member_name, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Your full name" required>
                </div>

                <div>
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" placeholder="you@example.com" required>
                </div>

                <div>
                    <label for="phone">Phone Number</label>
                    <input id="phone" type="tel" name="phone" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" placeholder="09XXXXXXXXX" required>
                </div>

                <div>
                    <label for="preferred-schedule-date">Preferred Saturday Schedule</label>
                    <input id="preferred-schedule-date" type="date" name="preferred_schedule_date" value="<?php echo htmlspecialchars($preferred_schedule_date, ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div>
                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes" placeholder="Optional remarks or questions"><?php echo htmlspecialchars($notes, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <button type="submit">Apply for Scheduling</button>
            </form>

            <?php if ($current_application): ?>
                <div class="status-box">
                    <strong>Current status:</strong> <?php echo strtoupper(htmlspecialchars($current_application['status'], ENT_QUOTES, 'UTF-8')); ?><br>
                    <?php if ($current_application['preferred_schedule_date']): ?>
                        <strong>Preferred Saturday:</strong> <?php echo htmlspecialchars($current_application['preferred_schedule_date'], ENT_QUOTES, 'UTF-8'); ?><br>
                    <?php endif; ?>
                    <?php if ($current_application['pmes_schedule']): ?>
                        <strong>PMES schedule:</strong> <?php echo htmlspecialchars($current_application['pmes_schedule'], ENT_QUOTES, 'UTF-8'); ?><br>
                    <?php endif; ?>
                    <?php if ($current_application['created_at']): ?>
                        <strong>Submitted:</strong> <?php echo htmlspecialchars($current_application['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                    <?php endif; ?>
                </div>
                <div style="margin-top:16px;">
                    <a href="membership_schedule.php" style="display:inline-block;background:#1456a0;color:white;text-decoration:none;padding:12px 18px;border-radius:4px;font-weight:bold;">Choose PMES Schedule</a>
                </div>
            <?php endif; ?>
        </section>

        <aside class="guide">
            <h2>Membership Process</h2>
            <ol>
                <li>Submit your membership application.</li>
                <li>Receive notification of the available PMES schedule.</li>
                <li>Attend the scheduled PMES session and get your certificate.</li>
                <li>Upload your certificate and required supporting documents.</li>
                <li>Wait for admin approval to complete registration.</li>
            </ol>
        </aside>
    </div>
</main>
<?php pascco_global_footer(); ?>
</body>
</html>
