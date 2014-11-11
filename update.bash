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

TEST=''
FLUSH=''
REPORT='snowreport'

while [ "$1" != "" ]; do
    case $1 in
        -t | --test ) shift
            TEST=1
            ;;
    esac
    case $1 in
        -f | --flush ) shift
            FLUSH=1
            ;;
    esac
    shift
done

if [ -z "$API_TOKEN" ]
then
    echo "ERROR: Environment var API_TOKEN must be set."
    echo "How to set it:"
    echo "$ export API_TOKEN='value'"
    exit 2
fi

# We have an API token.
# We use that to download json snow report data for each Colorado resort.
if [ -z "$TEST" ]
then
    > ids.txt
fi

for RESORT in `cat ids.colorado.txt`;
do
    if [ -z "$TEST" ]
    then
        mv /tmp/$RESORT-$REPORT /tmp/$RESORT-$REPORT-old
        URL="http://clientservice.onthesnow.com/externalservice/resort/$RESORT/$REPORT?token=$API_TOKEN&language=en&country=US"
        wget -O /tmp/$RESORT-$REPORT "$URL"
    fi

    # If this report is different than the prior, we add it to the list of resorts to update.
    if [ ! -z `diff /tmp/$RESORT-$REPORT-old /tmp/$RESORT-$REPORT` ]
    then
        echo $RESORT >> ids.txt
    fi
done;

php update.php $1 > sql

# mysql -u db27949 --password=$DB_PASS db27949_ski < handsql
#mysql --host=$7 --user=$4 --password=$5 $6 < sql

# Run the back-up routine
#php update.php backup > sql_backup
#mysql --host=$7 --user=$4 --password=$5 $6 < sql_backup

#Clean up the tables and write the changes to the report_delta db.
#wget -O- http://$2/skireport/cleanup.php
###php /var/www/vhosts/denverpostplus.com/httpdocs/app/skireport/cleanup.php > cleanuplog

#Do the update and check-up for colorado resorts
#wget --delete-after http://$2/skireport/cleanup.php?colorado

#New revised output (does it with file-writing, not wgetting-to-file)
#wget -O- http://$2/skireport/output.php?write=skiarea
#/usr/bin/php ./output.php skiarea
