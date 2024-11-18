<?php
$error = '';

require_once 'util/password.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required.");
        }

        $email_errors = isEmailValid($email);
        if (!$email_errors) {
            throw new Exception('Invalid email address.');
        }

        $db = new PDO('sqlite:../data/unlink.db');
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $userCount = $stmt->fetchColumn();

        if ($userCount > 0) {
            throw new Exception('Email address is already in use.');
        }

        $password_errors = isPasswordValid($password);
        if ($password_errors) {
            throw new Exception('Password does not meet the following requirements:');
        }

        $token = bin2hex(random_bytes(16));
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 14]);

        $stmt = $db->query("SELECT COUNT(*) FROM users");
        $userCount = $stmt->fetchColumn();
        $role = ($userCount == 0) ? 'admin' : 'user';

        $stmt = $db->prepare("INSERT INTO users (email, token, password_hash, role) VALUES (:email, :token, :password_hash, :role)");
        $stmt->execute([
            ':email' => $email,
            ':token' => $token,
            ':password_hash' => $passwordHash,
            ':role' => $role
        ]);

        $verifyLink = "https://unlink.fr/verify?token=$token";
        $subject = "Verify Your Email";
        $message = "Please click the following link to verify your email: $verifyLink";
        $headers = [
            'From' => 'no-reply@unlink.fr <unlinku@cluster029.hosting.ovh.net>',
        ];

        if (!mail($email, $subject, $message, $headers)) {
            throw new Exception("Failed to send verification email.");
        }

        header("Location: /verify?status=registered");
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
    <title>register - unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <meta name="description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:title" content="register - unlink." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://unlink.fr/register" />
    <meta property="og:image" content="https://unlink.fr/public/icon/og-512.png" />
    <meta name="theme-color" content="#00056b">

    <?php require_once 'components/favicon.php'; ?>

    <style>
        ul.password-errors {
            list-style-type: square;
            margin-left: 1.5em;
        }
    </style>
</head>

<body>
    <?php require_once 'components/navbar.php'; ?>

    <section class="section">
        <div class="container is-max-tablet">
            <h1 class="title">Register</h1>

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

            <form method="POST" action="register">
                <div class="field">
                    <label class="label">Email</label>
                    <div class="control">
                        <input class="input" type="email" name="email" required autocomplete="email">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Password</label>
                    <div class="control">
                        <input class="input" type="password" name="password" required autocomplete="new-password">
                    </div>
                </div>

                <div class="field level level-right">
                    <div class="control buttons">
                        <a href="/login" class="button is-primary is-outlined">Login</a>
                        <button class="button is-primary" type="submit">Register</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</body>

</html>