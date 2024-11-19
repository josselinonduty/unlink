<?php
session_start();

try {
    if (!isset($_SESSION['email'])) {
        header('Location: /login');
        exit();
    }

    $email = $_SESSION['email'];
    $shortid = $_GET['s'] ?? '';

    if (empty($shortid)) {
        $error = 'No short link ID provided.';
    } else {
        $db = new PDO('sqlite:../data/unlink.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $linkQuery = $db->prepare('SELECT shortid, display_name, source_url FROM links WHERE shortid = :shortid AND owner_email = :email');
        $linkQuery->execute(['shortid' => $shortid, 'email' => $email]);
        $link = $linkQuery->fetch(PDO::FETCH_ASSOC);

        if ($link) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $display_name = $_POST['display_name'] ?? '';

                if (!empty($display_name)) {
                    $updateQuery = $db->prepare('UPDATE links SET display_name = :display_name WHERE shortid = :shortid');
                    $updateQuery->execute(['display_name' => $display_name, 'shortid' => $shortid]);
                    $success = 'Link updated successfully! Redirecting to profile...';

                    header('refresh:3;url=/profile');
                } else {
                    $error = 'Display name cannot be empty.';
                }
            }
        } else {
            $error = 'You do not have permission to edit this link or it does not exist.';
        }
    }
} catch (PDOException $e) {
    $error = 'An error occurred while updating the link. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>edit - unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <meta name="robots" content="noindex, nofollow">

    <meta name="description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:title" content="edit - unlink." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://unlink.fr/edit" />
    <meta property="og:image" content="https://unlink.fr/public/icon/og-512.png" />
    <meta name="theme-color" content="#00056b">

    <?php require_once 'components/favicon.php'; ?>
</head>

<body>
    <?php require_once 'components/navbar.php'; ?>
    <section class="section">
        <div class="container is-max-tablet">
            <h1 class="title">Edit Link</h1>
            <?php if (isset($error)): ?>
                <div class="notification is-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif (isset($success)): ?>
                <div class="notification is-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($link)): ?>
                <form method="post">
                    <div class="field">
                        <label class="label">URL</label>
                        <div class="control">
                            <input class="input" type="text" value="<?= htmlspecialchars($link['source_url']) ?>" readonly disabled>
                        </div>
                    </div>
                    <div class="field">
                        <label class="label">Name</label>
                        <div class="control">
                            <input class="input" type="text" name="display_name" value="<?= htmlspecialchars($link['display_name']) ?>" required <?= isset($success) ? 'disabled' : '' ?>>
                        </div>
                    </div>
                    <div class="field">
                        <div class="control">
                            <div class="level level-right">
                                <a href="/profile" class="button">Back to profile</a>
                                <button class="button is-primary" type="submit">Update</button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>