<?php
declare(strict_types=1);

/**
 * resultat.php
 * Resum de les dades validades. Només s'hi pot arribar després d'una
 * validació correcta; si no, es redirigeix al formulari.
 */
require_once __DIR__ . '/includes/validacio.php';
iniciarSessio();

// Pàgina protegida: sense dades validades a la sessió, fora.
if (!isset($_SESSION['dades_validades']) || !is_array($_SESSION['dades_validades'])) {
    header('Location: registre.php');
    exit;
}

// Passem les dades a una variable local i esborrem la sessió: si es recarrega
// la pàgina ja no hi haurà dades i es redirigirà a registre.php.
$dades = $_SESSION['dades_validades'];
unset($_SESSION['dades_validades']);

$telefon = $dades['telefon'] ?? '';

$titolPagina = 'Registre completat';
require __DIR__ . '/includes/header.php';
?>
<section class="targeta">
    <h1>Registre completat</h1>
    <p class="avis-ok">Les dades s'han validat correctament.</p>

    <table class="taula-resum">
        <tr><th scope="row">Nom</th><td><?= escapa($dades['nom'] ?? '') ?></td></tr>
        <tr><th scope="row">Email</th><td><?= escapa($dades['email'] ?? '') ?></td></tr>
        <tr><th scope="row">Edat</th><td><?= escapa($dades['edat'] ?? '') ?></td></tr>
        <tr><th scope="row">Telèfon</th><td><?= $telefon === '' ? 'No indicat' : escapa($telefon) ?></td></tr>
        <tr><th scope="row">Població</th><td><?= escapa($dades['poblacio'] ?? '') ?></td></tr>
    </table>

    <p><a class="boto" href="index.php">Tornar a l'inici</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
