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

        $linkQuery = $db->prepare('SELECT * FROM links WHERE shortid = :shortid AND owner_email = :email');
        $linkQuery->execute(['shortid' => $shortid, 'email' => $email]);
        $link = $linkQuery->fetch(PDO::FETCH_ASSOC);

        if ($link) {
            $deleteQuery = $db->prepare('DELETE FROM links WHERE shortid = :shortid');
            $deleteQuery->execute(['shortid' => $shortid]);
            $success = 'Link deleted successfully! Redirecting to profile...';

            header('refresh:3;url=/profile');
        } else {
            $error = 'You do not have permission to delete this link or it does not exist.';
        }
    }
} catch (PDOException $e) {
    $error = 'An error occurred while deleting the link. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>delete - unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <?php require_once 'components/favicon.php'; ?>
</head>

<body>
    <?php require_once 'components/navbar.php'; ?>
    <section class="section">
        <div class="container is-max-tablet">
            <h1 class="title">Delete a link</h1>
            <?php if (isset($error)): ?>
                <div class="notification is-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php elseif (isset($success)): ?>
                <div class="notification is-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <a href="/profile" class="button">Back to profile</a>
        </div>
    </section>
</body>

</html>