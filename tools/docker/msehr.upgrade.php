<?php

// Check if the required argument is provided
if ($argc < 2) {
    echo "Usage: php msehr.upgrade.php <repo>\n\n base, chiro, gyneco, general, thermal, mpr, osteo\n";
    exit(1);
}

// Extract the shorthand repo argument
$repoShorthand = $argv[1];

// Define a mapping of shorthand names to GitHub user/repo
$repoMap = [
    'base' => 'MedShake/MedShakeEHR-base',
    'chiro' => 'MedShake/MedShakeEHR-modChiro',
    'gyneco' => 'MedShake/MedShakeEHR-modGynObs',
    'general' => 'MedShake/MedShakeEHR-modMedGe',
    'thermal' => 'MedShake/MedShakeEHR-modMedTher',
    'mpr' => 'MedShake/MedShakeEHR-modMPR',
    'osteo' => 'marsante/MedShakeEHR-modOsteo',
    
];

// Check if the repo shorthand exists in the map
if (!isset($repoMap[$repoShorthand])) {
    echo "Invalid repo shorthand.\n";
    exit(1);
}

// Extract the user and repo from the repo map
$repoInfo = explode('/', $repoMap[$repoShorthand]);
$user = $repoInfo[0];
$repo = $repoInfo[1];

try {
    // Extract the database credentials from config.yml
    $configFile = '/var/www/html/config/config.yml';
    $config = yaml_parse_file($configFile);

    $sqlServer = $config['sqlServeur'];
    $sqlBase = $config['sqlBase'];
    $sqlUser = $config['sqlUser'];
    $sqlPass = $config['sqlPass'];

    // Update the value of the 'state' column in the 'system' table
    $dsn = "mysql:host=$sqlServer;dbname=$sqlBase;charset=utf8mb4";
    $pdo = new PDO($dsn, $sqlUser, $sqlPass);

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "UPDATE system SET value = 'maintenance' WHERE name = 'state'";
    $pdo->exec($sql);

    // Retrieve the latest release tag name using cURL
    $apiUrl = "https://api.github.com/repos/$user/$repo/releases/latest";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
    $response = curl_exec($ch);
    curl_close($ch);

    $releaseData = json_decode($response, true);
    $latestRelease = $releaseData['tag_name'];

    // Print the latest release tag name
    echo "Dernière version : $latestRelease\n";

    // Download and extract the release from GitHub
    $version = substr($latestRelease, 1); // Remove the 'v' prefix
    $tarFile = "/tmp/$latestRelease.tar.gz";
    $downloadUrl = "https://github.com/$user/$repo/archive/$latestRelease.tar.gz";

    // Download the release tar.gz file using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $downloadUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Save the downloaded tar.gz file
    file_put_contents($tarFile, $response);

    // Extract the release contents using tar
    $extractDir = "/tmp/$repo-$version";
    $extractCommand = "tar -xzf $tarFile -C /tmp";
    exec($extractCommand);

    // Move the extracted files to the target directory
    $moveCommand = "cp -r -f $extractDir/* /var/www/html/";
    exec($moveCommand);

    // Set www-data if script executed by root
    if (posix_getuid() === 0) { // Check if the user is root
        // Get the UID of the www-data user
        $uid = posix_getpwnam('www-data')['uid'];
        $gid = posix_getgrnam('www-data')['gid'];

        // Set the correct ownership for the files and subdirectories
        $dirIterator = new RecursiveDirectoryIterator('/var/www/html/');
        $iterator = new RecursiveIteratorIterator($dirIterator);
        foreach ($iterator as $fileInfo) {
            chown($fileInfo->getPathname(), $uid);
            chgrp($fileInfo->getPathname(), $gid);
        }
    }
    
    // Print a success message
    echo "La copie c'est bien déroulée, connectez vous à votre compte administrateur pour appliquer la mise à jour.\n";

} catch (Exception $e) {
    // Handle any errors
    echo "Erreur: " . $e->getMessage() . "\n";
}

?>