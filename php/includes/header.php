<?php
// $cssPath i $basePath se postavljaju u svakoj stranici koja uključuje ovaj header
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Movie Database - Virtualna Videoteka">
    <link rel="stylesheet" href="<?= $cssPath ?>styles/style.css">
    <link rel="stylesheet" href="<?= $cssPath ?>styles/videoteka.css">
    <title><?= htmlspecialchars($pageTitle ?? 'Movie Database') ?></title>
</head>
<body>
<header>
    <h1>Movie Database</h1>
</header>

<nav class="nav-menu" aria-label="Main navigation">
    <ul>
        <li><a href="<?= $cssPath ?>index.html">Home</a></li>
        <li><a href="<?= $cssPath ?>pages/cart.html">Cart</a></li>
        <li><a href="<?= $cssPath ?>pages/charts.html">Charts</a></li>
        <li class="nav-new"><a href="<?= $basePath ?>films.php">Videoteka</a></li>
        <li class="nav-new"><a href="<?= $basePath ?>galerija.php">Galerija</a></li>
        <?php if (jePrijavljen()): ?>
            <li class="nav-new"><a href="<?= $basePath ?>videoteka.php">Moja videoteka</a></li>
            <?php if (jeAdmin()): ?>
                <li class="nav-new"><a href="<?= $basePath ?>dashboard.php">Admin</a></li>
            <?php endif; ?>
            <li class="nav-new"><a href="<?= $basePath ?>auth/logout.php">Odjava (<?= htmlspecialchars($_SESSION['korisnicko_ime']) ?>)</a></li>
        <?php else: ?>
            <li class="nav-new"><a href="<?= $basePath ?>auth/login.php">Prijava</a></li>
            <li class="nav-new"><a href="<?= $basePath ?>auth/register.php">Registracija</a></li>
        <?php endif; ?>
    </ul>
</nav>
