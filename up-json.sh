#!/bin/sh
# up-json.sh
# Updates the ski database with the most-recent report. Runs hourly.
#
# Takes seven parameters: operation(update/report/links/updatefilewrite) domain dbuser dbpass dbname
# ex: ./update.sh update denverpostplus.com/app user pass dbname /home/
# ex: ./update.sh update localhost/skireport user pass dbname /home/
# If you wanted to just update the output files you would do this:
# ./update.sh updatefilewrite denverpostplus.com/app
# ./update.sh updatefilewrite localhost
# /bin/sh ./update.sh update denverpostplus.com/app db27949 passwordy db27949_ski localhost

wget -O ski-new.json 'http://feeds.snocountry.com/conditions.php?apiKey=denver536.post214&states=co&resortType=Alpine'

#wget -O $3sql http://$2/skireport/update.php?$1
/usr/bin/php update.php $1 > sql

mysql --host=$6 --user=$3 --password=$4 $5 < sql
