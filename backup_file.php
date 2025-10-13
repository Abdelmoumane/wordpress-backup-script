<?php
// =====================================
// 🔒 Script PHP — Copia de seguridad completa de WordPress
// Archivos + Base de datos (sin usar FTP)
// =====================================

set_time_limit(0);
date_default_timezone_set('Europe/Madrid');

// ===== Carpeta donde se guardarán las copias =====
$backup_dir = __DIR__ . "/backups";
if (!is_dir($backup_dir)) mkdir($backup_dir, 0777, true);

// ===== Configuración del sitio =====
// ⚠️ Cambiar los datos reales de la base de datos cuando se use en el servidor.
$sites = [
    [
        'name' => 'dominio.com',   // Nombre del dominio
        'db_host' => 'localhost',       // Servidor de base de datos
        'db_user' => 'db_user', // Usuario de la base de datos
        'db_pass' => 'R.db_pass', // Contraseña de la base de datos
        'db_name' => 'db_name', // Nombre de la base de datos
        'source_dir' => dirname(__DIR__), // Carpeta principal del sitio (ej: /public_html)
    ]
];

// 🔁 Recorremos cada sitio configurado
foreach ($sites as $site) {
    echo "=============================\n";
    echo "🔹 Iniciando copia de seguridad: {$site['name']}\n";
    echo "=============================\n";

    // 📁 Crear carpeta temporal con fecha y hora
    $timestamp = date("Ymd-His");
    $folder = "$backup_dir/{$site['name']}-backup-$timestamp";
    mkdir($folder);

    // 1️⃣ Copia de la base de datos usando mysqldump
    $db_file = "$folder/db-{$site['name']}-$timestamp.sql";
    $cmd = "mysqldump -h{$site['db_host']} -u{$site['db_user']} -p'{$site['db_pass']}' {$site['db_name']} > {$db_file}";
    system($cmd);
    echo "✔ Base de datos guardada en: $db_file\n";

    // 2️⃣ Copiar los archivos del sitio (sin FTP)
    echo "📂 Copiando archivos del sitio...\n";
    $source = $site['source_dir'];
    $destination = "$folder/files";

    // Función recursiva para copiar carpetas y archivos
    function copyDir($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst, 0777, true);
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') continue;

            // Excluir carpetas que no queremos copiar
            if ($file == 'tools' || $file == 'backups') continue;

            if (is_dir("$src/$file")) {
                copyDir("$src/$file", "$dst/$file");
            } else {
                copy("$src/$file", "$dst/$file");
            }
        }
        closedir($dir);
    }

    // Ejecutar la copia de los archivos
    copyDir($source, $destination);
    echo "✔ Archivos copiados desde: $source\n";

    // 3️⃣ Comprimir todo en un archivo ZIP
    echo "🗜️ Comprimiendo en archivo ZIP...\n";
    $zip_name = "$backup_dir/{$site['name']}-backup-$timestamp.zip";
    $zip = new ZipArchive();
    if ($zip->open($zip_name, ZipArchive::CREATE) === TRUE) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folder) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        echo "✔ Archivo ZIP creado: $zip_name\n";
    } else {
        echo "❌ Error al crear el archivo ZIP para {$site['name']}\n";
    }

    // 4️⃣ Eliminar la carpeta temporal
    $it = new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->isDir()) rmdir($file->getRealPath());
        else unlink($file->getRealPath());
    }
    rmdir($folder);

    echo "✅ Copia de seguridad completada para {$site['name']}\n\n";
}

// 🎉 Finalización
echo "🎉 Todas las copias completadas correctamente.\n";
?>
