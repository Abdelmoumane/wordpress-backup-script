<?php
// ==========================================
// 💾 PHP Script — Copia de seguridad segura
// Descarga directa (sin ZIP, sin mensajes intermedios)
// ==========================================

set_time_limit(0);
date_default_timezone_set('Europe/Madrid'); // 🔧 Cambia si tu servidor usa otra zona horaria

// ===== 🔐 Seguridad =====
$token = "MiClaveSegura123"; // 🔧 Cambia esto por tu clave personal secreta
if (!isset($_GET['key']) || $_GET['key'] !== $token) {
    http_response_code(403);
    exit("⛔ Acceso denegado. Clave incorrecta.");
}

// ===== ⚙️ Configuración del sitio =====
$site = [
    'name'       => 'ejemplo.com',              // 🔧 Cambia al nombre de tu sitio o proyecto
    'db_host'    => 'localhost',                // 🔧 Servidor de base de datos (normalmente localhost)
    'db_user'    => 'TU_USUARIO_DB',            // 🔧 Usuario de la base de datos
    'db_pass'    => 'TU_CONTRASEÑA_DB',         // 🔧 Contraseña de la base de datos
    'db_name'    => 'TU_NOMBRE_DB',             // 🔧 Nombre de la base de datos
    'source_dir' => '/home/usuario/public_html' // 🔧 Ruta absoluta al directorio del sitio (por ejemplo /home/tuusuario/public_html)
];

// ===== 🧩 Funciones auxiliares =====
function copyDir($src, $dst) {
    if (!is_dir($src)) return;
    @mkdir($dst, 0777, true);
    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if (in_array($file, ['.', '..', 'tools', 'backups'])) continue;
        $from = "$src/$file"; 
        $to   = "$dst/$file";
        if (is_dir($from)) {
            copyDir($from, $to);
        } else {
            @copy($from, $to);
        }
    }
    closedir($dir);
}

function deleteDir($dirPath) {
    if (!is_dir($dirPath)) return;
    foreach (scandir($dirPath) as $object) {
        if (in_array($object, ['.', '..'])) continue;
        $path = "$dirPath/$object";
        if (is_dir($path)) {
            deleteDir($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dirPath);
}

// ===== 🧱 Preparar entorno temporal =====
$timestamp   = date("Ymd-His");
$tmp_base    = __DIR__ . '/tmp';               // 🔧 Carpeta temporal (asegúrate que /tools/tmp existe con permisos 0777)
$tmp_folder  = "$tmp_base/backup-$timestamp";
@mkdir($tmp_base, 0777, true);
@mkdir($tmp_folder, 0777, true);

// ===== 1️⃣ Exportar base de datos =====
$db_file = "$tmp_folder/db-{$site['name']}-$timestamp.sql";
$cmd = "mysqldump -h{$site['db_host']} -u{$site['db_user']} -p'{$site['db_pass']}' {$site['db_name']} > {$db_file} 2>&1";
exec($cmd);

// ===== 2️⃣ Copiar archivos del sitio =====
$files_folder = "$tmp_folder/files";
@mkdir($files_folder, 0777, true);
copyDir($site['source_dir'], $files_folder);

// ===== 3️⃣ Crear archivo .tar (usando tar del sistema) =====
$tar_file = "$tmp_folder/{$site['name']}-backup-$timestamp.tar";
$cmd = "tar -cf $tar_file -C $tmp_folder . 2>&1"; // 🔧 Asegúrate que tu hosting permite el comando tar
exec($cmd);

// ===== 📦 Descargar la copia de seguridad =====
if (file_exists($tar_file)) {
    header('Content-Type: application/x-tar');
    header('Content-Disposition: attachment; filename="' . basename($tar_file) . '"');
    header('Content-Length: ' . filesize($tar_file));
    readfile($tar_file);

    // 🔧 Si quieres conservar los archivos temporales para depuración, comenta esta línea:
    deleteDir($tmp_folder);

    exit;
} else {
    deleteDir($tmp_folder);
    exit("❌ Error al crear la copia de seguridad.");
}
?>
