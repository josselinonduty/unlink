<?php
try {
    $message = '';
    $status = $_GET['status'] ?? '';
    $token = $_GET['token'] ?? '';

    switch ($status) {
        case 'error':
            $message = "Verification failed.";
            break;
        case 'already':
            $message = "Your email address is already verified. Please login.";
            break;
        case 'registered':
            $message = "Please verify your email address.";
            break;
        case 'verified':
            $message = "Your email address was successfully verified. Redirecting to login page...";
            break;
    }

    if ($token && !$status) {
        $db = new PDO('sqlite:../data/unlink.db');

        $stmt = $db->prepare("SELECT email, token FROM users WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $updateStmt = $db->prepare("UPDATE users SET token = NULL WHERE email = :email");
            $updateStmt->execute([':email' => $user['email']]);

            $message = "Your email address was successfully verified. Redirecting to login page...";
            $status = 'verified';

            header("refresh:3;url=/login");
        } else {
            $status = 'error';
            $message = "Verification failed.";
        }
    }
} catch (Exception $e) {
    $status = 'error';
    $message = "Verification failed: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>verify - unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <?php require_once 'components/favicon.php'; ?>
</head>

<body>
    <?php require_once 'components/navbar.php'; ?>

    <section class="section">
        <div class="container">
            <h1 class="title">Verify my email</h1>

            <?php if ($message): ?>
                <div class="notification <?= $status === 'error' ? 'is-danger' : 'is-success' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>