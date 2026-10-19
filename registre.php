<?php
declare(strict_types=1);

/**
 * registre.php
 * Mostra el formulari de registre. Si venim d'una validació fallida, mostra
 * els errors sota cada camp i conserva els valors escrits.
 */
require_once __DIR__ . '/includes/validacio.php';
iniciarSessio();

// 1) Recuperem errors i dades antigues de la sessió...
$errors = $_SESSION['errors'] ?? [];
$antics = $_SESSION['dades_formulari'] ?? [];

// 2) ...i els esborrem JA: ara viuen només en aquestes variables locals.
//    Així, si l'usuari recarrega la pàgina, els errors no tornen a sortir.
//    (El token CSRF NO s'esborra: ha de continuar sent vàlid.)
unset($_SESSION['errors'], $_SESSION['dades_formulari']);

$token = obtenirTokenCsrf();

/**
 * Pinta un camp del formulari: <label>, <input> i, si cal, l'error a sota.
 * Tot el que ve de l'usuari (valor antic) passa per escapa() = htmlspecialchars().
 */
function pintaCamp(string $nom, string $etiqueta, array $errors, array $antics, array $atributs): void
{
    $error  = $errors[$nom] ?? null;
    $valor  = $antics[$nom] ?? '';
    $classe = $error !== null ? 'camp camp-error' : 'camp';
    ?>
    <div class="<?= $classe ?>">
        <label for="<?= $nom ?>"><?= escapa($etiqueta) ?></label>
        <input id="<?= $nom ?>" name="<?= $nom ?>" value="<?= escapa($valor) ?>"
            <?php foreach ($atributs as $clau => $val): ?>
                <?= $val === true ? $clau : $clau . '="' . escapa($val) . '"' ?>
            <?php endforeach; ?>
            <?php if ($error !== null): ?>
                aria-invalid="true" aria-describedby="error-<?= $nom ?>"
            <?php endif; ?>>
        <?php if ($error !== null): ?>
            <p class="missatge-error" id="error-<?= $nom ?>"><?= escapa($error) ?></p>
        <?php endif; ?>
    </div>
    <?php
}

$titolPagina = "Registre d'Usuari";
require __DIR__ . '/includes/header.php';
?>
<section class="targeta">
    <h1>Registre d'Usuari</h1>
    <p><a href="index.php">&larr; Tornar a l'índex</a></p>

    <?php if ($errors): ?>
        <div class="avis-error" role="alert">
            Hi ha dades incorrectes. Revisa els camps marcats en vermell.
        </div>
    <?php endif; ?>

    <!--
        novalidate: la validació que compta és la del SERVIDOR. Els atributs
        required/maxlength només són una ajuda per a l'usuari.
    -->
    <form action="valida_registre.php" method="post" novalidate>
        <!-- Token CSRF: valida_registre.php el compara amb el de la sessió -->
        <input type="hidden" name="csrf_token" value="<?= escapa($token) ?>">

        <?php
        pintaCamp('nom', 'Nom', $errors, $antics,
            ['type' => 'text', 'required' => true, 'minlength' => '2', 'maxlength' => '50', 'autocomplete' => 'given-name']);
        pintaCamp('email', 'Email', $errors, $antics,
            ['type' => 'email', 'required' => true, 'autocomplete' => 'email']);
        pintaCamp('edat', 'Edat', $errors, $antics,
            ['type' => 'text', 'inputmode' => 'numeric', 'required' => true]);
        pintaCamp('telefon', 'Telèfon (opcional)', $errors, $antics,
            ['type' => 'tel', 'autocomplete' => 'tel']);
        pintaCamp('poblacio', 'Població', $errors, $antics,
            ['type' => 'text', 'required' => true, 'minlength' => '2', 'maxlength' => '50', 'autocomplete' => 'address-level2']);
        ?>

        <button type="submit" class="boto">Registrar-me</button>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
