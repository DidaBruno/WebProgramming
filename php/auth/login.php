<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// redirect na films ako je korisnik vec prijavljen
if (jePrijavljen()) {
    header('Location: ../films.php');
    exit;
}

$greska = '';

// obrada post requesta nakon klika submit buttona
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $korisnicko_ime = trim($_POST['korisnicko_ime'] ?? '');
    $lozinka = $_POST['lozinka'] ?? '';

    if (!empty($korisnicko_ime) && !empty($lozinka)) {
        $stmt = $pdo->prepare('SELECT * FROM korisnici WHERE korisnicko_ime = ?');
        $stmt->execute([$korisnicko_ime]);
        $korisnik = $stmt->fetch();

        if ($korisnik && password_verify($lozinka, $korisnik['lozinka'])) {
            // spremanje korisnika u sesiju
            $_SESSION['korisnik_id'] = $korisnik['id'];
            $_SESSION['korisnicko_ime'] = $korisnik['korisnicko_ime'];
            $_SESSION['uloga'] = $korisnik['uloga'];
            header('Location: ../films.php');
            exit;
        } else {
            $greska = 'Pogrešno korisničko ime ili lozinka.';
        }
    } else {
        $greska = 'Unesite korisničko ime i lozinku.';
    }
}

$pageTitle = 'Prijava';
$cssPath = '../../public/';
$basePath = '../';
require_once '../includes/header.php';
?>

<div class="container" style="justify-content:center; padding:20px;">
    <main style="max-width:400px; width:100%;">
        <h2>Prijava</h2>

        <?php if ($greska): ?>
            <div class="poruka-greska"><p><?= htmlspecialchars($greska) ?></p></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <label>Korisničko ime</label>
            <!-- vraca upisanu vrijednost nakon neuspjeha da korisnik ne mora ponovno tipkati ime -->
            <input type="text" name="korisnicko_ime"
                   value="<?= htmlspecialchars($_POST['korisnicko_ime'] ?? '') ?>"
                   required>

            <label>Lozinka</label>
            <input type="password" name="lozinka" required>

            <button type="submit">Prijavi se</button>
        </form>

        <p style="margin-top:15px;">Nemaš račun? <a href="register.php">Registriraj se</a>.</p>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
