<?php
session_start();
require_once 'util/shortid.php';

$error = '';
$success = '';
try {
    if (!isset($_SESSION['email'])) {
        header('Location: /login');
        exit();
    }

    $email = $_SESSION['email'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $longUrl = trim($_POST['long_url']);

        if (empty($longUrl) || !filter_var($longUrl, FILTER_VALIDATE_URL)) {
            $error = 'Please enter a valid URL.';
        } else {
            $db = new PDO('sqlite:../data/unlink.db');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $roleQuery = $db->prepare('SELECT role FROM users WHERE email = :email');
            $roleQuery->execute(['email' => $email]);
            $role = $roleQuery->fetchColumn();

            if ($role === 'admin') {
                goto createLink;
            }

            $linkCountQuery = $db->prepare('SELECT COUNT(*) FROM links WHERE owner_email = :email');
            $linkCountQuery->execute(['email' => $email]);
            $linkCount = $linkCountQuery->fetchColumn();

            if ($linkCount >= 10) {
                $error = 'You have reached the maximum of 10 links.';
            } else {
                createLink:
                $isUnique = false;
                do {
                    $shortid = generateShortId(6);

                    $shortidQuery = $db->prepare('SELECT COUNT(*) FROM links WHERE shortid = :shortid');
                    $shortidQuery->execute(['shortid' => $shortid]);
                    $isUnique = $shortidQuery->fetchColumn() == 0;
                } while (!$isUnique);
                // TODO: Implement link creation

                $insertQuery = $db->prepare('INSERT INTO links (shortid, source_url, owner_email, deleting_at) VALUES (:shortid, :source_url, :owner_email, NULL)');
                $insertQuery->execute([
                    'shortid' => $shortid,
                    'source_url' => $longUrl,
                    'owner_email' => $email
                ]);

                $success = 'Link created successfully!';

                header('Location: /profile');
                exit();
            }
        }
    }
} catch (PDOException $e) {
    $error = 'An error occurred while creating the link. Please try again.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>create - unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <meta name="description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:title" content="create - unlink." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://unlink.fr/create" />
    <meta property="og:image" content="https://unlink.fr/public/icon/og-512.png" />
    <meta name="theme-color" content="#00056b">

    <?php require_once 'components/favicon.php'; ?>
</head>

<body>
    <?php require_once 'components/navbar.php'; ?>

    <section class="section">
        <div class="container is-max-tablet">
            <h1 class="title">Shorten a link</h1>

            <?php if ($error): ?>
                <div class="notification is-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif ($success): ?>
                <div class="notification is-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form action="/create" method="post">
                <div class="field">
                    <label class="label" for="long_url">URL</label>
                    <div class="control">
                        <input type="url" id="long_url" name="long_url" class="input" placeholder="https://example.com" required>
                    </div>
                </div>

                <div class="control">
                    <button type="submit" class="button is-primary">Shorten my link</button>
                </div>
            </form>
        </div>
    </section>
</body>

</html>