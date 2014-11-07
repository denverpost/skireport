#!/bin/bash
# update.bash
# Updates the ski database with the most-recent report. Runs hourly.
#
# Takes seven parameters: operation(update/report/links/updatefilewrite) domain filedestination dbuser dbpass dbname
# ex: ./update.bash update denverpostplus.com/app user pass dbname /home/
# ex: ./update.bash update localhost/skireport user pass dbname /home/
# 
# If you wanted to just update the output files you would do this:
# ./update.bash updatefilewrite denverpostplus.com/app
# ./update.bash updatefilewrite localhost
# cd /var/www/vhosts/denverpostplus.com/httpdocs/app/skireport/; /bin/sh ./update.bash update denverpostplus.com/app ./ db27949 $DB_PASS db27949_ski localhost

TEST=0

while [ "$1" != "" ]; do
    case $1 in
        -t | --test ) shift
            TEST=1
            ;;
    esac
    shift
done


rm ski-old.xml
mv ski-new.xml ski-old.xml
wget -O ski-new.xml http://dyn.onthesnow.com/media/denverpost/denverpost/xml/na-ski.xml

# Output the differences in the xml files to text
diff -U34 ski-new.xml ski-old.xml > skidiff.tmp.txt
# diff -U34 ski-new.xml na-ski.original.xml > st
# grep 'SkiArea' st > stt

# Delete the cruft (lines 3-44) off the top of the file
sed 3,44d skidiff.tmp.txt > skidiff.txt

# Write the updated SkiArea ids to ids.tmp.txt
grep -o 'SkiArea id="[0-9]*' skidiff.tmp.txt > ids.tmp.txt

# Delete the cruft and save it as ids.txt
sed 's/SkiArea id="//g' ids.tmp.txt > ids.txt

#wget -O sql http://$2/skireport/update.php?$1
php update.php $1 > sql

# mysql -u db27949 --password=$DB_PASS db27949_ski < handsql
mysql --host=$7 --user=$4 --password=$5 $6 < sql

rm ids.tmp.txt
rm skidiff.tmp.txt

# Run the back-up routine
php update.php backup > sql_backup
mysql --host=$7 --user=$4 --password=$5 $6 < sql_backup

#Clean up the tables and write the changes to the report_delta db.
#wget -O- http://$2/skireport/cleanup.php
###php /var/www/vhosts/denverpostplus.com/httpdocs/app/skireport/cleanup.php > cleanuplog

#Do the update and check-up for colorado resorts
#wget --delete-after http://$2/skireport/cleanup.php?colorado

#New revised output (does it with file-writing, not wgetting-to-file)
#wget -O- http://$2/skireport/output.php?write=skiarea
#/usr/bin/php ./output.php skiarea
