<?php
require_once 'util/password.php';
$error = '';
$message = '';

try {
    $token = $_GET['token'] ?? '';

    if (!$token) {
        throw new Exception("Invalid or expired token.");
    }

    $db = new PDO('sqlite:../data/unlink.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT email, reset_token_expires FROM users WHERE reset_token = :token");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Invalid or expired token.");
    }

    if (strtotime($user['reset_token_expires']) < time()) {
        $updateStmt = $db->prepare("UPDATE users SET reset_token = NULL, reset_token_expires = NULL WHERE reset_token = :token");
        $updateStmt->execute([':token' => $token]);

        header("Location: /forgot");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';

        $password_errors = isPasswordValid($password);
        if ($password_errors) {
            throw new Exception('Password does not meet the following requirements:');
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 14]);

        $updateStmt = $db->prepare("UPDATE users SET password_hash = :password_hash, reset_token = NULL, reset_token_expires = NULL WHERE email = :email");
        $updateStmt->execute([
            ':password_hash' => $passwordHash,
            ':email' => $user['email'],
        ]);

        header("Location: /login");
        exit;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>reset - unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <meta name="robots" content="noindex, nofollow">

    <meta name="description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:title" content="reset - unlink." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://unlink.fr/reset" />
    <meta property="og:image" content="https://unlink.fr/public/icon/og-512.png" />
    <meta name="theme-color" content="#00056b">

    <?php require_once 'components/favicon.php'; ?>
</head>

<body>
    <?php require_once 'components/navbar.php'; ?>

    <section class="section">
        <div class="container is-max-tablet">
            <h1 class="title">Reset my password</h1>

            <?php if ($error): ?>
                <div class="notification is-danger">
                    <?= htmlspecialchars($error) ?>
                    <?php if ($password_errors): ?>
                        <ul class="password-errors">
                            <?php foreach ($password_errors as $password_error): ?>
                                <li><?= htmlspecialchars($password_error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($user): ?>
                <form method="POST" action="reset?token=<?= htmlspecialchars($token) ?>">
                    <div class="field">
                        <label class="label">New Password</label>
                        <div class="control">
                            <input class="input" type="password" name="password" required autocomplete="new-password">
                        </div>
                    </div>

                    <div class="field">
                        <div class="control buttons">
                            <button class="button is-primary" type="submit">Reset Password</button>
                            <a href="/login" class="button is-primary is-outlined">Login</a>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="notification is-danger">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>