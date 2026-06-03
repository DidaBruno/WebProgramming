<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

provjeriPrijavu('auth/login.php');

$poruka = '';

// Uklanjanje filma iz videoteke
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ukloni'])) {
    $id_filma = (int)$_POST['id_filma'];
    $stmt = $pdo->prepare(
        'DELETE FROM zeljeni_filmovi WHERE id_korisnika = ? AND id_filma = ?'
    );
    $stmt->execute([$_SESSION['korisnik_id'], $id_filma]);
    $poruka = 'Film je uklonjen iz vaše videoteke.';
}

// Dohvati filmove korisnika
$stmt = $pdo->prepare(
    'SELECT f.*, zf.dodano_at
     FROM zeljeni_filmovi zf
     JOIN filmovi f ON f.id = zf.id_filma
     WHERE zf.id_korisnika = ?
     ORDER BY zf.dodano_at DESC'
);
$stmt->execute([$_SESSION['korisnik_id']]);
$mojiFilmovi = $stmt->fetchAll();

$pageTitle = 'Moja videoteka';
$cssPath = '../public/';
$basePath = '';
require_once 'includes/header.php';
?>

<div class="container" style="padding:20px;">
    <main>
        <h2 style="text-align:left;">
            Moja videoteka
            <span style="font-size:14px; font-weight:normal; color:#aaa;">
                (<?= htmlspecialchars($_SESSION['korisnicko_ime']) ?>)
            </span>
        </h2>

        <?php if ($poruka): ?>
            <div class="poruka-uspjeh"><?= htmlspecialchars($poruka) ?></div>
        <?php endif; ?>

        <?php if (empty($mojiFilmovi)): ?>
            <p>Vaša videoteka je prazna. <a href="films.php">Dodajte filmove</a>.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Naslov</th>
                            <th>Žanr</th>
                            <th>Godina</th>
                            <th>Trajanje (min)</th>
                            <th>Zemlja</th>
                            <th>Ocjena</th>
                            <th>Dodano</th>
                            <th>Ukloni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mojiFilmovi as $film): ?>
                            <tr>
                                <td><?= htmlspecialchars($film['naslov']) ?></td>
                                <td><?= htmlspecialchars($film['zanr']) ?></td>
                                <td><?= (int)$film['godina'] ?></td>
                                <td><?= (int)$film['trajanje_min'] ?></td>
                                <td><?= htmlspecialchars($film['zemlja']) ?></td>
                                <td><?= number_format((float)$film['ocjena'], 1) ?></td>
                                <td><?= date('d.m.Y.', strtotime($film['dodano_at'])) ?></td>
                                <td>
                                    <form method="POST" style="margin:0;"
                                          onsubmit="return confirm('Ukloniti ovaj film?')">
                                        <input type="hidden" name="id_filma"
                                               value="<?= (int)$film['id'] ?>">
                                        <button type="submit" name="ukloni"
                                                value="1" class="btn-ukloni">✕</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <p style="margin-top:20px;"><a href="films.php" class="btn-link">← Natrag na popis filmova</a></p>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
