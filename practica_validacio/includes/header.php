<?php
/**
 * includes/header.php
 * Capçalera i menú comuns. Cada pàgina defineix abans $titolPagina.
 */
$titolPagina = $titolPagina ?? 'Sistema';
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titolPagina, ENT_QUOTES, 'UTF-8') ?> · Sistema</title>
    <link rel="stylesheet" href="css/estils.css">
</head>
<body>
<header class="capcalera">
    <div class="contenidor capcalera-interior">
        <span class="logo">Sistema d'Usuaris</span>
        <nav class="menu" aria-label="Menú principal">
            <a href="index.php">Inici</a>
            <a href="registre.php">Registre</a>
        </nav>
    </div>
</header>
<main class="contenidor">
