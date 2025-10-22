# 💾 Script PHP de Copia de Seguridad Segura (sin ZIP)

Un script en PHP sencillo y seguro para crear **copias de seguridad completas** (archivos + base de datos)  
en un **hosting compartido**, con **descarga directa al ordenador local** — sin SSH ni archivos ZIP.

---

## 🚀 Características
- Exporta la base de datos utilizando `mysqldump`.
- Copia todos los archivos del sitio (excepto `/tools` y `/backups`).
- Genera un archivo `.tar` en lugar de `.zip`.
- Limpia automáticamente los archivos temporales después del proceso.
- Protegido con una clave de acceso segura (`?key=`).

---

## 🔐 Seguridad
⚠️ **No incluyas credenciales reales en el repositorio público (como GitHub).**  
Usa un archivo de configuración privado llamado `config_private.php` y agrégalo al `.gitignore`.

**Ejemplo: `config_private.php`**
```php
<?php
return [
  'name' => 'mi-sitio',
  'db_host' => 'localhost',
  'db_user' => 'usuario_db',
  'db_pass' => 'contraseña_db',
  'db_name' => 'nombre_db',
  'source_dir' => '/home/usuario/public_html',
];

```

---

🧩 Uso

Sube los archivos backup_download.php y config_private.php al servidor (por ejemplo: /public_html/tools/).

Accede al script desde el navegador con tu clave secreta:

https://tudominio.com/tools/backup_download.php?key=MiClaveSegura123


La copia de seguridad se descargará automáticamente a tu ordenador local.

⚙️ Personalización opcional

Puedes:

Cambiar el valor de $token para mayor seguridad.

Restringir el acceso al script por dirección IP.

Programar copias automáticas usando un cron job.

📦 Mejoras futuras

Opción para guardar las copias en el servidor en lugar de descargarlas.

Integración con almacenamiento en la nube (Google Drive / Dropbox).

Notificación automática por correo electrónico tras la copia exitosa.
