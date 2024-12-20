<?php
session_start();

$asUser = null;
$users = null;
try {
    if (!isset($_SESSION['email'])) {
        header('Location: /login');
        exit();
    }

    $db = new PDO('sqlite:../data/unlink.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $email = $_SESSION['email'];

    $userQuery = $db->prepare('SELECT email, created_at, role FROM users WHERE email = :email');
    $userQuery->execute(['email' => $email]);
    $user = $userQuery->fetch(PDO::FETCH_ASSOC);
    $asUser = $user;

    if ($user['role'] === 'admin' && isset($_GET['as']) && !empty($_GET['as'])) {
        $email = $_GET['as'];
        $userQuery->execute(['email' => $email]);
        $asUser = $userQuery->fetch(PDO::FETCH_ASSOC);

        if (!$asUser) {
            $error = "The user you are trying to view does not exist.";
        }
    }

    if ($user['role'] === 'admin') {
        $usersQuery = $db->prepare('SELECT email FROM users WHERE email != :email');
        $usersQuery->execute(['email' => $email]);
        $users = $usersQuery->fetchAll(PDO::FETCH_COLUMN);
    }

    if ($asUser['email'] !== $user['email'] && $asUser['role'] !== 'admin') {
        $linksQuery = $db->prepare('SELECT shortid, source_url, created_at, deleting_at, views, display_name FROM links WHERE owner_email = :email');
        $linksQuery->execute(['email' => $asUser['email']]);
        $links = $linksQuery->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $linksQuery = $db->prepare('SELECT shortid, source_url, created_at, deleting_at, views, display_name FROM links WHERE owner_email = :email');
        $linksQuery->execute(['email' => $email]);
        $links = $linksQuery->fetchAll(PDO::FETCH_ASSOC);
    }
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

    <meta name="description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:description" content="Unlink. Fast, free, open-source url shortener." />
    <meta property="og:title" content="profile - unlink." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://unlink.fr/profile" />
    <meta property="og:image" content="https://unlink.fr/public/icon/og-512.png" />
    <meta name="theme-color" content="#00056b">

    <?php require_once 'components/favicon.php'; ?>

    <script>
        function copyToClipboard(shortid) {
            const url = `${window.location.origin}/v/${shortid}`;
            navigator.clipboard.writeText(url).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>

    <style>
        .link-name {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
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
                        <p><strong>Email:</strong> <?= htmlspecialchars($asUser['email']) ?></p>
                        <p><strong>Member since:</strong> <?= htmlspecialchars((new DateTime($asUser['created_at']))->format('F j, Y')) ?></p>
                    </div>

                    <div class="level">
                        <p><strong>Role:</strong> <?= $asUser['role'] ?></p>
                        <p><strong>Links left:</strong> <?= $asUser['role'] === 'admin' ? 'unlimited' : 10 - count($links) ?></p>
                    </div>

                    <div class="level level-left">
                        <a class="button is-danger" href="/disappear">Delete my account</a>
                        <a class="button is-primary is-outlined" href="/forgot">Change my password</a>
                    </div>

                    <?php if ($user['role'] === 'admin'): ?>
                        <?php if ($user['email'] !== $asUser['email']): ?>
                            <div class="level">
                                <a class="button is-primary" href="/profile">View as myself</a>
                            </div>
                        <?php else: ?>
                            <div class="level">
                                <form action="/profile" method="get" class="field has-addons">
                                    <div class="select">
                                        <select name="as" required autocomplete="off">
                                            <option value="" disabled selected>Select a user</option>
                                            <?php foreach ($users as $u): ?>
                                                <option value="<?= htmlspecialchars($u) ?>"><?= htmlspecialchars($u) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="control">
                                        <button type="submit" class="button is-primary">View as user</button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <h2 class="subtitle">Your Links</h2>

                <?php if (count($links) > 0): ?>
                    <?php if ($asUser['role'] !== 'admin' && count($links) >= 10): ?>
                        <div class="notification is-warning">
                            You have reached the maximum number of links allowed.
                        </div>
                    <?php else: ?>
                        <a
                            class="button is-primary"
                            href="/create<?= $asUser['email'] !== $user['email'] ? '?as=' . htmlspecialchars($asUser['email']) : '' ?>">
                            Create a new link
                        </a>
                    <?php endif; ?>

                    <div class="table-container">
                        <table class="table is-striped is-fullwidth is-vcentered">
                            <thead>
                                <tr>
                                    <th class="has-text-centered">Name</th>
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
                                        <td class="has-text-centered link-name"><?= htmlspecialchars($link['display_name']) ?: '-' ?></td>
                                        <td class="has-text-centered"><?= htmlspecialchars((new DateTime($link['created_at']))->format('Y-m-d')) ?></td>
                                        <td class="has-text-centered"><?= $link['deleting_at'] ? htmlspecialchars((new DateTime($link['deleting_at']))->format('Y-m-d')) : '-' ?></td>
                                        <td class="has-text-centered">
                                            <a class="button is-primary is-outlined" href="<?= htmlspecialchars($link['source_url']) ?>" target="_blank">Open</a>
                                        </td>
                                        <td class="has-text-centered">
                                            <div class="level">
                                                <a class="button is-primary is-outlined" href="https://unlink.fr/v/<?= htmlspecialchars($link['shortid']) ?>" target="_blank">Open</a>
                                                <button class="button is-primary" onclick="copyToClipboard('<?= htmlspecialchars($link['shortid']) ?>')">
                                                    Copy
                                                </button>
                                            </div>
                                        </td>
                                        <td class="has-text-centered"><?= htmlspecialchars($link['views']) ?></td>

                                        <td class="has-text-centered">
                                            <div class="level">
                                                <a href="/edit?s=<?= htmlspecialchars($link['shortid']) ?>" class="button is-primary">Edit</a>
                                                <a href="/delete?s=<?= htmlspecialchars($link['shortid']) ?>" class="button is-danger">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <a
                        class="button is-primary"
                        href="/create<?= $asUser['email'] !== $user['email'] ? '?as=' . htmlspecialchars($asUser['email']) : '' ?>">
                        Shorten my first link
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</body>

</html>