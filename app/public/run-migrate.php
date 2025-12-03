<?php
echo shell_exec('php /var/www/html/bin/console doctrine:migrations:migrate --no-interaction 2>&1');
