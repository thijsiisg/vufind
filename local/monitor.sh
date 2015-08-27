#!/bin/bash
#
# monitor.sh [status file]
#
# Purpose: place a status file in the root of the web application if all is well.
# If not: replace the file and try to restart the applications.


if (( $(pgrep -c "monitor.sh") == 1 )) ; then
    echo "Self"
else
    echo "Already running"
    exit 1
fi


f=$1
if [ -z "$f" ] ; then
    echo "Warning. No file parameter found. Usage: ./monitor.sh /path/to/root/of/the/webapplication"
    exit 1
fi

s=/opt/status.txt
q="http://localhost:8080/solr/biblio/select"
O=/tmp/status.txt
wget --spider -T 5 -t 3 -O $O $q
rc=$?
if [[ $rc == 0 ]] ; then
    echo "$(date)">$f
    exit 0
else
    rm -f $f
    echo "$(date): Invalid response ${rc}" >> $s
    service vufind stop
    sleep 7
    killall java
    sleep 7
    service vufind start
    sleep 30

    subject="${HOSTNAME} - Automatic restart by ${0}"
    /usr/bin/sendmail --body "$s" --from "search@${HOSTNAME}" --to "$MAIL_TO" --subject "$subject" --mail_relay "$VUFIND_MAIL_HOST"

    exit 1
fi
