<?php
try {
    if (!isset($_GET['s'])) {
        displayErrorPage('No short link ID provided.');
        exit();
    }

    $shortid = $_GET['s'];

    $db = new PDO('sqlite:../data/unlink.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $db->prepare('SELECT source_url FROM links WHERE shortid = :shortid');
    $query->execute(['shortid' => $shortid]);
    $link = $query->fetch(PDO::FETCH_ASSOC);

    if (!$link) {
        displayErrorPage('The short link does not exist.');
        exit();
    }

    $sourceUrl = $link['source_url'];

    $incrementQuery = $db->prepare('UPDATE links SET views = views + 1 WHERE shortid = :shortid');
    $incrementQuery->execute(['shortid' => $shortid]);

    header("Location: $sourceUrl");
    exit();
} catch (PDOException $e) {
    displayErrorPage('An error occurred while processing your request. Please try again.');
    exit();
}

function displayErrorPage($errorMessage)
{
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>lost - unlink.</title>
        <link rel="stylesheet" href="/public/styles/main.css">
        <script src="/public/scripts/bulma.js" defer></script>

        <meta name="description" content="Unlink. Fast, free, open-source url shortener." />
        <meta property="og:description" content="Unlink. Fast, free, open-source url shortener." />
        <meta property="og:title" content="lost - unlink." />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://unlink.fr/lost" />
        <meta property="og:image" content="https://unlink.fr/public/icon/og-512.png" />
        <meta name="theme-color" content="#00056b">

        <?php require_once 'components/favicon.php'; ?>
    </head>

    <body>
        <?php require_once 'components/navbar.php'; ?>

        <section class="section">
            <div class="container">
                <div class="notification is-danger">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
                <p class="has-text-centered">
                    <a href="/" class="button is-primary">Go back to safer space</a>
                </p>
            </div>
        </section>
    </body>

    </html>
<?php
}
?>