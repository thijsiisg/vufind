#!/bin/bash
#
# harvest-marc.sh
#
# Usage: ./harvest-marc.sh set_spec from [optional datestamp YYYY-MM-DD ]


#-----------------------------------------------------------------------------------------------------------------------
# Get the setSpec argument
#-----------------------------------------------------------------------------------------------------------------------
set_spec=$1
if [ -z "$set_spec" ] ; then
	echo "No setspec given as argument. Usage: ./harvest-marc.sh set_spec from [optional datestamp YYYY-MM-DD ]"
	exit -1
fi


#-----------------------------------------------------------------------------------------------------------------------
# Setup the environment variables
#-----------------------------------------------------------------------------------------------------------------------
HARVEST_DIRECTORY="/data/datasets/${set_spec}/"
log="/data/log/${set_spec}.log"
catalog_file="${HARVEST_DIRECTORY}catalog.xml"


#-----------------------------------------------------------------------------------------------------------------------
# The VuFind harvester sets a last harvest file. But here we set our own with ten days overlapping.
#-----------------------------------------------------------------------------------------------------------------------
from=$2
if [ -z "$from" ] ; then
    DAYS=10
    year=$(date --date="${DAYS} days ago" +"%Y")
    month=$(date --date="${DAYS} days ago" +"%m")
    day=$(date --date="${DAYS} days ago" +"%d")
    from="${year}-${month}-${day}"
fi


#-----------------------------------------------------------------------------------------------------------------------
# If it is a harvest directory older than three days, we delete it because it has gone stale.
#-----------------------------------------------------------------------------------------------------------------------
find $HARVEST_DIRECTORY -type d -mtime 3 -exec rm -rf {} +
if [ -d $HARVEST_DIRECTORY ] ; then
	echo "Folder ${HARVEST_DIRECTORY} exists... a harvest may be in progress. Skipping todays harvest..." >> $log
	exit 0
fi



#-----------------------------------------------------------------------------------------------------------------------
# Tell what we are doing
#-----------------------------------------------------------------------------------------------------------------------
mkdir -p $HARVEST_DIRECTORY
echo $from > "${HARVEST_DIRECTORY}last_harvest.txt"
echo "set_spec=${set_spec}" >> $log
echo "from=${from}" >> $log 
echo "Harvest folder: ${HARVEST_DIRECTORY}" >> $log


#-----------------------------------------------------------------------------------------------------------------------
# Begin the harvest
#-----------------------------------------------------------------------------------------------------------------------
php /usr/local/vufind/local/harvest_oai.php $set_spec >> $log
if [ ! -f $catalog_file ] ; then
    echo "Catalog not found: ${catalog_file}">> $log
    subject="Catalog not found: ${set_spec}"
    python $scripts/utils/sendmail.py --body "The harvest did not succeed for ${set_spec}" --from "$MAIL_FROM" --to "$MAIL_TO" --subject "$subject" --mail_relay "$MAIL_RELAY" --mail_user "$MAIL_USER" --mail_password "$MAIL_PASSWORD"
    exit 1
fi
 

#-----------------------------------------------------------------------------------------------------------------------
# Import the records
#-----------------------------------------------------------------------------------------------------------------------
/usr/local/vufind/import-marc.sh -p /usr/local/vufind/local/import/import_$set_spec.properties $f >> $log


#-----------------------------------------------------------------------------------------------------------------------
# Clear the directory
#-----------------------------------------------------------------------------------------------------------------------
rm -rf $HARVEST_DIRECTORY


#-----------------------------------------------------------------------------------------------------------------------
# Create PDFs
#-----------------------------------------------------------------------------------------------------------------------
fop="/usr/local/vufind/local/import/fop-${set_spec}.sh"
if [ -f $fop ] ; then
    $fop
fi


#-----------------------------------------------------------------------------------------------------------------------
# Commit. Really not neccessary.
#-----------------------------------------------------------------------------------------------------------------------
wget -O /tmp/commit.txt "http://localhost:8080/solr/biblio/update?commit=true"


#-----------------------------------------------------------------------------------------------------------------------
# I think we are done for today...
#-----------------------------------------------------------------------------------------------------------------------
echo "I think we are done for today..." >> $log
exit 0