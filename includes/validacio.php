<?php
declare(strict_types=1);

/**
 * includes/validacio.php
 *
 * Funcions compartides per tota la pràctica:
 *   1. Sessió i seguretat (sessió, escapament de sortida, token CSRF, lectura de camps).
 *   2. Normalització de dades (una funció per camp que ho necessita).
 *   3. Validació (una funció per camp: retorna el missatge d'error o null si és correcte).
 *
 * IMPORTANT: aquí NO s'escapa ni es "neteja" res. Una dada incorrecta es rebutja.
 * L'escapament (htmlspecialchars) es fa només a la SORTIDA (registre.php i resultat.php).
 */

/* =====================================================================
 * 1. SESSIÓ I SEGURETAT
 * ===================================================================== */

/**
 * Inicia la sessió una sola vegada, amb una cookie una mica més segura.
 */
function iniciarSessio(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,   // JavaScript no pot llegir la cookie de sessió
            'samesite' => 'Lax',  // no s'envia en peticions POST creuades entre llocs
        ]);
        session_start();
    }
}

/**
 * Escapa un valor per mostrar-lo a l'HTML (protecció contra XSS).
 * S'ha de fer servir a TOT el que s'imprimeix i ve de l'usuari.
 */
function escapa(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Retorna el token CSRF de la sessió; el crea si encara no existeix.
 * El token es manté durant tota la sessió (així funciona amb diverses pestanyes).
 */
function obtenirTokenCsrf(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Llegeix un camp de $_POST: comprova que existeix, que és un string
 * (no un array tipus nom[]=x) i que és UTF-8 vàlid. Aplica trim().
 *
 * @return string|null  el valor amb trim(), o null si la dada no és acceptable.
 */
function llegirCamp(string $clau): ?string
{
    if (!isset($_POST[$clau]) || !is_string($_POST[$clau])) {
        return null;
    }
    if (!mb_check_encoding($_POST[$clau], 'UTF-8')) {
        return null;
    }
    return trim($_POST[$clau]);
}

/* =====================================================================
 * 2. NORMALITZACIÓ
 * ===================================================================== */

/** Email: passa a minúscules. */
function normalitzarEmail(string $email): string
{
    return mb_strtolower($email, 'UTF-8');
}

/** Telèfon: treu espais i guions abans de validar (666 12 34 56 -> 666123456). */
function normalitzarTelefon(string $telefon): string
{
    return preg_replace('/[\s-]+/', '', $telefon) ?? $telefon;
}

/**
 * Població: primera lletra de cada paraula en majúscula, deixant en minúscula
 * les partícules (de, del, d', l', la, i...) tret que siguin la primera paraula.
 *
 *   manresa            -> Manresa
 *   la seu d'urgell    -> La Seu d'Urgell
 *   la Seu d'Urgell    -> La Seu d'Urgell
 *
 * Es fa amb mb_* i el modificador /u perquè funcioni amb accents (UTF-8).
 */
function normalitzarPoblacio(string $poblacio): string
{
    $particules = ['de', 'del', 'dels', 'd', 'l', 'la', 'el', 'els', 'les', 'i'];
    $minuscules = mb_strtolower($poblacio, 'UTF-8');
    $primera = true;

    $resultat = preg_replace_callback(
        '/[\p{L}\p{M}]+/u',
        function (array $m) use (&$primera, $particules): string {
            $paraula = $m[0];
            $esPrimera = $primera;
            $primera = false;

            // Les partícules es queden en minúscula (si no són la primera paraula).
            if (!$esPrimera && in_array($paraula, $particules, true)) {
                return $paraula;
            }
            return mb_strtoupper(mb_substr($paraula, 0, 1, 'UTF-8'), 'UTF-8')
                 . mb_substr($paraula, 1, null, 'UTF-8');
        },
        $minuscules
    );

    return $resultat ?? $poblacio;
}

/* =====================================================================
 * 3. VALIDACIÓ (retornen el missatge d'error o null si és correcte)
 * ===================================================================== */

/** Nom: obligatori, 2-50 caràcters, lletres (amb accents), espais, apòstrofs i guions. */
function validarNom(string $nom): ?string
{
    if ($nom === '') {
        return "El nom és obligatori.";
    }
    $llargada = mb_strlen($nom, 'UTF-8');
    if ($llargada < 2 || $llargada > 50) {
        return "El nom ha de tenir entre 2 i 50 caràcters.";
    }
    // \p{L} = qualsevol lletra Unicode; \p{M} = marques d'accent combinades.
    if (!preg_match("/^[\\p{L}\\p{M} '’-]+$/u", $nom)) {
        return "El nom només pot tenir lletres, espais, apòstrofs i guions.";
    }
    return null;
}

/** Email: obligatori i amb format vàlid (ja arriba en minúscules). */
function validarEmail(string $email): ?string
{
    if ($email === '') {
        return "L'email és obligatori.";
    }
    if (mb_strlen($email, 'UTF-8') > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Introdueix un email vàlid (per exemple, anna@lacetania.cat).";
    }
    // filter_var accepta "anna@localhost"; exigim un punt al domini.
    $domini = substr($email, (int) strrpos($email, '@') + 1);
    if (!str_contains($domini, '.')) {
        return "Introdueix un email vàlid (per exemple, anna@lacetania.cat).";
    }
    return null;
}

/**
 * Edat: obligatòria, nombre enter entre 18 i 120, sense decimals.
 * Només es permeten dígits (així "18.5", "1e3" o "abc" queden fora).
 */
function validarEdat(string $edat): ?string
{
    if ($edat === '') {
        return "L'edat és obligatòria.";
    }
    if (!preg_match('/^[0-9]{1,3}$/D', $edat)) {
        return "L'edat ha de ser un nombre enter, sense decimals ni lletres.";
    }
    $numero = (int) $edat;
    if ($numero < 18 || $numero > 120) {
        return "L'edat ha d'estar entre 18 i 120 anys.";
    }
    return null;
}

/** Telèfon: opcional; si s'omple (ja sense espais ni guions), exactament 9 dígits. */
function validarTelefon(string $telefon): ?string
{
    if ($telefon === '') {
        return null; // camp opcional
    }
    if (!preg_match('/^[0-9]{9}$/D', $telefon)) {
        return "El telèfon ha de tenir exactament 9 dígits.";
    }
    return null;
}

/** Població: obligatòria, 2-50 caràcters, lletres, espais, apòstrofs i guions. */
function validarPoblacio(string $poblacio): ?string
{
    if ($poblacio === '') {
        return "La població és obligatòria.";
    }
    $llargada = mb_strlen($poblacio, 'UTF-8');
    if ($llargada < 2 || $llargada > 50) {
        return "La població ha de tenir entre 2 i 50 caràcters.";
    }
    if (!preg_match("/^[\\p{L}\\p{M} '’-]+$/u", $poblacio)) {
        return "La població només pot tenir lletres, espais, apòstrofs i guions.";
    }
    return null;
}
