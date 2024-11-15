<?php
session_start();
try {
    if (!isset($_SESSION['email'])) {
        header('Location: /login');
        exit();
    }

    $db = new PDO('sqlite:../data/unlink.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = $_SESSION['email'];

    $userQuery = $db->prepare('SELECT email, created_at FROM users WHERE email = :email');
    $userQuery->execute(['email' => $email]);
    $user = $userQuery->fetch(PDO::FETCH_ASSOC);

    $linksQuery = $db->prepare('SELECT shortid, source_url, created_at, deleting_at, views FROM links WHERE owner_email = :email');
    $linksQuery->execute(['email' => $email]);
    $links = $linksQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "An error occurred while fetching your profile data.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>profile - unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <?php require_once 'components/favicon.php'; ?>

    <script>
        function copyToClipboard(shortid) {
            const url = `${window.location.origin}/v/${shortid}`;
            navigator.clipboard.writeText(url).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>
</head>

<body>
    <?php require_once 'components/navbar.php'; ?>

    <section class="section">
        <div class="container is-max-tablet">
            <h1 class="title">Profile</h1>

            <?php if (isset($error)): ?>
                <div class="notification is-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php else: ?>
                <div class="box">
                    <div class="level">
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>Member since:</strong> <?= htmlspecialchars((new DateTime($user['created_at']))->format('F j, Y')) ?></p>
                    </div>

                    <div class="level level-left">
                        <a class="button is-danger" href="/disappear">Delete my account</a>
                        <a class="button is-primary is-outlined" href="/forgot">Change my password</a>
                    </div>
                </div>

                <h2 class="subtitle">Your Links</h2>


                <?php if (count($links) > 0): ?>
                    <a class="button is-primary" href="/create">Create a new link</a>

                    <div class="table-container">
                        <table class="table is-striped is-fullwidth is-vcentered">
                            <thead>
                                <tr>
                                    <th class="has-text-centered">Created at</th>
                                    <th class="has-text-centered">Expires at</th>
                                    <th class="has-text-centered">Source</th>
                                    <th class="has-text-centered">Handle</th>
                                    <th class="has-text-centered">Views</th>

                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($links as $link): ?>
                                    <tr>
                                        <td class="has-text-centered"><?= htmlspecialchars((new DateTime($link['created_at']))->format('Y-m-d')) ?></td>
                                        <td class="has-text-centered"><?= $link['deleting_at'] ? htmlspecialchars((new DateTime($link['deleting_at']))->format('Y-m-d')) : '-' ?></td>
                                        <td class="has-text-centered">
                                            <a class="button is-primary is-outlined" href="<?= htmlspecialchars($link['source_url']) ?>" target="_blank">Open</a>
                                        </td>
                                        <td class="has-text-centered">
                                            <a class="button is-primary is-outlined" href="https://unlink.fr/v/<?= htmlspecialchars($link['shortid']) ?>" target="_blank">Open</a>
                                            <button class="button is-primary" onclick="copyToClipboard('<?= htmlspecialchars($link['shortid']) ?>')">
                                                Copy
                                            </button>
                                        </td>
                                        <td class="has-text-centered"><?= htmlspecialchars($link['views']) ?></td>

                                        <td class="has-text-centered buttons">
                                            <a href="/delete?s=<?= htmlspecialchars($link['shortid']) ?>" class="button is-danger">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <a class="button is-primary" href="/create">Shorten my first link</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>