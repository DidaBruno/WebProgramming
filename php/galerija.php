<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';


// Pročitaj sve slike iz public/images i ubaci u bazu one kojih još nema.
$folder = __DIR__ . '/../public/images';
$datoteke = glob($folder . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE);

$stmtInsert = $pdo->prepare(
    'INSERT IGNORE INTO slike (naziv_datoteke, putanja) VALUES (?, ?)'
);
foreach ($datoteke as $putanjaApsolutna) {
    $naziv = basename($putanjaApsolutna); // npr. 01-movie.jpg
    $putanjaWeb = '../public/images/' . $naziv; // put za <img src> iz php/ mape
    $stmtInsert->execute([$naziv, $putanjaWeb]);
}

// OBRADA OCJENE (POST) - samo prijavljeni korisnik
$poruka = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ocjena'], $_POST['id_slika'])) {
    // provjera jeli korisnik prijavljen
    if (!jePrijavljen()) {
        header('Location: auth/login.php');
        exit;
    }
    $id_slika = (int)$_POST['id_slika'];
    $ocjena = (int)$_POST['ocjena'];

    if ($ocjena >= 1 && $ocjena <= 5) {
        // INSERT, a ako veza korisnik+slika već postoji -> UPDATE (ponovna ocjena)
        $stmt = $pdo->prepare(
            'INSERT INTO ocjene (id_korisnik, id_slika, ocjena) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE ocjena = VALUES(ocjena), vrijeme_ocjene = CURRENT_TIMESTAMP'
        );
        $stmt->execute([$_SESSION['korisnik_id'], $id_slika, $ocjena]);
        $poruka = 'Ocjena spremljena!';
    }
}

// DOHVAT SLIKA + PROSJEČNA OCJENA + MOJA OCJENA
$idKor = $_SESSION['korisnik_id'] ?? 0;

$stmt = $pdo->prepare(
    'SELECT s.*,
            ROUND(AVG(o.ocjena), 1) AS prosjek,
            COUNT(o.id)             AS broj_glasova,
            (SELECT ocjena FROM ocjene WHERE id_slika = s.id AND id_korisnik = ?) AS moja_ocjena
     FROM slike s
     LEFT JOIN ocjene o ON o.id_slika = s.id
     GROUP BY s.id
     ORDER BY s.naziv_datoteke ASC'
);
$stmt->execute([$idKor]);
$slike = $stmt->fetchAll();

$pageTitle = 'Galerija - Ocjenjivanje';
$cssPath = '../public/';
$basePath = '';
require_once 'includes/header.php';
?>

<link rel="stylesheet" href="../public/styles/style_images.css">

<main class="image-container" style="padding:20px;">
    <h2 style="text-align:left;">Galerija – ocijeni slike</h2>

    <?php if ($poruka): ?>
        <div class="poruka-uspjeh"><?= htmlspecialchars($poruka) ?></div>
    <?php endif; ?>

    <?php if (!jePrijavljen()): ?>
        <p><a href="auth/login.php" class="btn-link">Prijavi se</a> da bi mogao/la ocjenjivati slike.</p>
    <?php endif; ?>

    <section class="gallery">
        <?php foreach ($slike as $slika): ?>
            <figure class="ocjena-img">
                <img src="<?= htmlspecialchars($slika['putanja']) ?>"
                     alt="<?= htmlspecialchars($slika['naziv_datoteke']) ?>">

                <!-- Prosječna ocjena (IMDb stil) -->
                <figcaption class="prosjek">
                    <?php if ($slika['broj_glasova'] > 0): ?>
                        &#9733; <?= number_format((float)$slika['prosjek'], 1) ?> / 5
                        <span class="broj">(<?= (int)$slika['broj_glasova'] ?> glasova)</span>
                    <?php else: ?>
                        <span class="broj">Još nema ocjena</span>
                    <?php endif; ?>
                </figcaption>

                <!-- Zvjezdice za ocjenjivanje (samo prijavljeni) -->
                <?php if (jePrijavljen()): ?>
                    <form method="POST" class="zvjezdice">
                        <input type="hidden" name="id_slika" value="<?= (int)$slika['id'] ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="submit" name="ocjena" value="<?= $i ?>"
                                    class="zvijezda <?= ($slika['moja_ocjena'] >= $i) ? 'aktivna' : '' ?>"
                                    title="<?= $i ?> / 5">
                                &#9733;
                            </button>
                        <?php endfor; ?>
                    </form>
                    <?php if ($slika['moja_ocjena']): ?>
                        <p class="moja-ocjena">Tvoja ocjena: <?= (int)$slika['moja_ocjena'] ?>/5</p>
                    <?php endif; ?>
                <?php endif; ?>
            </figure>
        <?php endforeach; ?>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
