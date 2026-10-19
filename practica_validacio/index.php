<?php
/**
 * index.php
 * Pàgina principal: benvinguda i enllaç al formulari de registre.
 */
$titolPagina = 'Inici';
require __DIR__ . '/includes/header.php';
?>
<section class="targeta">
    <h1>Benvingut/da al Sistema</h1>
    <p>
        Aquest sistema permet registrar nous usuaris. Les dades que introdueixes
        es validen al servidor i, si hi ha algun error, te'l mostrem sota cada camp
        sense que perdis el que ja havies escrit.
    </p>
    <a class="boto" href="registre.php">Registre d'Usuari</a>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
