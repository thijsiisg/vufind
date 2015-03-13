#!/bin/bash
#
# harvest-marc.sh
#
# Usage: ./harvest-marc.sh set_spec from [optional datestamp YYYY-MM-DD ]



set_spec=$1
if [ -z "$set_spec" ] ; then
	echo "No setspec given as argument. Usage: ./harvest-marc.sh set_spec from [optional datestamp YYYY-MM-DD ]"
	exit -1
fi


HARVEST_DIRECTORY="/data/datasets/${set_spec}/"
log="/data/log/${set_spec}.log"
catalog_file="${HARVEST_DIRECTORY}catalog_file.xml"
last_harvest="${HARVEST_DIRECTORY}last_harvest.txt"



# The VuFind harvester sets a last harvest file. But we set our own to have some days overlapping.
from=$2
if [ -z "$from" ] ; then
    year=$(date --date="222 days ago" +"%Y")
    month=$(date --date="222 days ago" +"%m")
    day=$(date --date="222 days ago" +"%d")
    from="${year}-${month}-${day}"
fi


# Find a directory older than three days. If it is there, we assume a job is stale and no longer running.
find $HARVEST_DIRECTORY -type d -mtime 3 -exec rm -rf {} +
if [ -d $HARVEST_DIRECTORY ] ; then
	echo "Folder ${HARVEST_DIRECTORY} exists... skipping..." >> $log
	exit -1
fi


mkdir -p $HARVEST_DIRECTORY
echo $from > $last_harvest
echo "set_spec=${set_spec}" >> $log
echo "from=${from}" >> $log 
echo "Harvest folder: ${HARVEST_DIRECTORY}" >> $log


# Begin the harvest
php /usr/local/vufind/local/harvest_oai.php $set_spec >> $log
if [ ! -f $catalog_file ] ; then
    echo "catalog_file not found: ${catalog_file}">> $log
    exit 1
fi
 
 
# Import the records
/usr/local/vufind/import-marc.sh -p /usr/local/vufind/local/import/import_$set_spec.properties $f >> $log


# Clear the directory
rm -rf $HARVEST_DIRECTORY


# Create PDFs
fop="/usr/local/vufind/local/import/fop-${set_spec}.sh"
if [ -f $fop ] ; then
    $fop
fi


wget -O /tmp/commit.txt "http://localhost:8080/solr/biblio/update?commit=true"

##############################################################################
# I think we are done for today...
echo "I think we are done for today..." >> $log


exit 0