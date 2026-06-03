<?php

// stiti stranice koje smiju vidjeti samo prijavljeni korisnici
function provjeriPrijavu(string $redirectPath = '../auth/login.php'): void {
    if (!isset($_SESSION['korisnik_id'])) {
        header("Location: $redirectPath");
        exit;
    }
}

// stiti stranice koje smije vidjet samo admin
function provjeriAdmina(string $redirectPath = '../films.php'): void {
    if (!isset($_SESSION['korisnik_id']) || $_SESSION['uloga'] !== 'administrator') {
        header("Location: $redirectPath");
        exit;
    }
}

function jePrijavljen(): bool {
    return isset($_SESSION['korisnik_id']);
}

function jeAdmin(): bool {
    return isset($_SESSION['uloga']) && $_SESSION['uloga'] === 'administrator';
}
?>
