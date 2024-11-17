<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <?php require_once '../src/components/favicon.php'; ?>
</head>

<body>
    <?php require_once '../src/components/navbar.php'; ?>

    <?php

    $db = new PDO('sqlite:../data/unlink.db');

    $stmt = $db->prepare("SELECT * FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll();

    echo "<table border='1'>";
    echo "<thead><th>Email</th><th>Token</th><th>Password Hash</th><th>Created At</th><th>Reset Token</th><th>Reset Token Expiry</th><th>Role</th></tr></thead>";
    echo "<tbody>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['token'] . "</td>";
        echo "<td>" . $user['password_hash'] . "</td>";
        echo "<td>" . $user['created_at'] . "</td>";
        echo "<td>" . $user['reset_token'] . "</td>";
        echo "<td>" . $user['reset_token_expires'] . "</td>";
        echo "<td>" . $user['role'] . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table><br/>";

    $stmt = $db->prepare("SELECT * FROM links");
    $stmt->execute();
    $links = $stmt->fetchAll();

    echo "<table border='1'>";
    echo "<tr><th>Short ID</th><th>Source URL</th><th>Owner Email</th><th>Created At</th><th>Deleting At</th><th>Views</th></tr>";
    foreach ($links as $link) {
        echo "<tr>";
        echo "<td>" . $link['shortid'] . "</td>";
        echo "<td>" . $link['source_url'] . "</td>";
        echo "<td>" . $link['owner_email'] . "</td>";
        echo "<td>" . $link['created_at'] . "</td>";
        echo "<td>" . $link['deleting_at'] . "</td>";
        echo "<td>" . $link['views'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    ?>

</body>

</html>