#!/bin/bash
# update.bash
# Updates the ski database with the most-recent report. Runs hourly.
#
# Takes seven parameters: operation(update/report/links/updatefilewrite) domain filedestination dbuser dbpass dbname
# ex: ./update.bash update denverpostplus.com/app user pass dbname /home/
# ex: ./update.bash update localhost/skireport user pass dbname /home/
# If you wanted to just update the output files you would do this:
# ./update.bash updatefilewrite denverpostplus.com/app
# ./update.bash updatefilewrite localhost
# /bin/sh /var/www/vhosts/denverpostplus.com/httpdocs/app/skireport/update.bash update denverpostplus.com/app /var/www/vhosts/denverpostplus.com/httpdocs/app/skireport/ db27949 $DB_PASS db27949_ski localhost

rm $3ski-old.xml
mv $3ski-new.xml $3ski-old.xml
wget -O $3ski-new.xml http://dyn.onthesnow.com/media/denverpost/denverpost/xml/na-ski.xml

# Output the differences in the xml files to text
diff -U34 $3ski-new.xml $3ski-old.xml > $3skidiff.tmp.txt
# diff -U34 ski-new.xml na-ski.original.xml > st
# grep 'SkiArea' st > stt

# Delete the cruft (lines 3-44) off the top of the file
sed 3,44d $3skidiff.tmp.txt > $3skidiff.txt

# Write the updated SkiArea ids to ids.tmp.txt
grep -o 'SkiArea id="[0-9]*' $3skidiff.tmp.txt > $3ids.tmp.txt

# Delete the cruft and save it as ids.txt
sed 's/SkiArea id="//g' $3ids.tmp.txt > $3ids.txt

#wget -O $3sql http://$2/skireport/update.php?$1
/usr/bin/php $3update.php $1 > $3sql

# mysql -u db27949 --password=$DB_PASS db27949_ski < handsql
mysql --host=$7 --user=$4 --password=$5 $6 < $3sql

rm $3ids.tmp.txt
rm $3skidiff.tmp.txt

# Run the back-up routine
/usr/bin/php $3update.php backup > $3sql_backup
mysql --host=$7 --user=$4 --password=$5 $6 < $3sql_backup

#Clean up the tables and write the changes to the report_delta db.
#wget -O- http://$2/skireport/cleanup.php
###php /var/www/vhosts/denverpostplus.com/httpdocs/app/skireport/cleanup.php > cleanuplog

#Do the update and check-up for colorado resorts
#wget --delete-after http://$2/skireport/cleanup.php?colorado

#New revised output (does it with file-writing, not wgetting-to-file)
#wget -O- http://$2/skireport/output.php?write=skiarea
#/usr/bin/php ./output.php skiarea
