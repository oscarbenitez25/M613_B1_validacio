<?php
declare(strict_types=1);

/**
 * valida_registre.php
 * Rep el POST del formulari, valida tots els camps i redirigeix:
 *   - amb errors   -> registre.php  (errors + dades enviades a la sessió)
 *   - tot correcte -> resultat.php  (dades validades a la sessió)
 *
 * Aquest fitxer no imprimeix HTML ni escapa res: una dada incorrecta es
 * rebutja amb un missatge d'error. L'escapament es fa a la sortida.
 */
require_once __DIR__ . '/includes/validacio.php';
iniciarSessio();

// 1) Només acceptem peticions POST.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: registre.php');
    exit;
}

// 2) Comprovació del token CSRF amb hash_equals() (comparació segura).
$tokenPost   = $_POST['csrf_token'] ?? null;
$tokenSessio = $_SESSION['csrf_token'] ?? null;
if (!is_string($tokenPost) || !is_string($tokenSessio) || !hash_equals($tokenSessio, $tokenPost)) {
    http_response_code(403);
    exit('403 · Petició rebutjada: el token CSRF no és vàlid.');
}

// 3) Lectura dels camps (existeixen, són string, trim()).
$enviat = [];   // valors tal com els ha escrit l'usuari (amb trim) per tornar-los al formulari
$errors = [];   // errors per camp
$dades  = [];   // dades validades i normalitzades

foreach (['nom', 'email', 'edat', 'telefon', 'poblacio'] as $camp) {
    $valor = llegirCamp($camp);
    if ($valor === null) {
        $errors[$camp] = 'Dada no enviada o amb un format incorrecte.';
        $enviat[$camp] = '';
    } else {
        $enviat[$camp] = $valor;
    }
}

// 4) Validació i normalització camp per camp (només si la lectura ha anat bé).
if (!isset($errors['nom'])) {
    $error = validarNom($enviat['nom']);
    if ($error !== null) { $errors['nom'] = $error; } else { $dades['nom'] = $enviat['nom']; }
}

if (!isset($errors['email'])) {
    $email = normalitzarEmail($enviat['email']);          // a minúscules
    $error = validarEmail($email);
    if ($error !== null) { $errors['email'] = $error; } else { $dades['email'] = $email; }
}

if (!isset($errors['edat'])) {
    $error = validarEdat($enviat['edat']);
    if ($error !== null) { $errors['edat'] = $error; } else { $dades['edat'] = (int) $enviat['edat']; } // es desa com a int
}

if (!isset($errors['telefon'])) {
    $telefon = normalitzarTelefon($enviat['telefon']);    // sense espais ni guions
    $error   = validarTelefon($telefon);
    if ($error !== null) { $errors['telefon'] = $error; } else { $dades['telefon'] = $telefon; }
}

if (!isset($errors['poblacio'])) {
    $poblacio = normalitzarPoblacio($enviat['poblacio']); // majúscules inicials
    $error    = validarPoblacio($poblacio);
    if ($error !== null) { $errors['poblacio'] = $error; } else { $dades['poblacio'] = $poblacio; }
}

// 5) Redirecció segons el resultat (sempre amb exit després de header()).
if ($errors) {
    $_SESSION['errors']          = $errors;
    $_SESSION['dades_formulari'] = $enviat;
    header('Location: registre.php');
    exit;
}

unset($_SESSION['errors'], $_SESSION['dades_formulari']);
$_SESSION['dades_validades'] = $dades;
header('Location: resultat.php');
exit;
