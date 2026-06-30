#!/bin/sh
# Render OMP's config.inc.php from environment variables on every boot, so the
# image carries no secrets and the config always reflects the platform's env
# (DB credentials, public URL, etc.). Then normalise the Apache MPM and start.
set -e

TEMPLATE=/var/www/html/config.docker.inc.php
CONFIG=/var/www/html/config.inc.php

if [ -f "$TEMPLATE" ]; then
  php -r '
    $tpl = file_get_contents("'"$TEMPLATE"'");
    $map = [
      "__OMP_BASE_URL__"     => getenv("OMP_BASE_URL")     ?: "http://localhost",
      "__OMP_INSTALLED__"    => getenv("OMP_INSTALLED")    ?: "Off",
      "__OMP_DB_DRIVER__"    => getenv("OMP_DB_DRIVER")    ?: "mysqli",
      "__OMP_DB_HOST__"      => getenv("OMP_DB_HOST")      ?: "localhost",
      "__OMP_DB_USER__"      => getenv("OMP_DB_USER")      ?: "omp",
      "__OMP_DB_PASSWORD__"  => getenv("OMP_DB_PASSWORD")  ?: "omp",
      "__OMP_DB_NAME__"      => getenv("OMP_DB_NAME")      ?: "omp",
      "__OMP_FILES_DIR__"    => getenv("OMP_FILES_DIR")    ?: "/var/www/files",
    ];
    file_put_contents("'"$CONFIG"'", strtr($tpl, $map));
  '
  chown www-data:www-data "$CONFIG"
  echo "Rendered config.inc.php from environment."
fi

# Force prefork as the only Apache MPM (mod_php requires it), then run Apache.
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* 2>/dev/null || true
a2enmod mpm_prefork >/dev/null 2>&1 || true

exec apache2-foreground
