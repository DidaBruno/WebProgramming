<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// redirect na films ako je korisnik vec prijavljen
if (jePrijavljen()) {
    header('Location: ../films.php');
    exit;
}

$greske = [];
$uspjeh = '';

// obrada post requesta nakon klika na submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $korisnicko_ime = trim($_POST['korisnicko_ime'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $lozinka = $_POST['lozinka'] ?? '';
    $lozinka_potvrda = $_POST['lozinka_potvrda'] ?? '';

    if (empty($korisnicko_ime) || strlen($korisnicko_ime) < 3) {
        $greske[] = 'Korisničko ime mora imati najmanje 3 znaka.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $greske[] = 'Unesite ispravan email.';
    }
    if (strlen($lozinka) < 6) {
        $greske[] = 'Lozinka mora imati najmanje 6 znakova.';
    }
    if ($lozinka !== $lozinka_potvrda) {
        $greske[] = 'Lozinke se ne podudaraju.';
    }

    if (empty($greske)) {
        $stmt = $pdo->prepare('SELECT id FROM korisnici WHERE korisnicko_ime = ? OR email = ?'); // zađštita od SQL injectiona
        $stmt->execute([$korisnicko_ime, $email]); 
        if ($stmt->fetch()) {
            $greske[] = 'Korisničko ime ili email već postoji.';
        } else {
            $hash = password_hash($lozinka, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'INSERT INTO korisnici (korisnicko_ime, email, lozinka) VALUES (?, ?, ?)'
            );
            $stmt->execute([$korisnicko_ime, $email, $hash]);
            $uspjeh = 'Registracija uspješna! <a href="login.php">Prijavite se</a>.';
        }
    }
}

$pageTitle = 'Registracija';
$cssPath = '../../public/';
$basePath = '../';
require_once '../includes/header.php';
?>

<div class="container" style="justify-content:center; padding:20px;">
    <main style="max-width:480px; width:100%;">
        <h2>Registracija</h2>

        <?php if ($uspjeh): ?>
            <div class="poruka-uspjeh"><?= $uspjeh ?></div>
        <?php endif; ?>

        <?php if (!empty($greske)): ?>
            <div class="poruka-greska">
                <?php foreach ($greske as $g): ?>
                    <p><?= htmlspecialchars($g) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <label>Korisničko ime</label>
            <!-- vraca upisanu vrijednost nakon neuspjeha da korisnik ne mora ponovno tipkati ime/email -->
            <input type="text" name="korisnicko_ime"
                   value="<?= htmlspecialchars($_POST['korisnicko_ime'] ?? '') ?>"
                   required minlength="3">

            <label>Email</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required>

            <label>Lozinka</label>
            <input type="password" name="lozinka" required minlength="6">

            <label>Potvrda lozinke</label>
            <input type="password" name="lozinka_potvrda" required>

            <button type="submit">Registriraj se</button>
        </form>

        <p style="margin-top:15px;">Već imaš račun? <a href="login.php">Prijavi se</a>.</p>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>
