# TASCA-05 · Validació d'un formulari de registre amb PHP (M613-B1)

Requisits: PHP 8.0 o superior (amb l'extensió mbstring).

## Com executar-ho
Des de la carpeta del projecte:

    php -S localhost:8000

i obre http://localhost:8000/index.php (o copia la carpeta a `htdocs` si fas servir XAMPP).

## Estructura
- `index.php` · pàgina principal
- `registre.php` · formulari (errors sota cada camp + valors conservats)
- `valida_registre.php` · POST, token CSRF, validació, redireccions
- `resultat.php` · resum de dades validades (protegit)
- `includes/validacio.php` · funcions de sessió, seguretat, normalització i validació
- `includes/header.php`, `includes/footer.php` · parts comunes
- `css/estils.css` · estils

## Mesures de seguretat
- `htmlspecialchars()` (funció `escapa()`) a tota la sortida; cap neteja a l'entrada.
- Token CSRF amb `random_bytes()` + `hash_equals()`; resposta 403 si no coincideix.
- Només es processen peticions POST; `resultat.php` protegit per la sessió.
