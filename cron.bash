cd /var/www/apache/denverpostplus.com/httpdocs/app/skireport/
source .source.bash
echo $DB_USER > dblog
./update.bash update > log-update-bash
date > log-skiphp
/usr/bin/php output.php skiarea >> log-skiphp
