<?php
$error = '';
$message = '';

require_once 'util/password.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';

        if (empty($email)) {
            throw new Exception("Email is required.");
        }

        $email_errors = isEmailValid($email);
        if (!$email_errors) {
            throw new Exception('Invalid email address.');
        }

        $db = new PDO('sqlite:../data/unlink.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $resetToken = bin2hex(random_bytes(16));
            $resetTokenExpires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $updateStmt = $db->prepare("UPDATE users SET reset_token = :reset_token, reset_token_expires = :reset_token_expires WHERE email = :email");
            $updateStmt->execute([
                ':reset_token' => $resetToken,
                ':reset_token_expires' => $resetTokenExpires,
                ':email' => $email,
            ]);

            $resetLink = "https://unlink.fr/reset?token=$resetToken";
            $subject = "Reset your password";
            $message = "Please click the following link to reset your password: $resetLink\nThis link will expire in 15 minutes.";
            $headers = [];

            mail($email, $subject, $message, $headers);
        }

        $message = "If an account with that email exists, a password reset link has been sent.";
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
    <title>forgot - unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <meta name="robots" content="noindex, nofollow">

    <meta name="description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:title" content="forgot - unlink." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://unlink.fr/forgot" />
    <meta property="og:image" content="https://unlink.fr/public/icon/og-512.png" />
    <meta name="theme-color" content="#00056b">

    <?php require_once 'components/favicon.php'; ?>
</head>

<body>
    <?php require_once 'components/navbar.php'; ?>

    <section class="section">
        <div class="container is-max-tablet">
            <h1 class="title">Change my password</h1>

            <?php if ($error): ?>
                <div class="notification is-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="notification is-success">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="forgot">
                <div class="field">
                    <label class="label">Email</label>
                    <div class="control">
                        <input class="input" type="email" name="email" required autocomplete="email">
                    </div>
                </div>

                <div class="field level level-right">
                    <div class="control buttons">
                        <a href="/login" class="button is-primary is-outlined">Login</a>
                        <button class="button is-primary" type="submit">Send me a link</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</body>

</html>