<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

provjeriAdmina('films.php');

$poruka = '';
$greske = [];
$editFilm = null;

// ---- BRISANJE FILMOVA ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['obrisi'])) {
    $id = (int)$_POST['id'];
    $pdo->prepare('DELETE FROM filmovi WHERE id = ?')->execute([$id]);
    $poruka = 'Film je obrisan.';
}

// ---- UČITAJ ZA UREĐIVANJE ----
if (isset($_GET['uredi'])) {
    $stmt = $pdo->prepare('SELECT * FROM filmovi WHERE id = ?');
    $stmt->execute([(int)$_GET['uredi']]);
    $editFilm = $stmt->fetch();
}

// ---- VALIDACIJA (zajednička za dodavanje i uređivanje) ----
function validirajFilm(array $data, array &$greske): bool {
    if (empty(trim($data['naslov']))) {
        $greske[] = 'Naslov je obavezan.';
    }
    if (empty(trim($data['zanr']))) {
        $greske[] = 'Žanr je obavezan.';
    }
    $godina = (int)$data['godina'];
    if ($godina < 1888 || $godina > (int)date('Y')) {
        $greske[] = 'Godina mora biti između 1888 i ' . date('Y') . '.';
    }
    $trajanje = (int)$data['trajanje_min'];
    if ($trajanje < 1 || $trajanje > 600) {
        $greske[] = 'Trajanje mora biti između 1 i 600 minuta.';
    }
    $ocjena = (float)$data['ocjena'];
    if ($ocjena < 0.0 || $ocjena > 10.0) {
        $greske[] = 'Ocjena mora biti između 0.0 i 10.0.';
    }
    return empty($greske);
}

// ---- DODAVANJE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj'])) {
    if (validirajFilm($_POST, $greske)) {
        $stmt = $pdo->prepare(
            'INSERT INTO filmovi (naslov, zanr, godina, trajanje_min, ocjena, redatelj, zemlja)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            trim($_POST['naslov']),
            trim($_POST['zanr']),
            (int)$_POST['godina'],
            (int)$_POST['trajanje_min'],
            (float)$_POST['ocjena'],
            trim($_POST['redatelj']),
            trim($_POST['zemlja']),
        ]);
        $poruka = 'Film je uspješno dodan!';
    }
}

// ---- UREĐIVANJE ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uredi_spremi'])) {
    $id = (int)$_POST['id'];
    if (validirajFilm($_POST, $greske)) {
        $stmt = $pdo->prepare(
            'UPDATE filmovi SET naslov=?, zanr=?, godina=?, trajanje_min=?, ocjena=?, redatelj=?, zemlja=?
             WHERE id=?'
        );
        $stmt->execute([
            trim($_POST['naslov']),
            trim($_POST['zanr']),
            (int)$_POST['godina'],
            (int)$_POST['trajanje_min'],
            (float)$_POST['ocjena'],
            trim($_POST['redatelj']),
            trim($_POST['zemlja']),
            $id,
        ]);
        $poruka = 'Film je uspješno ažuriran!';
        $editFilm = null;
    } else {
        // Ostavi podatke u formi
        $editFilm = $_POST;
        $editFilm['id'] = $id;
    }
}

// Dohvati sve filmove
$filmovi = $pdo->query('SELECT * FROM filmovi ORDER BY naslov ASC')->fetchAll();

$pageTitle = 'Admin – Upravljanje filmovima';
$cssPath = '../public/';
$basePath = '';
require_once 'includes/header.php';
?>

<div class="container" style="padding:20px; flex-direction:column; max-width:1100px; margin:0 auto;">
    <h2 style="text-align:left;">Admin – Upravljanje filmovima</h2>

    <?php if ($poruka): ?>
        <div class="poruka-uspjeh"><?= htmlspecialchars($poruka) ?></div>
    <?php endif; ?>
    <?php if (!empty($greske)): ?>
        <div class="poruka-greska">
            <?php foreach ($greske as $g): ?>
                <p><?= htmlspecialchars($g) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Forma za dodavanje / uređivanje -->
    <section style="background:#2b2b2b; padding:20px; border-radius:8px; margin-bottom:25px;">
        <h3 style="text-align:left; margin-top:0;">
            <?= $editFilm ? 'Uredi film' : 'Dodaj novi film' ?>
        </h3>
        <form method="POST" class="film-form">
            <?php if ($editFilm): ?>
                <input type="hidden" name="id" value="<?= (int)$editFilm['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div>
                    <label>Naslov *</label>
                    <input type="text" name="naslov" required
                           value="<?= htmlspecialchars($editFilm['naslov'] ?? '') ?>">
                </div>
                <div>
                    <label>Žanr *</label>
                    <input type="text" name="zanr" required
                           value="<?= htmlspecialchars($editFilm['zanr'] ?? '') ?>">
                </div>
                <div>
                    <label>Godina * (1888–<?= date('Y') ?>)</label>
                    <input type="number" name="godina" required min="1888" max="<?= date('Y') ?>"
                           value="<?= htmlspecialchars($editFilm['godina'] ?? '') ?>">
                </div>
                <div>
                    <label>Trajanje (min) * (1–600)</label>
                    <input type="number" name="trajanje_min" required min="1" max="600"
                           value="<?= htmlspecialchars($editFilm['trajanje_min'] ?? '') ?>">
                </div>
                <div>
                    <label>Ocjena * (0.0–10.0)</label>
                    <input type="number" name="ocjena" required min="0" max="10" step="0.1"
                           value="<?= htmlspecialchars($editFilm['ocjena'] ?? '') ?>">
                </div>
                <div>
                    <label>Redatelj</label>
                    <input type="text" name="redatelj"
                           value="<?= htmlspecialchars($editFilm['redatelj'] ?? '') ?>">
                </div>
                <div>
                    <label>Zemlja</label>
                    <input type="text" name="zemlja"
                           value="<?= htmlspecialchars($editFilm['zemlja'] ?? '') ?>">
                </div>
            </div>

            <?php if ($editFilm): ?>
                <button type="submit" name="uredi_spremi" value="1">Spremi izmjene</button>
                <a href="dashboard.php"><button type="button">Odustani</button></a>
            <?php else: ?>
                <button type="submit" name="dodaj" value="1">Dodaj film</button>
            <?php endif; ?>
        </form>
    </section>

    <!-- Tablica svih filmova -->
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Naslov</th>
                    <th>Žanr</th>
                    <th>Godina</th>
                    <th>Trajanje</th>
                    <th>Ocjena</th>
                    <th>Zemlja</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filmovi as $film): ?>
                    <tr>
                        <td><?= (int)$film['id'] ?></td>
                        <td><?= htmlspecialchars($film['naslov']) ?></td>
                        <td><?= htmlspecialchars($film['zanr']) ?></td>
                        <td><?= (int)$film['godina'] ?></td>
                        <td><?= (int)$film['trajanje_min'] ?> min</td>
                        <td><?= number_format((float)$film['ocjena'], 1) ?></td>
                        <td><?= htmlspecialchars($film['zemlja']) ?></td>
                        <td style="white-space:nowrap;">
                            <a href="dashboard.php?uredi=<?= (int)$film['id'] ?>">
                                <button type="button" class="btn-uredi">Uredi</button>
                            </a>
                            <form method="POST" style="display:inline;"
                                  onsubmit="return confirm('Obrisati ovaj film?')">
                                <input type="hidden" name="id" value="<?= (int)$film['id'] ?>">
                                <button type="submit" name="obrisi" value="1" class="btn-ukloni">Obriši</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
