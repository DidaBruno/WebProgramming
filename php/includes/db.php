<?php
// ova datoteka otvara vezu prema MySQL bazi.
// svaka stranica koja koristi bazu uključuje se preko require

$host = 'localhost';
$dbname = 'videoteka';
$db_user = 'root';
$db_pass = '';

try {
    // stvara pdo objekt
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", //dns
        $db_user,
        $db_pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // ukoliko upit ne uspije baca exception
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // prilikom fetch vraća asocijativni niz
} catch (PDOException $e) {
    die("Greška pri spajanju na bazu: " . $e->getMessage());
}
?>
