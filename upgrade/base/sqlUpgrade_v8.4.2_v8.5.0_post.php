<?php
/**
 * Supprime jquery-ui-1.14.1.custom
 */
function rrmdir_safe(string $dir): bool {
    // sécurité : refuser les chemins vides ou racine
    if ($dir === '' || $dir === '/' || $dir === '.' || $dir === '..') {
        return false;
    }

    // vérifier existence et que c'est bien un dossier
    if (!is_dir($dir)) {
        return false;
    }

    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($files as $file) {
        $path = $file->getRealPath();
        if ($path === false) {
            // problème de résolution (possible symlink cassé) — ignorer ou loguer
            continue;
        }
        // ne pas suivre/supprimer la cible d'un symlink
        if ($file->isLink()) {
            @unlink($path);
            continue;
        }
        if ($file->isDir()) {
            @rmdir($path);
        } else {
            @unlink($path);
        }
    }

    return @rmdir($dir);
}

// usage : construire le chemin à partir de homepath
$dirToRemove = rtrim($p['homepath'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'public_html' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'jquery-ui-1.14.1.custom';

if (rrmdir_safe($dirToRemove)) {
    // supprimé
} else {
    // échec — gérer l'erreur / permissions
}

// composer
$initialDir=getcwd();

$pathToComposer='php '.$p['homepath'].'composer.phar';
file_put_contents($p['homepath'].'composer.phar', fopen("https://getcomposer.org/download/latest-stable/composer.phar", 'r'));

// COMPOSER_DISCARD_CHANGE pour écraser jquery et un changement non commité dans le paquet
chdir($p['config']['webDirectory']);
exec('COMPOSER_HOME="/tmp/" COMPOSER_DISCARD_CHANGES=true '.$pathToComposer.' update --no-dev --prefer-dist -o 2>&1', $output);
chdir($p['homepath']);
exec('COMPOSER_HOME="/tmp/" COMPOSER_DISCARD_CHANGES=true '.$pathToComposer.' update --no-dev --prefer-dist -o 2>&1', $output);
chdir($initialDir);
