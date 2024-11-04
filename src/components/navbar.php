<?php
session_start();
$isConnected = isset($_SESSION['email']);
?>

<nav class="navbar is-light" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
        <a class="navbar-item" href="/">
            <strong>unlink.</strong>
        </a>

        <a role="navbar-button" class="navbar-burger" data-target="navbarContent" aria-label="menu" aria-expanded="false">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </a>
    </div>

    <div class="navbar-menu" id="navbarContent">
        <div class="navbar-end">
            <div class="buttons is-grouped navbar-item">
                <?php if ($isConnected): ?>
                    <a class="button is-outlined is-primary" href="/logout">Logout</a>
                    <a class="button is-primary" href="/profile">Profile</a>
                <?php else: ?>
                    <a class="button is-outlined is-primary" href="/register">Register</a>
                    <a class="button is-primary" href="/login">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>