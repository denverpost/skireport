#!/bin/bash
# update.bash
# Updates the ski database with the most-recent report. Runs hourly.
#
# Takes seven parameters: operation(update/report/links/updatefilewrite) domain filedestination dbuser dbpass dbname
# ex: ./update.bash update 
# ex: ./update.bash update
# 
# If you wanted to just update the output files you would do this:
# ./update.bash updatefilewrite denverpostplus.com/app
# ./update.bash updatefilewrite localhost
# cd /var/www/vhosts/denverpostplus.com/httpdocs/app/skireport/; ./update.bash update 
source ../../../source.bash
TEST=''
FLUSH=''
REPORT='snowreport'
OPERATION=$1

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

# If we're flushing the files, we run output then quit.
if [ ! -z "$FLUSH" ]
then
    php output.php skiarea
    exit 2
fi

if [ -z "$API_TOKEN" ]
then
    echo "ERROR: Environment var API_TOKEN must be set."
    echo "How to set it:"
    echo "$ export API_TOKEN='value'"
    exit 2
fi

# We have an API token.
# We use that to download json snow report data for each Colorado resort.
> ids.txt
for RESORT in `cat ids.colorado.txt`;
do
    if [ -z "$TEST" ]
    then
        mv /tmp/$RESORT-$REPORT /tmp/$RESORT-$REPORT-old
        URL="http://clientservice.onthesnow.com/externalservice/resort/$RESORT/$REPORT?token=$API_TOKEN&language=en&country=US"
        wget -O /tmp/$RESORT-$REPORT "$URL"
    fi

    # If this report is different than the prior, we add it to the list of resorts to update.
    # The '--brief' flag on the diff command just lets us know if the files are different.
    # If they're the same, diff will return nothing.
    DIFF=`diff /tmp/$RESORT-$REPORT-old /tmp/$RESORT-$REPORT --brief`
    printf -v SPACELESS '%s' $DIFF
    if [ ! -z $SPACELESS ]
    then
        echo $RESORT >> ids.txt
    fi
done;

php update.php $OPERATION > sql/$OPERATION.sql

# mysql -u db27949 --password=$DB_PASS db27949_ski < handsql
mysql --host=localhost --user=$DB_USER --password=$DB_PASS db27949_ski < sql/$OPERATION.sql

# Run the back-up routine
#php update.php backup > sql_backup
#mysql --host=$7 --user=$4 --password=$DB_PASS $6 < sql_backup

# Clean up the tables and write the changes to the report_delta db.
php cleanup.php > log-cleanup

php output.php skiarea > log-output
