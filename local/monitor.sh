#!/bin/bash
#
# monitor.sh [status file]
#
# Purpose: place a status file in the root of the web application if all is well.
# If not: replace the file and try to restart the applications.

f=$1
if [ -z "$f" ] ; then
    echo "Warning. No file parameter found. Usage: ./monitor.sh /path/to/root/of/the/webapplication"
    exit 1
fi


q="http://localhost:8080/solr/biblio/select"
O=/tmp/status.txt
wget --spider -T 3 -t 3 -O $O $q
rc=$?
if [[ $rc == 0 ]] ; then
    touch $f
    cp /opt/status.txt $f
    exit 0
else
    rm -f $f
	echo "$(date): Invalid response ${rc}" >> /opt/status.txt
	service vufind stop
    sleep 15
	killall java
	sleep 5
	service vufind start
	sleep 15
fi