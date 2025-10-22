<?php
// =====================================
// 💾 PHP Script — Copia de seguridad segura (sin ZIP)
// Solo descarga desde el servidor al PC local si se usa la clave correcta
// =====================================

set_time_limit(0);
date_default_timezone_set('Europe/Madrid');

// ===== 🔐 Seguridad básica =====
$token = "MiClaveSegura123"; // 🔹 Cambia esto a tu clave personal
if (!isset($_GET['key']) || $_GET['key'] !== $token) {
    http_response_code(403);
    exit("⛔ Acceso denegado. Clave incorrecta.");
}

// ===== Configuración del sitio =====
$site = [
    'name' => 'exemple.com',
    'db_host' => 'localhost',
    'db_user' => 'TU_USUARIO_DB',
    'db_pass' => 'TU_CONTRASEÑA_DB',
    'db_name' => 'TU_NOMBRE_DB',
    'source_dir' => dirname(__DIR__), // por ejemplo: /public_html
];

// ===== Crear archivo temporal de base de datos =====
$timestamp = date("Ymd-His");
$tmp_folder = sys_get_temp_dir() . "/backup-" . $timestamp;
mkdir($tmp_folder, 0777, true);

$db_file = "$tmp_folder/db-{$site['name']}-$timestamp.sql";
$cmd = "mysqldump -h{$site['db_host']} -u{$site['db_user']} -p'{$site['db_pass']}' {$site['db_name']} > {$db_file}";
system($cmd);

// ===== Crear carpeta temporal para archivos =====
$files_folder = "$tmp_folder/files";
mkdir($files_folder, 0777, true);

function copyDir($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst, 0777, true);
    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') continue;
        if ($file == 'tools' || $file == 'backups') continue;
        if (is_dir("$src/$file")) {
            copyDir("$src/$file", "$dst/$file");
        } else {
            copy("$src/$file", "$dst/$file");
        }
    }
    closedir($dir);
}

copyDir($site['source_dir'], $files_folder);

// ===== Crear archivo .tar =====
$tar_file = "$tmp_folder/{$site['name']}-backup-$timestamp.tar";
$phar = new PharData($tar_file);
$phar->buildFromDirectory($tmp_folder);

// ===== Forzar la descarga al ordenador local =====
header('Content-Type: application/x-tar');
header('Content-Disposition: attachment; filename="' . basename($tar_file) . '"');
header('Content-Length: ' . filesize($tar_file));
readfile($tar_file);

// ===== Limpiar archivos temporales =====
function deleteDir($dirPath) {
    if (!is_dir($dirPath)) return;
    $objects = scandir($dirPath);
    foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
            $path = $dirPath . "/" . $object;
            if (is_dir($path))
                deleteDir($path);
            else
                unlink($path);
        }
    }
    rmdir($dirPath);
}
deleteDir($tmp_folder);
exit;
?>
