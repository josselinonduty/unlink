<?php
session_start();

try {
    if (!isset($_SESSION['email'])) {
        header("Location: /login");
        exit();
    }

    $email = $_SESSION['email'];

    $db = new PDO('sqlite:../data/unlink.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $deleteLinks = $db->prepare('DELETE FROM links WHERE owner_email = :email');
    $deleteLinks->execute(['email' => $email]);

    $deleteUser = $db->prepare('DELETE FROM users WHERE email = :email');
    $deleteUser->execute(['email' => $email]);

    if ($deleteUser->rowCount() > 0) {
        session_unset();
        session_destroy();
        $message = "Your account has been successfully deleted. Redirecting to the homepage...";
        $redirectUrl = '/';
    } else {
        $message = "An error occurred while deleting your account. Redirecting to your profile...";
        $redirectUrl = '/profile';
    }
} catch (PDOException $e) {
    $message = "An error occurred while processing your request. Redirecting to your profile...";
    $redirectUrl = '/profile';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>disappear - unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>
    <meta http-equiv="refresh" content="3;url=<?= htmlspecialchars($redirectUrl) ?>">
</head>

<body>
    <?php require_once 'components/navbar.php'; ?>

    <section class="section">
        <div class="container">
            <div class="notification <?= isset($error) ? 'is-danger' : 'is-success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        </div>
    </section>
</body>

</html>