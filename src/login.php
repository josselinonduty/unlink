<?php

$error = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required.");
        }

        $db = new PDO('sqlite:../data/unlink.db');
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE email = :email AND token IS NULL");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['email'] = $email;

            header("Location: /profile");
            exit;
        } else {
            throw new Exception("Invalid email or password.");
        }
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
    <title>login - unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <meta name="description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:title" content="login - unlink." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://unlink.fr/login" />
    <meta property="og:image" content="https://unlink.fr/public/icon/og-512.png" />
    <meta name="theme-color" content="#00056b">

    <?php require_once 'components/favicon.php'; ?>
</head>

<body>
    <?php require_once 'components/navbar.php'; ?>

    <section class="section">
        <div class="container is-max-tablet">
            <h1 class="title">Login</h1>

            <?php if ($error): ?>
                <div class="notification is-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <div class="field">
                    <label class="label">Email</label>
                    <div class="control">
                        <input class="input" type="email" name="email" required autocomplete="email">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Password</label>
                    <div class="control">
                        <input class="input" type="password" name="password" required autocomplete="current-password">
                    </div>
                </div>

                <div class="field level level-right">
                    <div class="control buttons">
                        <a href="/register" class="button is-primary is-outlined">Register</a>
                        <a href="/forgot" class="button is-primary is-outlined">Forgot my password</a>
                        <button class="button is-primary" type="submit">Login</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</body>

</html>