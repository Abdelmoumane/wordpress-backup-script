# WordPress Backup Script (PHP)

Script en PHP para crear copias de seguridad completas de sitios WordPress
(archivos + base de datos) en un hosting compartido.

## Características
- Exporta la base de datos con `mysqldump`
- Copia todos los archivos del sitio (excepto /tools y /backups)
- Crea un archivo ZIP final con todo
- No requiere acceso SSH (solo PHP)

## Seguridad
⚠️ No incluir credenciales reales en el repositorio.  
Usar un archivo `config.php` local con los datos de conexión.

## Uso
1. Subir `backup_full.php` y `config.php` al servidor.  
2. Acceder desde el navegador o por cron job.
