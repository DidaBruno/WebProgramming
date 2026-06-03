<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Dohvati sve zanrove i zemlje za filter dropdown
$zanrovi = $pdo->query("SELECT DISTINCT zanr FROM filmovi ORDER BY zanr")->fetchAll(PDO::FETCH_COLUMN);
$zemlje = $pdo->query("SELECT DISTINCT zemlja FROM filmovi ORDER BY zemlja")->fetchAll(PDO::FETCH_COLUMN);

// Filtriranje i sortiranje
$where = [];
$params = [];

$filterZanr = $_GET['zanr'] ?? '';
$filterGodOd = $_GET['god_od'] ?? '';
$filterGodDo = $_GET['god_do'] ?? '';
$filterZemlja = $_GET['zemlja'] ?? '';
$sort = $_GET['sort'] ?? '';

if ($filterZanr !== '') {
    $where[] = 'zanr LIKE ?';
    $params[] = '%' . $filterZanr . '%';
}
if ($filterGodOd !== '' && is_numeric($filterGodOd)) {
    $where[] = 'godina >= ?';
    $params[] = (int)$filterGodOd;
}
if ($filterGodDo !== '' && is_numeric($filterGodDo)) {
    $where[] = 'godina <= ?';
    $params[] = (int)$filterGodDo;
}
if ($filterZemlja !== '') {
    $where[] = 'zemlja LIKE ?';
    $params[] = '%' . $filterZemlja . '%';
}

$sql = 'SELECT * FROM filmovi';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$orderMap = [
    'godina-asc'  => 'godina ASC',
    'godina-desc' => 'godina DESC',
    'ocjena-asc'  => 'ocjena ASC',
    'ocjena-desc' => 'ocjena DESC',
    'naslov-asc'  => 'naslov ASC',
];
$sql .= ' ORDER BY ' . ($orderMap[$sort] ?? 'naslov ASC');

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$filmovi = $stmt->fetchAll();


// Dodavanje u videoteku (POST)
$poruka = '';
$upozorenje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_film'])) {
    // redirect na login ako korisnik nije prijavljen
    if (!jePrijavljen()) {
        header('Location: auth/login.php');
        exit;
    }
    $id_filma = (int)$_POST['id_filma'];

    $stmtFilm = $pdo->prepare('SELECT ocjena FROM filmovi WHERE id = ?');
    $stmtFilm->execute([$id_filma]);
    $film = $stmtFilm->fetch();

    if ($film) {
        if ((float)$film['ocjena'] < 5.0) {
            $upozorenje = 'Ovaj film ima nisku ocjenu – jeste li sigurni da ga želite dodati?';
            // Ako korisnik potvrdio (drugi submit s potvrdom)
            if (isset($_POST['potvrdi'])) {
                _dodajUVideoteku($pdo, $_SESSION['korisnik_id'], $id_filma, $poruka);
            }
        } else {
            _dodajUVideoteku($pdo, $_SESSION['korisnik_id'], $id_filma, $poruka);
        }
    }
}

// funkcija za dodavanje videoteke u SQL bazu (u zeljeni_filmovi)
function _dodajUVideoteku(PDO $pdo, int $id_kor, int $id_filma, string &$poruka): void {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO zeljeni_filmovi (id_korisnika, id_filma) VALUES (?, ?)'
        );
        $stmt->execute([$id_kor, $id_filma]);
        $poruka = 'Film je dodan u vašu videoteku!';
    } catch (PDOException $e) {
        $poruka = 'Film je već u vašoj videoteci.';
    }
}

$pageTitle = 'Filmovi - Videoteka';
$cssPath = '../public/';
$basePath = '';
require_once 'includes/header.php';
?>

<div class="container" style="padding:20px;">
    <main>
        <h2 style="text-align:left;">Popis filmova</h2>

        <?php if ($poruka): ?>
            <div class="poruka-uspjeh"><?= htmlspecialchars($poruka) ?></div>
        <?php endif; ?>

        <?php if ($upozorenje && !isset($_POST['potvrdi'])): ?>
            <div class="poruka-upozorenje">
                <p>&#9888; <?= htmlspecialchars($upozorenje) ?></p>
                <form method="POST">
                    <input type="hidden" name="id_filma" value="<?= (int)$_POST['id_filma'] ?>">
                    <input type="hidden" name="dodaj_film" value="1">
                    <input type="hidden" name="potvrdi" value="1">
                    <button type="submit" name="dodaj_film" value="1">Da, dodaj svejedno</button>
                    <a href="films.php"><button type="button">Odustani</button></a>
                </form>
            </div>
        <?php endif; ?>

        <!-- Filteri -->
        <form method="GET" id="filteri">
            <select name="zanr">
                <option value="">-- Odaberi žanr --</option>
                <?php foreach ($zanrovi as $z): ?>
                    <option value="<?= htmlspecialchars($z) ?>"
                        <?= $filterZanr === $z ? 'selected' : '' ?>>
                        <?= htmlspecialchars($z) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="number" name="god_od" placeholder="Godina od"
                   value="<?= htmlspecialchars($filterGodOd) ?>" min="1888" max="2026">
            <input type="number" name="god_do" placeholder="Godina do"
                   value="<?= htmlspecialchars($filterGodDo) ?>" min="1888" max="2026">

            <select name="zemlja">
                <option value="">-- Odaberi zemlju --</option>
                <?php foreach ($zemlje as $z): ?>
                    <option value="<?= htmlspecialchars($z) ?>"
                        <?= $filterZemlja === $z ? 'selected' : '' ?>>
                        <?= htmlspecialchars($z) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Filtriraj</button>

            <select name="sort" onchange="this.form.submit()">
                <option value="">Sortiraj</option>
                <option value="godina-asc"  <?= $sort === 'godina-asc'  ? 'selected' : '' ?>>Godina ↑</option>
                <option value="godina-desc" <?= $sort === 'godina-desc' ? 'selected' : '' ?>>Godina ↓</option>
                <option value="ocjena-asc"  <?= $sort === 'ocjena-asc'  ? 'selected' : '' ?>>Ocjena ↑</option>
                <option value="ocjena-desc" <?= $sort === 'ocjena-desc' ? 'selected' : '' ?>>Ocjena ↓</option>
                <option value="naslov-asc"  <?= $sort === 'naslov-asc'  ? 'selected' : '' ?>>Naslov A-Z</option>
            </select>

            <?php if ($filterZanr || $filterGodOd || $filterGodDo || $filterZemlja || $sort): ?>
                <a href="films.php"><button type="button">Resetiraj filtere</button></a>
            <?php endif; ?>
        </form>

        <?php if (jeAdmin()): ?>
            <div style="margin-bottom:15px;">
                <a href="dashboard.php"><button type="button">+ Upravljanje filmovima (Admin)</button></a>
            </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table id="filmovi-tablica">
                <thead>
                    <tr>
                        <th>Naslov</th>
                        <th>Žanr</th>
                        <th>Godina</th>
                        <th>Trajanje (min)</th>
                        <th>Zemlja</th>
                        <th>Ocjena</th>
                        <th>Videoteka</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filmovi as $film): ?>
                        <tr>
                            <td><?= htmlspecialchars($film['naslov']) ?></td>
                            <td><?= htmlspecialchars($film['zanr']) ?></td>
                            <td><?= (int)$film['godina'] ?></td>
                            <td><?= (int)$film['trajanje_min'] ?></td>
                            <td><?= htmlspecialchars($film['zemlja']) ?></td>
                            <td><?= number_format((float)$film['ocjena'], 1) ?></td>
                            <td>
                                <?php if (jePrijavljen()): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="id_filma"
                                               value="<?= (int)$film['id'] ?>">
                                        <button type="submit" name="dodaj_film"
                                                value="1" class="btn-dodaj">+</button>
                                    </form>
                                <?php else: ?>
                                    <a href="auth/login.php" class="btn-prijava">Prijavi se</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($filmovi)): ?>
                        <tr><td colspan="7" style="text-align:center;">Nema filmova za odabrane filtere.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
